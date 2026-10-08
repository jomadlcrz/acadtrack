-- Acadtrack Database Schema
--
-- For local development with root privileges, you can uncomment the database creation:
-- CREATE DATABASE IF NOT EXISTS acadtrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE acadtrack;
--
-- When importing via hosting panels (InfinityFree, cPanel phpMyAdmin), select your database
-- from the left sidebar before importing this file directly.

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_temp_password TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active', 'inactive') DEFAULT 'active',
    deactivated_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_users_status (status)
) ENGINE=InnoDB;

-- Academic Years
CREATE TABLE academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_year VARCHAR(20) NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_ay_school_year (school_year),
    INDEX idx_ay_active (is_active)
) ENGINE=InnoDB;

-- Academic Terms (Semesters)
CREATE TABLE academic_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    semester INT(2) NOT NULL DEFAULT 1,
    is_active TINYINT(1) DEFAULT 0,
    is_closed TINYINT(1) DEFAULT 0,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    closed_by INT NULL DEFAULT NULL,
    closure_reason VARCHAR(255) NULL DEFAULT NULL,
    is_archived TINYINT(1) DEFAULT 0,
    archived_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_is_archived (is_archived),
    INDEX idx_terms_closed (is_closed),
    INDEX idx_terms_active_sem (is_active, semester),
    INDEX idx_terms_year_sem (academic_year_id, semester)
) ENGINE=InnoDB;

-- Roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- User Roles
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB;

-- Admin Details
CREATE TABLE admin_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    phone_number VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dept_abbrev VARCHAR(50) NOT NULL UNIQUE,
    dept_name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_departments_status (status)
) ENGINE=InnoDB;

-- Programs (Academic Degrees)
CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NULL,
    program_abbrev VARCHAR(30) NOT NULL UNIQUE,
    program_name VARCHAR(191) NOT NULL UNIQUE,
    program_type VARCHAR(100) NOT NULL DEFAULT 'Bachelors Degree',
    program_length VARCHAR(50) NOT NULL DEFAULT '4 Years',
    description TEXT NULL,
    status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_dept_status (department_id, status)
) ENGINE=InnoDB;

-- Sets
CREATE TABLE sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    department_id INT NULL,
    program_id INT NULL,
    set_name VARCHAR(50) NOT NULL,
    set_code VARCHAR(20) NULL,
    year_level INT NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    UNIQUE KEY unique_set (set_name, academic_term_id),
    INDEX idx_sets_term_year (academic_term_id, year_level),
    INDEX idx_sets_term_status (academic_term_id, status)
) ENGINE=InnoDB;

-- Student Details
CREATE TABLE student_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    program_id INT NULL,
    set_id INT NULL,
    student_number VARCHAR(50) NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    year_level INT NOT NULL DEFAULT 1,
    status ENUM('Regular', 'Irregular') NOT NULL DEFAULT 'Regular',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    FOREIGN KEY (set_id) REFERENCES sets(id) ON DELETE SET NULL,
    INDEX idx_student_number (student_number),
    INDEX idx_sd_name (last_name, first_name),
    INDEX idx_sd_year_level (year_level),
    INDEX idx_sd_status (status),
    INDEX idx_sd_set_prog (set_id, program_id)
) ENGINE=InnoDB;

-- Faculty Details
CREATE TABLE faculty_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department_id INT NULL,
    employee_id VARCHAR(50) NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NOT NULL,
    faculty_type ENUM('instructor', 'dean') NOT NULL DEFAULT 'instructor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    INDEX idx_faculty_type (faculty_type),
    INDEX idx_fd_name (last_name, first_name),
    INDEX idx_fd_dept_type (department_id, faculty_type)
) ENGINE=InnoDB;

-- Students (extension of users)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    set_id INT NULL,
    year_level INT NOT NULL,
    status ENUM('Regular', 'Irregular') NOT NULL DEFAULT 'Regular',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (set_id) REFERENCES sets(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Faculty (extension of users)
CREATE TABLE faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    program_id INT NULL,
    subject_code VARCHAR(50) NOT NULL,
    descriptive_title VARCHAR(200) NOT NULL,
    units DECIMAL(4, 1) NOT NULL DEFAULT 3.0,
    subject_type VARCHAR(50) NOT NULL DEFAULT 'GenEd Core',
    nature ENUM('Lecture', 'Laboratory', 'Combined') NOT NULL DEFAULT 'Lecture',
    year_level INT NOT NULL,
    semester INT(2) NOT NULL DEFAULT 1,
    is_archived TINYINT(1) DEFAULT 0,
    archived_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    UNIQUE KEY unique_subject (subject_code, academic_term_id),
    INDEX idx_is_archived (is_archived),
    INDEX idx_subjects_term_archived (academic_term_id, is_archived, semester),
    INDEX idx_subjects_term_year_sem (academic_term_id, year_level, semester),
    INDEX idx_subjects_program (program_id)
) ENGINE=InnoDB;

-- Subject Prerequisites (Normalized 3NF / BCNF)
CREATE TABLE prerequisites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    prerequisite_subject_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY uq_prereq_pair (subject_id, prerequisite_subject_id),
    INDEX idx_subject_id (subject_id),
    INDEX idx_prereq_subject_id (prerequisite_subject_id)
) ENGINE=InnoDB;

-- Faculty-Subject Assignments
CREATE TABLE faculty_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    faculty_id INT NOT NULL,
    subject_id INT NOT NULL,
    set_id INT NULL DEFAULT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (set_id) REFERENCES sets(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    INDEX idx_fs_faculty_term (faculty_id, academic_term_id),
    INDEX idx_fs_subject_term (subject_id, academic_term_id),
    INDEX idx_fs_set (set_id)
) ENGINE=InnoDB;

-- Enrollments
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_enrollment (student_id, subject_id, academic_term_id),
    INDEX idx_enrollments_subject_term (subject_id, academic_term_id),
    INDEX idx_enrollments_student_term (student_id, academic_term_id)
) ENGINE=InnoDB;

-- Grading Periods
CREATE TABLE grading_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    order_num INT NOT NULL,
    weight DECIMAL(5,2) DEFAULT 1.00,
    is_current TINYINT(1) DEFAULT 0,
    is_closed TINYINT(1) DEFAULT 0,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    closure_reason VARCHAR(255) NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    INDEX idx_gp_closed (is_closed)
) ENGINE=InnoDB;

-- Grades
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    grading_period_id INT NOT NULL,
    subject_id INT NOT NULL,
    student_id INT NOT NULL,
    grade DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (grading_period_id) REFERENCES grading_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_grade (student_id, subject_id, grading_period_id, academic_term_id),
    INDEX idx_grades_subject_term_period (subject_id, academic_term_id, grading_period_id),
    INDEX idx_grades_student_term (student_id, academic_term_id)
) ENGINE=InnoDB;

-- Grade History Log (Immutable Audit Trail)
CREATE TABLE grade_history_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    grade_id INT NOT NULL,
    student_id INT NOT NULL,
    old_score DECIMAL(5,2) NULL,
    new_score DECIMAL(5,2) NULL,
    action_performed VARCHAR(20) DEFAULT 'UPDATE',
    changed_by INT NULL DEFAULT NULL,
    reason VARCHAR(255) NULL DEFAULT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_grade_id (grade_id),
    INDEX idx_student_id (student_id)
) ENGINE=InnoDB;

-- Grading Sheets
CREATE TABLE grading_sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    grading_period_id INT NOT NULL,
    faculty_id INT NOT NULL,
    subject_id INT NOT NULL,
    status ENUM('DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'APPROVED', 'FINALIZED', 'RETURNED') DEFAULT 'DRAFT',
    approved_by INT NULL,
    submitted_at TIMESTAMP NULL,
    reviewed_by INT NULL DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    approved_at TIMESTAMP NULL,
    returned_at TIMESTAMP NULL,
    confirmed_at TIMESTAMP NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (grading_period_id) REFERENCES grading_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_sheet (faculty_id, subject_id, grading_period_id, academic_term_id),
    INDEX idx_sheets_term_status (academic_term_id, status),
    INDEX idx_sheets_faculty_term (faculty_id, academic_term_id)
) ENGINE=InnoDB;

-- Grading Sheet Events (append-only review trail: submitted, review started, approved, returned, adjusted, finalized)
CREATE TABLE IF NOT EXISTS grading_sheet_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    grading_sheet_id INT NOT NULL,
    action VARCHAR(30) NOT NULL,
    from_status VARCHAR(20) NULL,
    to_status VARCHAR(20) NULL,
    actor_id INT NULL,
    actor_name VARCHAR(120) NOT NULL DEFAULT '',
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sheet_events_sheet (grading_sheet_id, id),
    CONSTRAINT fk_sheet_events_sheet FOREIGN KEY (grading_sheet_id) REFERENCES grading_sheets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grading Settings
CREATE TABLE grading_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    faculty_id INT NULL,
    subject_id INT NULL,
    grading_method ENUM('zero_based', 'fifty_based') DEFAULT 'zero_based',
    min_grade DECIMAL(5,2) DEFAULT 0.00,
    max_grade DECIMAL(5,2) DEFAULT 100.00,
    prelim_weight DECIMAL(5,2) DEFAULT 20.00,
    midterm_weight DECIMAL(5,2) DEFAULT 20.00,
    semi_final_weight DECIMAL(5,2) DEFAULT 20.00,
    final_weight DECIMAL(5,2) DEFAULT 40.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Attendance Records
CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    subject_id INT NOT NULL,
    student_id INT NOT NULL,
    faculty_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Excused', 'Late') NOT NULL DEFAULT 'Present',
    remarks VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_attendance_student_subject_date (academic_term_id, subject_id, student_id, attendance_date),
    INDEX idx_att_sub_term_date (subject_id, academic_term_id, attendance_date),
    INDEX idx_att_stud_term (student_id, academic_term_id)
) ENGINE=InnoDB;

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('credentials', 'grades_published', 'general') NOT NULL DEFAULT 'general',
    status ENUM('sent', 'pending', 'failed') NOT NULL DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_notifications_user_status_date (user_id, status, created_at)
) ENGINE=InnoDB;

-- Password Resets
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    used_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_password_resets_email (email),
    INDEX idx_password_resets_token (token)
) ENGINE=InnoDB;

-- Term Audit Logs (Append-Only Lifecycle Ledger)
CREATE TABLE IF NOT EXISTS term_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(64) NOT NULL,
    sy_id INT NULL,
    school_year VARCHAR(20) NULL,
    semester_number INT NULL,
    performed_by INT NULL,
    performer_name VARCHAR(255) NULL,
    role VARCHAR(64) NULL,
    ip_address VARCHAR(45) NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tal_action (action),
    INDEX idx_tal_sy_id (sy_id),
    INDEX idx_tal_performed_by (performed_by),
    INDEX idx_tal_created_at (created_at),
    FOREIGN KEY (sy_id) REFERENCES academic_years(id) ON DELETE SET NULL,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Audit Logs (Append-Only Staff Activity Log)
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    actor_id INT NULL,
    actor_name VARCHAR(120) NOT NULL,
    actor_email VARCHAR(255) NOT NULL DEFAULT '',
    actor_role VARCHAR(30) NOT NULL DEFAULT '',
    category VARCHAR(30) NOT NULL,
    action VARCHAR(80) NOT NULL,
    target_type VARCHAR(40) NOT NULL DEFAULT '',
    target_id VARCHAR(64) NOT NULL DEFAULT '',
    target_label VARCHAR(120) NOT NULL DEFAULT '',
    summary VARCHAR(500) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_category (category, created_at),
    INDEX idx_audit_actor (actor_id, created_at),
    INDEX idx_audit_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Term Registrations (Per-Term Student Standing & Modular Scheduling Ledger)
CREATE TABLE IF NOT EXISTS student_term_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    program_id INT UNSIGNED NULL,
    set_id INT NULL,
    year_level INT NOT NULL DEFAULT 1,
    status ENUM('Regular', 'Irregular') NOT NULL DEFAULT 'Regular',
    registration_status ENUM('enrolled', 'withdrawn', 'completed') NOT NULL DEFAULT 'enrolled',
    registered_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_term (student_id, academic_term_id),
    INDEX idx_str_term_set (academic_term_id, set_id),
    INDEX idx_str_term_status (academic_term_id, status),
    CONSTRAINT fk_str_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    CONSTRAINT fk_str_term FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    CONSTRAINT fk_str_set FOREIGN KEY (set_id) REFERENCES sets(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default academic year and terms
INSERT INTO academic_years (id, school_year, is_active) VALUES
(1, '2026-2027', 1);

INSERT INTO academic_terms (id, academic_year_id, semester, is_active, is_archived) VALUES
(1, 1, 1, 1, 0),
(2, 1, 2, 0, 0);

-- Insert baseline roles
INSERT INTO roles (id, role_name, description) VALUES
(1, 'Admin', 'System Administrator with full institutional management access'),
(2, 'Dean', 'College Dean overseeing curriculum, faculty assignments, and grade verification'),
(3, 'Faculty', 'Instructor encoding student scores and submitting period grading sheets'),
(4, 'Student', 'Enrolled student viewing official grades and whole evaluation');

-- Insert default admin user (email: admin@gwc.edu, temporary password: GWC_acadtrack@2026)
INSERT INTO users (id, email, password, status, is_temp_password) VALUES
(1, 'admin@gwc.edu', '$2y$10$IMk.VC1eTxllDzm.Ufxz1etCBMBG7VgzXaJ1Qqk0f4KZ3WSJqTeu2', 'active', 1);

-- Map admin user to Admin role
INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1);

-- Insert admin profile details
INSERT INTO admin_details (user_id, first_name, last_name) VALUES
(1, 'System', 'Admin');

-- Insert default academic departments
INSERT INTO departments (id, dept_abbrev, dept_name, description, status) VALUES
(1, 'CITE', 'College of Information Technology Education', 'Academic department managing Information Technology, Computer Science, and computing education programs.', 'active');

-- Insert default academic programs
INSERT INTO programs (id, program_abbrev, program_name, program_type, program_length, department_id, status, description) VALUES
(1, 'BSIT', 'Bachelor of Science in Information Technology', 'Bachelors Degree', '4 Years', 1, 'active', 'Prepares students to be IT professionals who are able to perform installation, operation, programming, and maintenance of computer systems.');

-- Insert baseline CITE subjects
INSERT INTO subjects (id, academic_term_id, program_id, subject_code, descriptive_title, units, subject_type, nature, year_level, semester, is_archived) VALUES
(1, 1, 1, 'CC101', 'Introduction to Computing', 3.0, 'Major with Lab', 'Combined', 1, 1, 0),
(2, 1, 1, 'CC102', 'Fundamentals of Programming (Java)', 3.0, 'Major with Lab', 'Combined', 1, 1, 0),
(9, 2, 1, 'CC103', 'Intermediate Programming (Adv. Java)', 3.0, 'Major without Lab', 'Lecture', 1, 2, 0),
(10, 2, 1, 'DS101', 'Discrete Structures', 3.0, 'Major without Lab', 'Lecture', 1, 2, 0),
(17, 1, 1, 'CC104', 'Data Structures & Algorithms', 3.0, 'Major with Lab', 'Lecture', 2, 1, 0),
(18, 1, 1, 'GV101', 'Intro to Graphics Design', 3.0, 'Major with Lab', 'Lecture', 2, 1, 0),
(19, 1, 1, 'HCI101', 'Introduction to Human Computer Interaction 1', 3.0, 'Major without Lab', 'Lecture', 2, 1, 0),
(21, 1, 1, 'IM101', 'Fundamentals of Database Systems', 3.0, 'Major with Lab', 'Lecture', 2, 1, 0),
(22, 1, 1, 'OOP101', 'Object Oriented Programming', 3.0, 'Major with Lab', 'Lecture', 2, 1, 0),
(24, 1, 1, 'SP101', 'Social and Professional Issues', 3.0, 'Major without Lab', 'Lecture', 2, 1, 0),
(25, 2, 1, 'CC105', 'Information Management 1', 3.0, 'Major with Lab', 'Lecture', 2, 2, 0),
(27, 2, 1, 'IP101', 'Integrative Programming and Technologies 1', 3.0, 'Major with Lab', 'Lecture', 2, 2, 0),
(28, 2, 1, 'MS102', 'Quantitative Methods (incl. modeling & Simulation)', 3.0, 'Major without Lab', 'Lecture', 2, 2, 0),
(29, 2, 1, 'NET101', 'Networking 1', 3.0, 'Major with Lab', 'Lecture', 2, 2, 0),
(31, 2, 1, 'PT101', 'Platform-based Development (Web Systems)', 3.0, 'Major without Lab', 'Lecture', 2, 2, 0),
(33, 1, 1, 'CC106', 'Application Dev\'t and Emerging Technologies', 3.0, 'Major with Lab', 'Lecture', 3, 1, 0),
(34, 1, 1, 'IAS101', 'Information Assurance and Security 1', 3.0, 'Major without Lab', 'Lecture', 3, 1, 0),
(35, 1, 1, 'IM102', 'Advance Database Systems', 3.0, 'Major with Lab', 'Lecture', 3, 1, 0),
(36, 1, 1, 'ITELEC1', 'IT Major Elective 1 (Graphics & Visual Computing)', 3.0, 'Major with Lab', 'Lecture', 3, 1, 0),
(37, 1, 1, 'NET102', 'Networking 2', 3.0, 'Major with Lab', 'Lecture', 3, 1, 0),
(39, 1, 1, 'SAD311', 'System Analysis and Design', 3.0, 'Research/Thesis', 'Lecture', 3, 1, 0),
(40, 1, 1, 'SIA101', 'System Integration and Architecture', 3.0, 'Major without Lab', 'Lecture', 3, 1, 0),
(41, 2, 1, 'CAPS101', 'Capstone Project and Research 1', 3.0, 'Research/Thesis', 'Lecture', 3, 2, 0),
(43, 2, 1, 'IT312', 'Computer Accounting (with SAP)', 3.0, 'Major with Lab', 'Lecture', 3, 2, 0),
(44, 2, 1, 'ITELEC2', 'IT Major Elective 2 (Data Warehousing)', 3.0, 'Major with Lab', 'Lecture', 3, 2, 0),
(45, 2, 1, 'PT102', 'Platform-based Dev\'t (Multimedia Systems)', 3.0, 'Major with Lab', 'Lecture', 3, 2, 0),
(46, 2, 1, 'PT103', 'Platform-based Development (Android Programming)', 3.0, 'Major with Lab', 'Lecture', 3, 2, 0),
(47, 2, 1, 'SE101', 'Software Engineering 1', 3.0, 'Major with Lab', 'Lecture', 3, 2, 0),
(48, 1, 1, 'CAPS102', 'Capstone Project and Research 2', 3.0, 'Research/Thesis', 'Lecture', 4, 1, 0),
(49, 1, 1, 'ITELEC3', 'IT Major Elective 4 (Web Systems & Development 2)', 3.0, 'Major without Lab', 'Lecture', 4, 1, 0),
(50, 1, 1, 'ITELEC4', 'IT Major Elective 4 (Web Systems & Development 2)', 3.0, 'Major with Lab', 'Lecture', 4, 1, 0),
(51, 1, 1, 'OS101', 'Operating System', 3.0, 'Major with Lab', 'Lecture', 4, 1, 0),
(52, 1, 1, 'SA101', 'System Administration and Maintenance 1', 3.0, 'Major without Lab', 'Lecture', 4, 1, 0),
(53, 2, 1, 'PRAC101', 'OJT Practicum (486 hours)', 3.0, 'Practicum/OJT', 'Lecture', 4, 2, 0);

-- Insert baseline CITE prerequisites
INSERT INTO prerequisites (id, subject_id, prerequisite_subject_id) VALUES
(1, 9, 2),
(5, 17, 10),
(6, 17, 9),
(7, 19, 1),
(8, 21, 9),
(9, 22, 9),
(11, 24, 1),
(12, 25, 21),
(13, 25, 9),
(15, 27, 19),
(16, 27, 21),
(17, 28, 17),
(18, 29, 9),
(20, 31, 9),
(21, 33, 21),
(22, 33, 25),
(23, 34, 29),
(24, 35, 21),
(25, 35, 25),
(26, 36, 10),
(27, 37, 29),
(28, 40, 27),
(30, 44, 36),
(31, 45, 36),
(32, 46, 17),
(33, 46, 22),
(34, 47, 21),
(35, 47, 31),
(36, 48, 41),
(37, 49, 44),
(38, 49, 35),
(39, 50, 45),
(40, 50, 44),
(41, 51, 17),
(42, 52, 34);

-- Insert default grading periods for 1st Semester
INSERT INTO grading_periods (name, order_num, weight, is_current, academic_term_id) VALUES
('Prelim', 1, 20.00, 0, 1),
('Midterm', 2, 20.00, 0, 1),
('Semi-Final', 3, 20.00, 0, 1),
('Final', 4, 40.00, 1, 1);

-- Insert default grading periods for 2nd Semester
INSERT INTO grading_periods (name, order_num, weight, is_current, academic_term_id) VALUES
('Prelim', 1, 20.00, 0, 2),
('Midterm', 2, 20.00, 0, 2),
('Semi-Final', 3, 20.00, 0, 2),
('Final', 4, 40.00, 1, 2);

-- Automated Grade Audit Trigger (Optional: requires MySQL TRIGGER privilege)
-- Note: Shared and free hosting providers (e.g. InfinityFree) do not grant TRIGGER privileges to MySQL users.
-- The AcadTrack application layer handles grade modification audit logging automatically as a fallback.
-- If your host supports triggers (e.g., local development or VPS), you can uncomment the block below:
--
-- DROP TRIGGER IF EXISTS before_grade_evaluation_change;
-- DELIMITER //
-- CREATE TRIGGER before_grade_evaluation_change
-- BEFORE UPDATE ON grades
-- FOR EACH ROW
-- BEGIN
--     IF (OLD.grade <> NEW.grade) OR (OLD.grade IS NULL AND NEW.grade IS NOT NULL) OR (OLD.grade IS NOT NULL AND NEW.grade IS NULL) THEN
--         INSERT INTO grade_history_log (grade_id, student_id, old_score, new_score, action_performed)
--         VALUES (OLD.id, OLD.student_id, OLD.grade, NEW.grade, 'UPDATE');
--     END IF;
-- END //
-- DELIMITER ;




