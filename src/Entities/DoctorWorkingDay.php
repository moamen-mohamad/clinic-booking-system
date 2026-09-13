<?php

namespace App\Entities;

use App\Enums\DayOfWeek;

class DoctorWorkingDay
{
    public function __construct(
        private int $doctorId,
        private DayOfWeek $dayOfWeek,
        private ?string $startTime = null,
        private ?string $endTime = null,
        private ?int $id = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoctorId(): int
    {
        return $this->doctorId;
    }

    public function getDayOfWeek(): DayOfWeek
    {
        return $this->dayOfWeek;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }
}