<?php

namespace App\Repositories;

use App\Entities\Doctor;
use App\Entities\DoctorWorkingDay;
use App\Enums\DayOfWeek;
use App\Repositories\Traits\InteractsWithDatabase;
use PDO;

class DoctorRepository
{
    use InteractsWithDatabase;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findAll(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name, phone, specialty, work_start_time, work_end_time, slot_duration_minutes FROM doctors ORDER BY name ASC'
        );

        $doctors = [];

        while ($doctor = $statement->fetch(PDO::FETCH_ASSOC)) {
            $doctors[] = new Doctor(
                name: $doctor['name'],
                phone: $doctor['phone'],
                specialty: $doctor['specialty'],
                workStartTime: $doctor['work_start_time'],
                workEndTime: $doctor['work_end_time'],
                slotDurationMinutes: (int) $doctor['slot_duration_minutes'],
                id: (int) $doctor['id']
            );
        }

        return $doctors;
    }

    public function findById(int $id): ?Doctor
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, phone, specialty, work_start_time, work_end_time, slot_duration_minutes FROM doctors WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $doctor = $statement->fetch(PDO::FETCH_ASSOC);

        if ($doctor === false) {
            return null;
        }

        return new Doctor(
            name: $doctor['name'],
            phone: $doctor['phone'],
            specialty: $doctor['specialty'],
            workStartTime: $doctor['work_start_time'],
            workEndTime: $doctor['work_end_time'],
            slotDurationMinutes: (int) $doctor['slot_duration_minutes'],
            id: (int) $doctor['id']
        );
    }
    public function getWorkingDaysByDoctorId(int $doctorId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, doctor_id, day_of_week, start_time, end_time FROM doctor_working_days WHERE doctor_id = :doctor_id ORDER BY id ASC'
        );

        $statement->execute([
            'doctor_id' => $doctorId,
        ]);

        $workingDays = [];

        while ($workingDay = $statement->fetch(PDO::FETCH_ASSOC)) {
            $workingDays[] = new DoctorWorkingDay(
                doctorId: (int) $workingDay['doctor_id'],
                dayOfWeek: DayOfWeek::from($workingDay['day_of_week']),
                startTime: $workingDay['start_time'],
                endTime: $workingDay['end_time'],
                id: (int) $workingDay['id']
            );
        }

        return $workingDays;
    }
    public function getWorkingDay(
        int $doctorId,
        DayOfWeek $dayOfWeek
    ): ?DoctorWorkingDay {
        $statement = $this->connection->prepare(
            'SELECT id, doctor_id, day_of_week, start_time, end_time FROM doctor_working_days WHERE doctor_id = :doctor_id AND day_of_week = :day_of_week LIMIT 1'
        );

        $statement->execute([
            'doctor_id' => $doctorId,
            'day_of_week' => $dayOfWeek->value,
        ]);

        $workingDay = $statement->fetch(PDO::FETCH_ASSOC);

        if ($workingDay === false) {
            return null;
        }

        return new DoctorWorkingDay(
            doctorId: (int) $workingDay['doctor_id'],
            dayOfWeek: DayOfWeek::from($workingDay['day_of_week']),
            startTime: $workingDay['start_time'],
            endTime: $workingDay['end_time'],
            id: (int) $workingDay['id']
        );
    }
}
