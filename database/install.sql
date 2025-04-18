-- Create database
CREATE DATABASE IF NOT EXISTS technical_resume;
USE technical_resume;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Resumes table
CREATE TABLE IF NOT EXISTS resumes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    profession VARCHAR(100) NOT NULL,
    availability VARCHAR(50),
    location VARCHAR(100),
    experience_summary TEXT,
    other_info TEXT,
    photo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Skills table
CREATE TABLE IF NOT EXISTS skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT NOT NULL,
    technology VARCHAR(100) NOT NULL,
    level INT NOT NULL,
    years INT NOT NULL,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);

-- Experiences table
CREATE TABLE IF NOT EXISTS experiences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT NOT NULL,
    company VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    start_date DATE,
    end_date DATE,
    main_activities TEXT,
    technologies TEXT,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);

-- Education table
CREATE TABLE IF NOT EXISTS education (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT NOT NULL,
    institution VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    additional_info TEXT,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);

-- Certifications table
CREATE TABLE IF NOT EXISTS certifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    institution VARCHAR(100) NOT NULL,
    year INT,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);

-- Languages table
CREATE TABLE IF NOT EXISTS languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resume_id INT NOT NULL,
    language VARCHAR(50) NOT NULL,
    level VARCHAR(20) NOT NULL,
    FOREIGN KEY (resume_id) REFERENCES resumes(id) ON DELETE CASCADE
);

-- Create uploads directory
-- Note: Execute this command in your system:
-- mkdir -p uploads && chmod 777 uploads