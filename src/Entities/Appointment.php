<?php

namespace App\Entities;

use App\Enums\BookingStatus;
use App\Exceptions\InvalidStatusTransitionException;
use DateTimeImmutable;
use InvalidArgumentException;

class Appointment
{
    public function __construct(
        private int $patientId,
        private int $doctorId,
        private DateTimeImmutable $startTime,
        private DateTimeImmutable $endTime,
        private BookingStatus $status = BookingStatus::Pending,
        private ?int $id = null
    ) {
        if ($this->endTime <= $this->startTime) {
            throw new InvalidArgumentException(
                'Appointment end time must be after start time.'
            );
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatientId(): int
    {
        return $this->patientId;
    }

    public function getDoctorId(): int
    {
        return $this->doctorId;
    }

    public function getStartTime(): DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getEndTime(): DateTimeImmutable
    {
        return $this->endTime;
    }

    public function getStatus(): BookingStatus
    {
        return $this->status;
    }

    public function changeStatus(BookingStatus $newStatus): void
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException(
                "Invalid transition from {$this->status->value} to {$newStatus->value}."
            );
        }

        $this->status = $newStatus;
    }
}