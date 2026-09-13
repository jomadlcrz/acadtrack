-- GWC Grading System Database Schema

CREATE DATABASE IF NOT EXISTS acadtrack
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE acadtrack;

-- Academic Years
CREATE TABLE academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Academic Terms (Semesters)
CREATE TABLE academic_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(50) NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Dean', 'Faculty', 'Student') NOT NULL DEFAULT 'Student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    force_password_change TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Sets
CREATE TABLE sets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    year_level INT NOT NULL,
    academic_term_id INT NOT NULL,
    department_id INT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    UNIQUE KEY unique_set (name, academic_term_id)
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
    code VARCHAR(20) NOT NULL,
    name VARCHAR(200) NOT NULL,
    nature ENUM('Lecture', 'Laboratory', 'Combined') NOT NULL DEFAULT 'Lecture',
    year_level INT NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    academic_term_id INT NOT NULL,
    is_archived TINYINT(1) DEFAULT 0,
    archived_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_subject (code, academic_term_id),
    INDEX idx_is_archived (is_archived)
) ENGINE=InnoDB;

-- Faculty-Subject Assignments
CREATE TABLE faculty_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_assignment (faculty_id, subject_id, academic_term_id)
) ENGINE=InnoDB;

-- Enrollments
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_enrollment (student_id, subject_id, academic_term_id)
) ENGINE=InnoDB;

-- Grading Periods
CREATE TABLE grading_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    order_num INT NOT NULL,
    weight DECIMAL(5,2) DEFAULT 1.00,
    is_current TINYINT(1) DEFAULT 0,
    academic_term_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Grades
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    grading_period_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    grade DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (grading_period_id) REFERENCES grading_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_grade (student_id, subject_id, grading_period_id, academic_term_id)
) ENGINE=InnoDB;

-- Grade History Log (Immutable Audit Trail)
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
) ENGINE=InnoDB;

-- Grading Sheets
CREATE TABLE grading_sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    subject_id INT NOT NULL,
    grading_period_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    status ENUM('DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'APPROVED', 'FINALIZED', 'RETURNED') DEFAULT 'DRAFT',
    submitted_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    returned_at TIMESTAMP NULL,
    remarks TEXT NULL,
    approved_by INT NULL,
    confirmed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (grading_period_id) REFERENCES grading_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_sheet (faculty_id, subject_id, grading_period_id, academic_term_id)
) ENGINE=InnoDB;

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- Insert default academic year and terms
INSERT INTO academic_years (name, is_active) VALUES
('2026-2027', 1);

INSERT INTO academic_terms (academic_year_id, semester, is_active) VALUES
(1, '1', 1),
(1, '2', 0);

-- Insert default admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role) VALUES
('System', 'Admin', 'admin@gwc.edu', '$2y$10$GhKMKrAkhGIM8w05euGjOOMoGlHXBHEezXHvCULtGX/slyOHainou', 'Admin');

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

-- Insert baseline curriculum subjects
INSERT INTO subjects (code, name, nature, year_level, semester, academic_term_id) VALUES
('IT101', 'Introduction to Computing', 'Lecture', 1, '1', 1),
('IT102', 'Computer Programming 1', 'Combined', 1, '1', 1),
('IT103', 'Data Structures and Algorithms', 'Combined', 2, '1', 1),
('IT201', 'Web Systems and Technologies', 'Combined', 2, '2', 2),
('IT202', 'Information Management', 'Lecture', 2, '2', 2);

-- Insert default academic departments
INSERT INTO departments (code, name, description, status) VALUES
('CIT', 'College of Information Technology', 'Academic department managing Computer Science, Information Technology, and computing programs.', 'active'),
('CS', 'Department of Computer Studies', 'Department providing core computer science, software engineering, and programming curricula.', 'active'),
('CBA', 'College of Business Administration', 'Academic department covering Business Administration, Management, and Accountancy programs.', 'active'),
('CAS', 'College of Arts and Sciences', 'Academic department delivering General Education, Humanities, Social Sciences, and Natural Sciences.', 'active'),
('COE', 'College of Engineering', 'Academic department overseeing Computer Engineering and applied technical disciplines.', 'active');

-- Insert baseline sets for 1st Semester
INSERT INTO sets (name, year_level, academic_term_id, department_id, status) VALUES
('BSIT-1A', 1, 1, 1, 'active'),
('BSIT-1B', 1, 1, 1, 'active'),
('BSIT-2A', 2, 1, 1, 'active'),
('BSCS-1A', 1, 1, 2, 'active');

-- Insert baseline sets for 2nd Semester
INSERT INTO sets (name, year_level, academic_term_id, department_id, status) VALUES
('BSIT-1A', 1, 2, 1, 'active'),
('BSIT-1B', 1, 2, 1, 'active'),
('BSIT-2A', 2, 2, 1, 'active'),
('BSCS-1A', 1, 2, 2, 'active');

-- Automated Grade Audit Trigger
DELIMITER //
CREATE TRIGGER IF NOT EXISTS before_grade_evaluation_change
BEFORE UPDATE ON grades
FOR EACH ROW
BEGIN
    IF (OLD.grade <> NEW.grade) OR (OLD.grade IS NULL AND NEW.grade IS NOT NULL) OR (OLD.grade IS NOT NULL AND NEW.grade IS NULL) THEN
        INSERT INTO grade_history_log (grade_id, student_id, old_score, new_score, action_performed)
        VALUES (OLD.id, OLD.student_id, OLD.grade, NEW.grade, 'UPDATE');
    END IF;
END //
DELIMITER ;



