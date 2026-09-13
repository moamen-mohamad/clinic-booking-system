<?php

namespace App\Entities;

class Patient extends Person
{
    public function __construct(
        string $name,
        string $phone,
        private ?int $id = null
    ) {
        parent::__construct($name, $phone);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): string
    {
        return 'patient';
    }
}