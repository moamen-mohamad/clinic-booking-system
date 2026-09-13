# Clinic Booking System

A RESTful clinic appointment booking API built with **Pure PHP and MySQL**, without using Laravel or any PHP framework.

The system is designed for a clinic where a secretary manages appointments for a doctor. It handles patients, doctors, working days, available appointment slots, bookings, and appointment status transitions.

---

## Features

* Pure PHP 8.1+
* MySQL database
* Object-Oriented Programming (OOP)
* PSR-4 autoloading with Composer
* RESTful API
* PDO with prepared statements
* Patient management
* Doctor management
* Doctor working days
* Automatic appointment slot generation
* Appointment booking
* Prevention of double booking
* Appointment filtering by date and status
* Appointment status management
* Status transition validation
* JSON request/response handling
* Custom exceptions
* Enum for appointment statuses
* Interface for bookable resources
* Abstract class for shared person behavior
* Trait for shared database connection behavior
* Closure used in available-slot filtering

---

## Technologies

* PHP 8.1+
* MySQL
* PDO
* Composer
* REST API
* JSON

No framework is used.

---

## Project Structure

```text
clinic-booking-system/
│
├── composer.json
├── README.md
├── .gitignore
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── public/
│   └── index.php
│
└── src/
    ├── Contracts/
    │   └── Bookable.php
    │
    ├── Entities/
    │   ├── Appointment.php
    │   ├── Doctor.php
    │   ├── DoctorWorkingDay.php
    │   ├── Patient.php
    │   └── Person.php
    │
    ├── Enums/
    │   ├── BookingStatus.php
    │   └── DayOfWeek.php
    │
    ├── Exceptions/
    │   ├── InvalidStatusTransitionException.php
    │   ├── ResourceNotFoundException.php
    │   └── SlotNotAvailableException.php
    │
    ├── Http/
    │   ├── Controllers/
    │   │   ├── AppointmentController.php
    │   │   ├── DoctorController.php
    │   │   └── PatientController.php
    │   │
    │   ├── JsonResponse.php
    │   ├── Request.php
    │   └── Router.php
    │
    ├── Repositories/
    │   ├── AppointmentRepository.php
    │   ├── DoctorRepository.php
    │   ├── PatientRepository.php
    │   │
    │   └── Traits/
    │       └── InteractsWithDatabase.php
    │
    └── Services/
        └── BookingService.php
```

---

## Architecture

The project separates responsibilities into several layers:

### Entities

Represent the main domain objects:

* `Patient`
* `Doctor`
* `DoctorWorkingDay`
* `Appointment`
* `Person`

### Repositories

Responsible for database operations:

* `PatientRepository`
* `DoctorRepository`
* `AppointmentRepository`

### Services

Business logic is handled by:

* `BookingService`

It is responsible for:

* calculating available slots
* validating working hours
* validating appointment times
* preventing overlapping appointments
* creating appointments
* changing appointment status

### Controllers

Handle HTTP requests and return JSON responses:

* `PatientController`
* `DoctorController`
* `AppointmentController`

### HTTP Layer

Contains:

* `Request`
* `JsonResponse`
* `Router`

---

## OOP Concepts Used

### Interface

`Bookable` defines the behavior required from a bookable doctor:

```php
interface Bookable
{
    public function getSlotDurationMinutes(): int;

    public function getWorkStartTime(): string;

    public function getWorkEndTime(): string;
}
```

`Doctor` implements this interface.

---

### Abstract Class

`Person` is an abstract class containing common properties and behavior shared by people in the system.

`Patient` and `Doctor` extend it.

---

### Trait

`InteractsWithDatabase` provides shared database connection behavior for repositories.

It is used by multiple repositories.

---

### Enum

`BookingStatus` defines the allowed appointment states:

```text
pending
confirmed
canceled
completed
no_showed
```

It also controls valid status transitions.

---

### Closure

A Closure is used in `BookingService` to filter generated appointment slots according to their availability.

---

## Clinic Rules

The example doctor works according to the following rules:

* Working hours: `08:00 - 16:00`
* Appointment duration: `30 minutes`
* Friday: non-working day

Working days can also override the doctor's default working hours.

For example:

```text
Saturday: 08:00 - 16:00
Sunday:   10:00 - 14:00
Monday:   08:00 - 16:00
Tuesday:  08:00 - 16:00
Wednesday:08:00 - 16:00
Thursday: 08:00 - 16:00
Friday:   Non-working day
```

---

## Database

The project uses four main tables:

### patients

Stores patient information.

Important constraint:

```text
phone UNIQUE
```

Each patient must have a unique phone number.

### doctors

Stores doctor information and default working hours.

### doctor_working_days

Stores working days and optional day-specific working-hour overrides.

### appointments

Stores patient appointments, including:

* patient
* doctor
* start time
* end time
* status
* creation time

---

## Requirements

Before running the project, make sure you have:

* PHP 8.1 or newer
* MySQL
* Composer

XAMPP can be used as a local development environment.

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/moamen-mohamad/clinic-booking-system.git
```

Then:

```bash
cd clinic-booking-system
```

---

### 2. Install Composer dependencies

Run:

```bash
composer install
```

If the project has no external packages, Composer is still required for PSR-4 autoloading.

---

### 3. Create the database

Make sure MySQL is running.

The database is created automatically by:

```text
database/schema.sql
```

Using XAMPP on Windows:

```bash
C:\xampp\mysql\bin\mysql.exe -u root < database/schema.sql
```

If your MySQL user has a password:

```bash
C:\xampp\mysql\bin\mysql.exe -u root -p < database/schema.sql
```

---

### 4. Insert seed data

Run:

```bash
C:\xampp\mysql\bin\mysql.exe -u root < database/seed.sql
```

Or with a password:

```bash
C:\xampp\mysql\bin\mysql.exe -u root -p < database/seed.sql
```

The seed creates the example doctor, working days, and sample patients.

---

## Database Configuration

The database connection is currently configured in:

```text
public/index.php
```

Default local configuration:

```php
$databaseHost = '127.0.0.1';
$databaseName = 'clinic_booking_system';
$databaseUser = 'root';
$databasePassword = '';
```

Update these values if your local MySQL configuration is different.

---

## Running the API

From the project root:

```bash
php -S localhost:8000 -t public
```

The API will be available at:

```text
http://localhost:8000
```

---

# API Documentation

## Patients

### Get all patients

```http
GET /patients
```

Example:

```text
GET http://localhost:8000/patients
```

---

### Get patient by phone

```http
GET /patients?phone={phone}
```

Example:

```text
GET http://localhost:8000/patients?phone=0991111111
```

---

### Get patient by ID

```http
GET /patients/{id}
```

Example:

```text
GET http://localhost:8000/patients/1
```

---

### Create patient

```http
POST /patients
```

Body:

```json
{
    "name": "Salem Hasan",
    "phone": "0993333333"
}
```

Successful response:

```text
201 Created
```

A duplicate phone number returns:

```text
409 Conflict
```

---

# Doctors

### Get all doctors

```http
GET /doctors
```

Example:

```text
GET http://localhost:8000/doctors
```

---

### Get available appointment slots

```http
GET /doctors/{id}/slots?date={date}
```

Example:

```text
GET http://localhost:8000/doctors/1/slots?date=2026-09-12
```

Example response:

```json
{
    "data": [
        {
            "start_time": "08:00",
            "end_time": "08:30"
        },
        {
            "start_time": "08:30",
            "end_time": "09:00"
        }
    ]
}
```

A non-working day returns an error.

---

# Appointments

### Get all appointments

```http
GET /appointments
```

Example:

```text
GET http://localhost:8000/appointments
```

---

### Get appointments by date

```http
GET /appointments?date={date}
```

Example:

```text
GET http://localhost:8000/appointments?date=2026-09-12
```

---

### Get appointments by status

```http
GET /appointments?status={status}
```

Example:

```text
GET http://localhost:8000/appointments?status=pending
```

Available statuses:

```text
pending
confirmed
canceled
completed
no_showed
```

---

### Filter by date and status

```http
GET /appointments?date={date}&status={status}
```

Example:

```text
GET http://localhost:8000/appointments?date=2026-09-12&status=pending
```

---

### Get appointment by ID

```http
GET /appointments/{id}
```

Example:

```text
GET http://localhost:8000/appointments/1
```

---

### Create appointment

```http
POST /appointments
```

Body:

```json
{
    "patient_name": "Ali Mohamad",
    "patient_phone": "0994444444",
    "doctor_id": 1,
    "date": "2026-09-12",
    "time": "08:00"
}
```

The system automatically:

1. Validates the doctor.
2. Validates the working day.
3. Validates the requested slot.
4. Checks for overlapping appointments.
5. Finds the patient by phone.
6. Creates the patient if necessary.
7. Creates the appointment with `pending` status.

---

### Update appointment status

```http
PATCH /appointments/{id}/status
```

Example:

```text
PATCH http://localhost:8000/appointments/1/status
```

Body:

```json
{
    "status": "confirmed"
}
```

---

## Appointment Status Transitions

Allowed transitions:

```text
pending
   ├── confirmed
   └── canceled

confirmed
   ├── completed
   ├── no_showed
   └── canceled
```

Final states:

```text
canceled
completed
no_showed
```

Once an appointment reaches a final state, it cannot be changed to another status.

Invalid transitions return:

```text
422 Unprocessable Entity
```

---

# Error Handling

The API uses HTTP status codes to represent different error conditions.

| Status | Meaning                                  |
| ------ | ---------------------------------------- |
| `200`  | Successful request                       |
| `201`  | Resource created                         |
| `400`  | Invalid request or business validation   |
| `404`  | Resource not found                       |
| `409`  | Conflict, such as duplicate phone number |
| `422`  | Invalid appointment status transition    |
| `500`  | Internal server or database error        |

Errors are returned as JSON.

Example:

```json
{
    "message": "The selected time slot is already booked."
}
```

---

# Business Rules

The system prevents:

* booking on non-working days
* booking outside working hours
* booking at invalid times such as `08:15`
* double booking of the same time slot
* duplicate patient phone numbers
* invalid appointment status transitions

For example, if `08:00 - 08:30` is already booked, it will no longer appear in the available slots.

---

# Testing

The API was tested using Postman.

The tested scenarios include:

* Get doctors
* Get available slots
* Day-specific working hours
* Non-working day
* Get patients
* Search patient by phone
* Get patient by ID
* Create patient
* Duplicate patient phone
* Create appointment
* Available slots after booking
* Duplicate appointment
* Invalid appointment slot
* Appointment outside working hours
* Get appointments
* Filter appointments by date
* Filter appointments by status
* Filter by date and status
* Update appointment status
* Invalid status transitions

---

# API Endpoints Summary

| Method | Endpoint                                    | Description               |
| ------ | ------------------------------------------- | ------------------------- |
| GET    | `/patients`                                 | Get all patients          |
| GET    | `/patients?phone={phone}`                   | Find patient by phone     |
| GET    | `/patients/{id}`                            | Get patient               |
| POST   | `/patients`                                 | Create patient            |
| GET    | `/doctors`                                  | Get doctors               |
| GET    | `/doctors/{id}/slots?date={date}`           | Get available slots       |
| GET    | `/appointments`                             | Get appointments          |
| GET    | `/appointments?date={date}`                 | Filter by date            |
| GET    | `/appointments?status={status}`             | Filter by status          |
| GET    | `/appointments?date={date}&status={status}` | Filter by date and status |
| GET    | `/appointments/{id}`                        | Get appointment           |
| POST   | `/appointments`                             | Create appointment        |
| PATCH  | `/appointments/{id}/status`                 | Update appointment status |

---

## License

This project was created as a backend API assignment and learning project using Pure PHP and MySQL.
