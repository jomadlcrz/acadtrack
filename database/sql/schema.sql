-- GWC Grading System Database Schema

CREATE DATABASE IF NOT EXISTS grading_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE grading_system;

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
    student_number VARCHAR(20) NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Dean', 'Faculty', 'Student') NOT NULL DEFAULT 'Student',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Students (extension of users)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    section_id INT NULL,
    year_level INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Faculty (extension of users)
CREATE TABLE faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sections
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    year_level INT NOT NULL,
    academic_term_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Subjects
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(200) NOT NULL,
    year_level INT NOT NULL,
    semester ENUM('1', '2') NOT NULL,
    academic_term_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subject (code, academic_term_id)
) ENGINE=InnoDB;

-- Faculty-Subject Assignments
CREATE TABLE faculty_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (faculty_id, subject_id, academic_term_id)
) ENGINE=InnoDB;

-- Enrollments
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_term_id INT NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
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
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (grading_period_id) REFERENCES grading_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_grade (student_id, subject_id, grading_period_id, academic_term_id)
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (grading_period_id) REFERENCES grading_periods(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_sheet (faculty_id, subject_id, grading_period_id, academic_term_id)
) ENGINE=InnoDB;

-- Grading Settings
CREATE TABLE grading_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_term_id INT NOT NULL,
    grading_method ENUM('zero_based', 'fifty_based') DEFAULT 'zero_based',
    min_grade DECIMAL(5,2) DEFAULT 0.00,
    max_grade DECIMAL(5,2) DEFAULT 100.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_term_id) REFERENCES academic_terms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert default academic year and term
INSERT INTO academic_years (name, is_active) VALUES
('2026-2027', 1);

INSERT INTO academic_terms (academic_year_id, semester, is_active) VALUES
(1, '1', 1);

-- Insert default admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role) VALUES
('System', 'Admin', 'admin@gwc.edu', '$2y$10$GhKMKrAkhGIM8w05euGjOOMoGlHXBHEezXHvCULtGX/slyOHainou', 'Admin');

-- Insert default grading periods
INSERT INTO grading_periods (name, order_num, weight, is_current, academic_term_id) VALUES
('Prelim', 1, 20.00, 0, 1),
('Midterm', 2, 20.00, 0, 1),
('Semi-Final', 3, 20.00, 0, 1),
('Final', 4, 40.00, 1, 1);
