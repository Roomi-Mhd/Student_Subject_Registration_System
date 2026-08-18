-- ============================================
--  Student Subject Registration System
--  South Eastern University of Sri Lanka
--  Database: student_registration
-- ============================================

CREATE DATABASE IF NOT EXISTS student_registration;

USE student_registration;

-- ============================================
--  STUDENTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    reg_number VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    mobile VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    department VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    otp VARCHAR(10) DEFAULT NULL,
    otp_expiry DATETIME DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
--  SUBJECTS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(150) NOT NULL,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    department VARCHAR(50) NOT NULL,
    faculty VARCHAR(100) NOT NULL,
    semester INT NOT NULL,
    credit INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
--  REGISTRATIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (student_id, subject_id)
);

-- ============================================
--  ADMINS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'lecturer') DEFAULT 'admin'
);

-- ============================================
--  SEED: Default Admin  Password: ADD@1234
-- ============================================
INSERT IGNORE INTO
    admins (username, password, role)
VALUES (
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin'
    );

-- ============================================
--  SEED: General Degree (ITC)
-- ============================================
INSERT IGNORE INTO
    subjects (
        subject_name,
        subject_code,
        department,
        faculty,
        semester,
        credit
    )
VALUES (
        'Introduction to Information Technology',
        'ITC 11012',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        1,
        2
    ),
    (
        'Computer Applications',
        'ITC 12012',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        2,
        2
    ),
    (
        'Web Development',
        'ITC 21012',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        3,
        2
    ),
    (
        'Database Management System',
        'ITC 22012',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        4,
        2
    ),
    (
        'Graphic Design',
        'ITC 31012',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        5,
        2
    ),
    (
        'Data Communication and Network',
        'ITC 32012',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        6,
        2
    );

-- ============================================
--  SEED: General Degree T/G Subjects (one per semester)
--  These are 3-credit subjects shared with Honours,
--  visible only to Information Technology-General students.
-- ============================================
INSERT IGNORE INTO
    subjects (
        subject_name,
        subject_code,
        department,
        faculty,
        semester,
        credit
    )
VALUES (
        'Computer System',
        'ITM 11013 (T/G)',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        1,
        3
    ),
    (
        'Information System',
        'ITM 12013 (T/G)',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        2,
        3
    ),
    (
        'Introduction to Programming',
        'ITM 21013 (T/G)',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        3,
        3
    ),
    (
        'Web Programming',
        'ITM 22013 (T/G)',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        4,
        3
    ),
    (
        'Computer Networks',
        'ITM 31013 (T/G)',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        5,
        3
    ),
    (
        'Information Security',
        'ITM 32013 (T/G)',
        'Information Technology-General',
        'Faculty of Arts & Culture',
        6,
        3
    );

-- ============================================
--  SEED: Honours Degree (ITM) - BA Honours in ICT
-- ============================================
INSERT IGNORE INTO
    subjects (
        subject_name,
        subject_code,
        department,
        faculty,
        semester,
        credit
    )
VALUES

-- Year 01
(
    'Computer System',
    'ITM 11013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    1,
    3
),
(
    'Information System',
    'ITM 12013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    2,
    3
),

-- Year 02 Semester I
(
    'Introduction to Programming',
    'ITM 21013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    3,
    3
),
(
    'Mathematics for Computing',
    'ITM 21023',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    3,
    3
),
(
    'Database Systems',
    'ITM 21033',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    3,
    3
),
(
    'System Analysis and Design',
    'ITM 21043',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    3,
    3
),
(
    'Principles of Management',
    'ITM 21053',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    3,
    3
),

-- Year 02 Semester II
(
    'Web Programming',
    'ITM 22013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    4,
    3
),
(
    'Operating System',
    'ITM 22023',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    4,
    3
),
(
    'Multimedia and Graphic Design',
    'ITM 22033',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    4,
    3
),
(
    'e-Business Technologies',
    'ITM 22043',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    4,
    3
),
(
    'Communication Skills',
    'ITM 22053',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    4,
    3
),

-- Year 03 Semester I (from image)
(
    'Internship Training',
    'INM 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'English for Business Writing',
    'ELC 31012',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    2
),
(
    'Graphical Design',
    'ITC 31012B',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    2
),
(
    'Computer Networks',
    'ITM 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Data Structures and Algorithms',
    'ITM 31023',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Object Oriented Programming',
    'ITM 31033',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'ICT Group Project',
    'ITM 31042',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    2
),
(
    'Democracy and Multiculturalism',
    'PSI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Structure of Personality and Disorders',
    'PHI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Environmental Economics',
    'ECI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Agriculture Geography',
    'GSI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Short Story in Tamil',
    'TMI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Comparative Studies: Christianity, Islam & Hinduism',
    'HCI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Sociology of Disaster Management',
    'SOI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),
(
    'Writing Skills in Tamil',
    'TTI 31013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    5,
    3
),

-- Year 03 Semester II
(
    'Information Security',
    'ITM 32013',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    6,
    3
),
(
    'Software Engineering',
    'ITM 32023',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    6,
    3
),
(
    'Research Methods for IT',
    'ITM 32033',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    6,
    3
),
(
    'Data Mining and Machine Learning',
    'ITM 32043',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    6,
    3
),
(
    'Enterprise Application Development',
    'ITM 32053',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    6,
    3
),

-- Year 04 Semester I
(
    'IT Project Management',
    'ITM 4113',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    7,
    3
),
(
    'Visual Programming',
    'ITM 4123',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    7,
    3
),
(
    'Computer Architecture',
    'ITM 4143',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    7,
    3
),

-- Year 04 Semester II
(
    'Industrial Training',
    'ITM 4223',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    8,
    3
),
(
    'Dissertation / Final Project',
    'ITM 4233',
    'Information Technology-Honours',
    'Faculty of Arts & Culture',
    8,
    3
);