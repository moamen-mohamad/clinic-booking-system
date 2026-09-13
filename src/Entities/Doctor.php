<?php
namespace App\Entities;

use App\Contracts\Bookable;

class Doctor extends Person implements Bookable
{
    public function __construct(
        string $name,
        string $phone,
        private string $specialty,
        private string $workStartTime,
        private string $workEndTime,
        private int $slotDurationMinutes,
        private ?int $id = null
    ) {
        parent::__construct($name, $phone);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSpecialty(): string
    {
        return $this->specialty;
    }

    public function getWorkStartTime(): string
    {
        return $this->workStartTime;
    }

    public function getWorkEndTime(): string
    {
        return $this->workEndTime;
    }

    public function getSlotDurationMinutes(): int
    {
        return $this->slotDurationMinutes;
    }

    public function getRole(): string
    {
        return 'doctor';
    }

}