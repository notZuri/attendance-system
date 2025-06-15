-- Drop existing tables (safe for dev reset)
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS fingerprints;
DROP TABLE IF EXISTS rfid_cards;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- Users table: student & professor accounts
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('student', 'professor') NOT NULL,
    student_number VARCHAR(20) UNIQUE, -- Only used if student
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    profile_photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table (optional, additional detailed student info)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    student_number VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    rfid_tag VARCHAR(50),       -- Can be matched to rfid_cards.card_uid if needed
    fingerprint_id VARCHAR(50), -- Can be matched to fingerprints.id if needed
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Class schedules (professors only)
CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- RFID cards table (linked to user)
CREATE TABLE rfid_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    card_uid VARCHAR(50) UNIQUE NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Fingerprint data table (linked to user)
CREATE TABLE fingerprints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fingerprint_template LONGBLOB NOT NULL,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Attendance table (time in/out with status)
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'late', 'absent') NOT NULL DEFAULT 'present',
    time_in TIME,
    time_out TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    UNIQUE(user_id, schedule_id, attendance_date) -- Avoid duplicates
);


-- Sample Schedule
INSERT INTO schedules (professor_id, subject, day_of_week, start_time, end_time)
VALUES (1, 'Computer Science 101', 'Monday', '08:00:00', '10:00:00');
