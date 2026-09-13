# School System Grade Evaluation Deletion & Archiving Architecture

## 📋 Overview
This document specifies the standard architecture, implementation rules, and database guidelines for handling data deletion and archiving within the **GWC Acadtrack Grade Evaluation System**. Due to the high sensitivity, regulatory requirements, and long-term historical dependencies of academic records, **hard deletion is strictly prohibited across the entire platform**. All foundational records are managed through **Archiving & Status Lifecycle Tracking**.

---

## 🧭 Core Architectural Guidelines

1. **Strict Archiving over Deletion (Zero Hard Deletes)**
   - Hard deletion is prohibited on production academic entities (`subjects`, `academic_terms`, `students`, `users`).
   - Academic records (enrollments, final marks, GPAs, and course catalogs) must remain permanent and immutable.
   - Lifecycle state is managed exclusively via tracking metrics (`is_archived`, `archived_at`, and `status`).

2. **Strict Foreign Key Restraints**
   - Global cascading hard deletes (`ON DELETE CASCADE`) are prohibited on core relational pillars (`users`, `students`, `subjects`, `academic_terms`).
   - Explicit `ON DELETE RESTRICT` clauses act as automated database-level safety locks, guaranteeing that foundational entities cannot be purged while dependent records exist.

3. **Immutable Accountability & Audit Logging**
   - Grade alterations or corrections require a transparent, non-erasable audit logging mechanism.
   - Automated MySQL triggers record before/after score deltas into a dedicated `grade_history_log` audit table.

---

## 🛠 Deletion Matrix & Strategies

| Strategy | Target Entities | Condition / Lifecycle State | Implementation |
| :--- | :--- | :--- | :--- |
| **Archiving / Status Management** | `subjects`, `academic_terms`, `students`, `users` | Course retirements, semester transitions, deactivated personnel | `is_archived = 1, archived_at = NOW()` or `status = 'inactive'` |
| **Zero Hard Delete Policy** | All database tables | **Prohibited Platform-Wide** | Any delete request safely maps to Archiving (`is_archived = 1`) to eliminate data loss risks |

---

## 1. Archiving & Status Management Details

### A. Subjects (Curricular Course Offerings)
- **Table Schema:**
  ```sql
  ALTER TABLE subjects 
      ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0,
      ADD COLUMN archived_at TIMESTAMP NULL DEFAULT NULL,
      ADD INDEX idx_is_archived (is_archived);
  ```
- **Archive Action:**
  ```sql
  UPDATE subjects 
  SET is_archived = 1, archived_at = NOW() 
  WHERE id = :id;
  ```
- **Restore Action:**
  ```sql
  UPDATE subjects 
  SET is_archived = 0, archived_at = NULL 
  WHERE id = :id;
  ```
- **Catalog Behavior:**
  - Active curriculum listings filter `is_archived = 0`.
  - Archived courses remain preserved in historical grade review, student grade slips, transcript generation, and institutional audit reports.
  - The UI provides tabs (`All`, `Active Only`, `Archived Only`) and offers `Archive` and `Restore` buttons without destructive delete buttons.

### B. Users & Faculty Personnel
- Status management via `users.status`:
  - `active`: Normal system access.
  - `inactive`: Deactivated; user is blocked from logging in.
- Administrator accounts are strictly protected from deactivation to prevent system lockout.

---

## 🛡 Relational Guardrails (MySQL Best Practices)

To prevent accidental cascading deletes from destroying foundational records, explicit `ON DELETE RESTRICT` constraints are enforced on foreign keys referencing core relational pillars:

```sql
-- Grades cannot be deleted if subject, student, or academic term is targeted
ALTER TABLE grades ADD CONSTRAINT fk_grades_student_restrict 
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT;

ALTER TABLE grades ADD CONSTRAINT fk_grades_subject_restrict 
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT;

ALTER TABLE grades ADD CONSTRAINT fk_grades_term_restrict 
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT;

-- Enrollments cannot be deleted if subject or academic term is targeted
ALTER TABLE enrollments ADD CONSTRAINT fk_enrollments_subject_restrict 
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT;

ALTER TABLE enrollments ADD CONSTRAINT fk_enrollments_term_restrict 
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT;

-- Grading sheets cannot be deleted if subject or academic term is targeted
ALTER TABLE grading_sheets ADD CONSTRAINT fk_grading_sheets_subject_restrict 
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT;

ALTER TABLE grading_sheets ADD CONSTRAINT fk_grading_sheets_term_restrict 
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT;

-- Faculty assignments cannot be deleted if subject is targeted
ALTER TABLE faculty_subjects ADD CONSTRAINT fk_faculty_subjects_subject_restrict 
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT;
```

---

## 📝 Grade Modification & Correction Auditing

Whenever a grade score is modified in `grades`, an automated MySQL trigger transparently records the transaction into `grade_history_log`.

### 1. Audit Log Schema
```sql
CREATE TABLE grade_history_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    grade_id INT NOT NULL,
    student_id INT NOT NULL,
    old_score DECIMAL(5,2) NULL,
    new_score DECIMAL(5,2) NULL,
    action_performed VARCHAR(20) DEFAULT 'UPDATE',
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_grade_id (grade_id),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Automated MySQL Trigger
```sql
DELIMITER //
CREATE TRIGGER before_grade_evaluation_change
BEFORE UPDATE ON grades
FOR EACH ROW
BEGIN
    IF (OLD.grade <> NEW.grade) OR (OLD.grade IS NULL AND NEW.grade IS NOT NULL) OR (OLD.grade IS NOT NULL AND NEW.grade IS NULL) THEN
        INSERT INTO grade_history_log (grade_id, student_id, old_score, new_score, action_performed)
        VALUES (OLD.id, OLD.student_id, OLD.grade, NEW.grade, 'UPDATE');
    END IF;
END //
DELIMITER ;
```

---

## 🔍 Application-Level Enforcement

1. **Dean Subject Management (`Dean\SubjectController` & `SubjectRepository`):**
   - `POST /dean/subjects/{id}/archive` sets `is_archived = 1, archived_at = NOW()`.
   - `POST /dean/subjects/{id}/restore` sets `is_archived = 0, archived_at = NULL`.
   - Any legacy delete invocation automatically performs a safe archive instead of destructive row deletion.
   - Course catalog UI provides tabs: `All`, `Active Only`, `Archived Only`.
2. **Faculty Student Roster (`Faculty\StudentController`):**
   - Student roster removal verifies if grades have already been recorded for that student in that subject. If grades exist, removal is blocked to preserve grade immutability.
3. **Dean Faculty Assignments (`Dean\FacultyAssignmentController`):**
   - Faculty assignment removal verifies if submitted, approved, or finalized grading sheets exist. If they do, assignment removal is prohibited.
4. **Admin User Management (`Admin\UserController`):**
   - User deactivation/activation handles personnel status transitions without row deletion.
