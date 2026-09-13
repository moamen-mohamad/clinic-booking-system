CREATE DATABASE IF NOT EXISTS clinic_booking_system CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE clinic_booking_system;

CREATE TABLE
    patients (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(30) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

CREATE TABLE
    doctors (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        specialty VARCHAR(255) NOT NULL,
        work_start_time TIME NOT NULL,
        work_end_time TIME NOT NULL,
        slot_duration_minutes SMALLINT UNSIGNED NOT NULL
    );

CREATE TABLE
    doctor_working_days (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT UNSIGNED NOT NULL,
        day_of_week ENUM (
            'saturday',
            'sunday',
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday'
        ) NOT NULL,
        start_time TIME NULL,
        end_time TIME NULL,
        CONSTRAINT fk_working_days_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT uq_doctor_working_day UNIQUE (doctor_id, day_of_week)
    );

CREATE TABLE
    appointments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_id INT UNSIGNED NOT NULL,
        doctor_id INT UNSIGNED NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        status ENUM (
            'pending',
            'confirmed',
            'canceled',
            'completed',
            'no_showed'
        ) NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id) REFERENCES patients (id) ON DELETE RESTRICT ON UPDATE CASCADE,
        CONSTRAINT fk_appointments_doctor FOREIGN KEY (doctor_id) REFERENCES doctors (id) ON DELETE RESTRICT ON UPDATE CASCADE,
        INDEX idx_appointments_doctor_start_status (doctor_id, start_time, status),
        INDEX idx_appointments_start_time (start_time)
    );