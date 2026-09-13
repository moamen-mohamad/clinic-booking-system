<?php

namespace App\Contracts;

interface Bookable
{
    public function getSlotDurationMinutes(): int;

    public function getWorkStartTime(): string;

    public function getWorkEndTime(): string;
}