<?php

namespace App\Repositories;

use App\Entities\Appointment;
use App\Enums\BookingStatus;
use App\Repositories\Traits\InteractsWithDatabase;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;

class AppointmentRepository
{
    use InteractsWithDatabase;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findAll(?DateTimeImmutable $date = null): array
    {
        $sql = 'SELECT id, patient_id, doctor_id, start_time, end_time, status FROM appointments';

        $params = [];

        if ($date !== null) {
            $startOfDay = $date->setTime(0, 0, 0);
            $endOfDay = $startOfDay->modify('+1 day');

            $sql .= ' WHERE start_time >= :start_of_day AND start_time < :end_of_day';

            $params = [
                'start_of_day' => $startOfDay->format('Y-m-d H:i:s'),
                'end_of_day' => $endOfDay->format('Y-m-d H:i:s'),
            ];
        }

        $sql .= ' ORDER BY start_time ASC';

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        $appointments = [];

        while ($appointment = $statement->fetch(PDO::FETCH_ASSOC)) {
            $appointments[] = $this->mapToEntity($appointment);
        }

        return $appointments;
    }

    public function findById(int $id): ?Appointment
    {
        $statement = $this->connection->prepare(
            'SELECT id, patient_id, doctor_id, start_time, end_time, status FROM appointments WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $appointment = $statement->fetch(PDO::FETCH_ASSOC);

        if ($appointment === false) {
            return null;
        }

        return $this->mapToEntity($appointment);
    }

    /**
     * @param array<BookingStatus> $statuses
     */
    public function findByStatuses(array $statuses, ?DateTimeImmutable $date = null): array
    {
        if (empty($statuses)) {
            return [];
        }

        $sql = 'SELECT id, patient_id, doctor_id, start_time, end_time, status FROM appointments WHERE ';
        $params = [];

        $statusPlaceholders = [];
        foreach ($statuses as $index => $status) {
            $key = "status_{$index}";
            $statusPlaceholders[] = ":{$key}";
            $params[$key] = $status->value;
        }
        $sql .= 'status IN (' . implode(', ', $statusPlaceholders) . ')';

        if ($date !== null) {
            $startOfDay = $date->setTime(0, 0, 0);
            $endOfDay = $startOfDay->modify('+1 day');

            $sql .= ' AND start_time >= :start_of_day AND start_time < :end_of_day';
            $params['start_of_day'] = $startOfDay->format('Y-m-d H:i:s');
            $params['end_of_day'] = $endOfDay->format('Y-m-d H:i:s');
        }

        $sql .= ' ORDER BY start_time ASC';

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        $appointments = [];
        while ($appointment = $statement->fetch(PDO::FETCH_ASSOC)) {
            $appointments[] = $this->mapToEntity($appointment);
        }

        return $appointments;
    }

    public function findOverlapping(
        int $doctorId,
        DateTimeImmutable $startTime,
        DateTimeImmutable $endTime
    ): ?Appointment {
        $statement = $this->connection->prepare(
            'SELECT id, patient_id, doctor_id, start_time, end_time,
             status FROM appointments WHERE doctor_id = :doctor_id
             AND start_time < :end_time AND end_time > :start_time
             AND status IN (:pending, :confirmed) LIMIT 1'
        );

        $statement->execute([
            'doctor_id' => $doctorId,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'pending' => BookingStatus::Pending->value,
            'confirmed' => BookingStatus::Confirmed->value,
        ]);

        $appointment = $statement->fetch(PDO::FETCH_ASSOC);

        if ($appointment === false) {
            return null;
        }

        return $this->mapToEntity($appointment);
    }

    public function create(Appointment $appointment): Appointment
    {
        $statement = $this->connection->prepare(
            'INSERT INTO appointments (patient_id, doctor_id, start_time, end_time, status)
             VALUES (:patient_id, :doctor_id, :start_time, :end_time, :status)'
        );

        $statement->execute([
            'patient_id' => $appointment->getPatientId(),
            'doctor_id' => $appointment->getDoctorId(),
            'start_time' => $appointment->getStartTime()->format('Y-m-d H:i:s'),
            'end_time' => $appointment->getEndTime()->format('Y-m-d H:i:s'),
            'status' => $appointment->getStatus()->value,
        ]);

        return new Appointment(
            patientId: $appointment->getPatientId(),
            doctorId: $appointment->getDoctorId(),
            startTime: $appointment->getStartTime(),
            endTime: $appointment->getEndTime(),
            status: $appointment->getStatus(),
            id: (int) $this->connection->lastInsertId()
        );
    }

    public function updateStatus(
        int $appointmentId,
        BookingStatus $status
    ): Appointment {
        $statement = $this->connection->prepare(
            'UPDATE appointments SET status = :status WHERE id = :id'
        );

        $statement->execute([
            'id' => $appointmentId,
            'status' => $status->value,
        ]);

        $appointment = $this->findById($appointmentId);

        if ($appointment === null) {
            throw new InvalidArgumentException(
                'Appointment not found.'
            );
        }

        return $appointment;
    }

    private function mapToEntity(array $data): Appointment
    {
        return new Appointment(
            patientId: (int) $data['patient_id'],
            doctorId: (int) $data['doctor_id'],
            startTime: new DateTimeImmutable($data['start_time']),
            endTime: new DateTimeImmutable($data['end_time']),
            status: BookingStatus::from($data['status']),
            id: (int) $data['id']
        );
    }
}
