USE clinic_booking_system;

INSERT INTO doctors (
    name,
    phone,
    specialty,
    work_start_time,
    work_end_time,
    slot_duration_minutes
)
VALUES (
    'Dr. Samer',
    '0990000000',
    'Internal Medicine',
    '08:00:00',
    '16:00:00',
    30
);

INSERT INTO doctor_working_days (
    doctor_id,
    day_of_week,
    start_time,
    end_time
)
VALUES
    (1, 'saturday', '08:00:00', '16:00:00'),
    (1, 'sunday', '10:00:00', '14:00:00'),
    (1, 'monday', NULL, NULL),
    (1, 'tuesday', NULL, NULL),
    (1, 'wednesday', NULL, NULL),
    (1, 'thursday', NULL, NULL);

INSERT INTO patients (
    name,
    phone
)
VALUES
    ('Mohamad Mansor', '0991111111'),
    ('Ahmad Habash', '0992222222');