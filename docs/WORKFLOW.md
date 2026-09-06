# Acadtrack — Feature Specifications & Workflow Architecture

> **Institution:** Golden West Colleges, Inc. (GWC)  
> **System:** Acadtrack — Academic Grading & Curriculum Evaluation Platform  
> **Reference Specification:** [grading_system_workflow.md](file:///C:/xampp/htdocs/acadtrack/grading_system_workflow.md)  
> **Related Documents:**
> * [Anti-Generic Design Standards](file:///C:/xampp/htdocs/acadtrack/docs/ANTI_GENERIC_DESIGN.md)
> * [Web Design & UI/UX Architecture](file:///C:/xampp/htdocs/acadtrack/docs/WEB_DESIGN_GUIDE.md)
> * [System Architecture & Error Handling](file:///C:/xampp/htdocs/acadtrack/docs/ARCHITECTURE.md)

---

## 1. System Overview

**Acadtrack** is a role-governed academic grading and curriculum evaluation platform engineered for Golden West Colleges, Inc. (GWC). It streamlines semester offerings, course assignments, student roster classification, period-based grade computation, multi-tier administrative reviews, automated email notifications, and student curriculum evaluations.

### The Four Academic Stakeholders
1. **System Administrator:** Oversees master system accounts, user credential provisioning, global term activation, system security, and joint grade sheet review/approval/printing.
2. **College Dean:** Manages curriculum offerings, assigns qualified faculty to subjects, audits grading distributions, and reviews, edits, approves, or returns submitted grading sheets with mandatory remarks.
3. **Faculty / Instructor:** Manages assigned subjects, configures subject nature and grading rules (Zero-Based vs. 50-Based), maintains student rosters (Regular/Irregular, 1st–4th Year), selects active semester, encodes period scores, repeatedly saves working drafts, and submits final sheets.
4. **Student:** Receives automated email credentials and grade availability notifications, selects semester (1st or 2nd), reviews period-by-period grades, and inspects their complete cumulative academic evaluation.

---

## 2. Role Permissions & Capability Matrix

| Feature / Capability | Admin | Dean | Faculty | Student | Route / Endpoint |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Role-Based Login & Dashboard Dispatch** | Yes | Yes | Yes | Yes | `/login`, `/dashboard` |
| **User Account & Password Administration** | Yes | No | No | No | `/admin/users` |
| **Semester & Term Management** (1st & 2nd Sem) | Yes | Yes | Yes | Yes | `/admin/settings`, `/faculty/grading` |
| **Faculty Subject Assignment** (Dean assigns to Faculty) | Yes | Yes | No | No | `/dean/faculty-assignments` |
| **Subject Nature & Type Management** | Yes | Yes | Yes | No | `/faculty/subjects` |
| **Student Management** (Regular/Irregular, 1st–4th Year) | Yes | Yes | Yes | No | `/faculty/students` |
| **Grading Settings** (Zero-Based / 50-Based, Weights) | No | No | Yes | No | `/faculty/grading/settings` |
| **Grade Encoding & Draft Autosaving** | No | No | Yes | No | `/faculty/grading` |
| **Grade Submission for Review** | No | No | Yes | No | `/faculty/grading/submit` |
| **Grade Review, Edit, Approval & Printing** | Yes | Yes | No | No | `/dean/grade-review`, `/dean/grading/print` |
| **Student Grade Inquiry (By Semester)** | Yes | Yes | Yes | Yes | `/student/grades` |
| **Whole Evaluation & Cumulative GPA Viewing** | Yes | Yes | No | Yes | `/student/evaluation` |
| **Automated Email Notifications** (Credentials & Grades) | System | System | System | Recipient | Background Mailer Service |

---

## 3. Grading Sheet Lifecycle State Machine

```mermaid
stateDiagram-v2
    [*] --> DRAFT : Faculty Enters Initial Scores
    DRAFT --> DRAFT : Faculty Saves Working Draft
    DRAFT --> SUBMITTED : Faculty Submits Sheet to Dean/Admin
    SUBMITTED --> UNDER_REVIEW : Dean or Admin Opens Review Queue
    UNDER_REVIEW --> RETURNED : Discrepancy Flagged / Returned with Feedback Remarks
    RETURNED --> DRAFT : Faculty Corrects Scores
    UNDER_REVIEW --> APPROVED : Dean or Admin Approves & Confirms
    APPROVED --> FINALIZED : Registrar Locks Term Records
    FINALIZED --> [*]
```

### State Definitions & Security Guarantees
* **`DRAFT`:** Editable solely by the assigned faculty member. Scores are autosaved or updated via `POST /faculty/grading/save`.
* **`SUBMITTED`:** Inputs locked on the faculty portal. Available in Dean/Admin review queue.
* **`UNDER_REVIEW`:** Dean or Admin has opened the grading sheet and is inspecting historical grade spread, scores, and percentage calculations.
* **`RETURNED`:** Dean has requested corrections with mandatory feedback comments. Inputs unlock for the faculty member to revise.
* **`APPROVED`:** Grades are certified by Dean or Admin. Triggers email notification dispatch to enrolled students.
* **`FINALIZED`:** Term closed. Approved grades become immutable historical records in student academic files.

---

## 4. End-to-End Operational Workflow (The 12-Step Architecture)

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin / Dean
    actor Faculty as Faculty Member
    actor Student as Student
    actor System as Acadtrack Mailer

    Admin->>Faculty: 1-2. Login & Assign Subjects to Faculty
    Faculty->>Faculty: 3. Set Subject Nature & Grading Settings (Zero or 50-Based)
    Faculty->>Faculty: 4-5. Add Students (Reg/Irreg, 1st-4th Yr) & Select Semester
    Faculty->>Faculty: 6-7. Encode Period Scores & Save Working Drafts
    Faculty->>Admin: 8. Submit Completed Grading Sheet
    Admin->>Admin: 9. Review, Edit When Necessary, Approve & Print
    Admin->>System: 10. Finalize Grades & Release to Records
    System-->>Student: 11. Dispatch Email Notification (Grades Available)
    Student->>Student: 12. Login, Select Semester, View Grades & Whole Evaluation
```

### Step 1: Login & Role-Based Dispatching
* **Actor:** All Users (Admin, Dean, Faculty, Student)
* **Route:** `GET /login` &rarr; `POST /login`
* **Process:** User enters email address and password. The system authenticates bcrypt hash and dispatches user to their dedicated role dashboard:
  * Admin &rarr; `/admin/dashboard`
  * Dean &rarr; `/dean/dashboard`
  * Faculty &rarr; `/faculty/dashboard`
  * Student &rarr; `/student/dashboard`

### Step 2: Dean Assigns Subjects
* **Actor:** College Dean (or Admin)
* **Route:** `/dean/faculty-assignments`
* **Process:** Dean selects a Faculty/Instructor from the department roster and assigns specific curriculum subjects and section course loads they will handle for the academic year.

### Step 3: Faculty Sets Up Subject
* **Actor:** Faculty Member
* **Route:** `/faculty/subjects`, `/faculty/grading/settings`
* **Process:** Faculty selects an assigned subject and configures:
  * **Subject Nature / Type:** Lecture, Laboratory, or Combined.
  * **Grading Method:** **Zero-Based (0–100)** or **50-Based (50–100)**.
  * **Grading Period Weights:** Default Prelim (20%), Midterm (20%), Semi-Final (20%), Final (40%).

### Step 4: Faculty Adds/Selects Students
* **Actor:** Faculty Member
* **Route:** `/faculty/students`
* **Process:** Faculty encodes or selects students enrolled in the class section and records:
  * **Enrollment Status:** `Regular` or `Irregular`.
  * **Year Level:** `1st Year`, `2nd Year`, `3rd Year`, or `4th Year`.

### Step 5: Select Semester
* **Actor:** Faculty Member
* **Route:** `/faculty/grading`
* **Process:** Faculty selects the active semester context:
  * **1st Semester**
  * **2nd Semester**
  Loads corresponding student class roster and period assessment columns.

### Step 6: Faculty Enters Grades
* **Actor:** Faculty Member
* **Route:** `/faculty/grading?subject_id={id}&semester={sem}`
* **Process:** Faculty inputs student scores for Prelim, Midterm, Semi-Final, and Final periods according to the configured grading method and period weights.
* **Standards:** Inputs enforce `tabular-nums` alignment, real-time decimal precision, and zero layout shift.

### Step 7: Save and Review Drafts
* **Actor:** Faculty Member
* **Route:** `POST /faculty/grading/save`
* **Process:** Faculty repeatedly saves working drafts with zero lockouts. System automatically calculates grade point equivalents, status marks (`Passed`, `Failed`, `Incomplete`), and checks for data completeness before submission.

### Step 8: Submit Grading Sheet
* **Actor:** Faculty Member
* **Route:** `POST /faculty/grading/submit`
* **Process:** Once all scores are verified, faculty submits the completed grading sheet. A modal confirmation confirms finalization. Input fields immediately lock (`SUBMITTED`) to safeguard data integrity.

### Step 9: Dean / Admin Review & Approval
* **Actor:** College Dean and/or System Administrator
* **Route:** `/dean/grade-review`, `/dean/grading/print`
* **Capabilities:**
  * **View:** Inspect grade distributions, averages, and passing/failing ratios.
  * **Edit:** Adjust or correct individual marks when institutional policy requires correction.
  * **Approve:** Certify and approve the grading sheet.
  * **Return:** Reject sheet with mandatory feedback remarks explaining required instructor revisions.
  * **Print:** Generate and print official physical grade sheets for registrar filing.

### Step 10: Grade Finalization
* **Actor:** Administration / Registrar
* **Process:** Once approved, grades become official historical records for the term. Final Grade Point Equivalents are locked against tampering.

### Step 11: Automated Email Notification
* **Actor:** System (Automated Mailer Service)
* **Process:** 
  1. Automated credential emails notify newly provisioned student and faculty accounts of their login credentials.
  2. Automated release alerts dispatch to enrolled students informing them that official semester grades have been approved and published.

### Step 12: Student Views Evaluation
* **Actor:** Student
* **Routes:** `/student/grades`, `/student/evaluation`
* **Process:**
  1. Student logs in to Acadtrack.
  2. Selects **1st Semester** or **2nd Semester** to inspect period-by-period marks and remarks.
  3. Clicks **"View Whole Evaluation"** to open their comprehensive curriculum evaluation, tracking completed units, cumulative GPA, and overall academic standing.

---

## 5. Exception & Edge Case Protocols

1. **Duplicate Subject Assignment:** Intercepted at controller level; displays dismissible warning banner preventing duplicate instructor assignment to the same section.
2. **Incomplete Grade Submissions:** Unfilled score rows prompt validation alerts; instructors must resolve blanks or explicitly assign `INC` (Incomplete) status.
3. **Returned Grading Sheets:** Highlighted with contextual review banner on `/faculty/grading`, displaying Dean's exact feedback comments and restoring input editability.
4. **Email Dispatch Failures:** Logged to application error logs; grade publication succeeds independently so students can still view results via portal inquiry even if external mail server is unreachable.