<?php

namespace App\Entities;

abstract class Person
{
    public function __construct(
        protected string $name,
        protected string $phone
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    abstract public function getRole(): string;
}