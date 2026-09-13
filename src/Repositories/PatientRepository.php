<?php

namespace App\Repositories;

use App\Entities\Patient;
use App\Repositories\Traits\InteractsWithDatabase;
use PDO;

class PatientRepository
{
    use InteractsWithDatabase;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function findAll(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name, phone, created_at FROM patients ORDER BY name ASC'
        );

        $patients = [];

        while ($patient = $statement->fetch(PDO::FETCH_ASSOC)) {
            $patients[] = new Patient(
                name: $patient['name'],
                phone: $patient['phone'],
                id: (int) $patient['id']
            );
        }

        return $patients;
    }

    public function findById(int $id): ?Patient
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, phone FROM patients WHERE id = :id LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
        ]);

        $patient = $statement->fetch(PDO::FETCH_ASSOC);

        if ($patient === false) {
            return null;
        }

        return new Patient(
            name: $patient['name'],
            phone: $patient['phone'],
            id: (int) $patient['id']
        );
    }

    public function findByPhone(string $phone): ?Patient
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, phone FROM patients WHERE phone = :phone LIMIT 1'
        );

        $statement->execute([
            'phone' => $phone,
        ]);

        $patient = $statement->fetch(PDO::FETCH_ASSOC);

        if ($patient === false) {
            return null;
        }

        return new Patient(
            name: $patient['name'],
            phone: $patient['phone'],
            id: (int) $patient['id']
        );
    }

    public function create(Patient $patient): Patient
    {
        $statement = $this->connection->prepare(
            'INSERT INTO patients (name, phone) VALUES (:name, :phone)'
        );

        $statement->execute([
            'name' => $patient->getName(),
            'phone' => $patient->getPhone(),
        ]);

        return new Patient(
            name: $patient->getName(),
            phone: $patient->getPhone(),
            id: (int) $this->connection->lastInsertId()
        );
    }
}
