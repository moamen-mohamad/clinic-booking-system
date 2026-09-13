<?php

namespace App\Http\Controllers;

use App\Entities\Patient;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Repositories\PatientRepository;

class PatientController
{
    public function __construct(
        private PatientRepository $patientRepository,
        private Request $request
    ) {}

    public function index(): void
    {
        $phone = $this->request->getQuery('phone');

        if ($phone !== null) {
            $patient = $this->patientRepository->findByPhone($phone);

            if ($patient === null) {
                JsonResponse::send([
                    'message' => 'Patient not found.'
                ], 404);

                return;
            }

            JsonResponse::send([
                'data' => [
                    'id' => $patient->getId(),
                    'name' => $patient->getName(),
                    'phone' => $patient->getPhone(),
                ]
            ]);

            return;
        }

        $patients = $this->patientRepository->findAll();

        $data = array_map(
            function (Patient $patient): array {
                return [
                    'id' => $patient->getId(),
                    'name' => $patient->getName(),
                    'phone' => $patient->getPhone(),
                ];
            },
            $patients
        );

        JsonResponse::send([
            'data' => $data
        ]);
    }

    public function show(int $id): void
    {
        $patient = $this->patientRepository->findById($id);

        if ($patient === null) {
            JsonResponse::send([
                'message' => 'Patient not found.'
            ], 404);

            return;
        }

        JsonResponse::send([
            'data' => [
                'id' => $patient->getId(),
                'name' => $patient->getName(),
                'phone' => $patient->getPhone(),
            ]
        ]);
    }

    public function store(): void
    {
        $data = $this->request->getJsonBody();

        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if ($name === '' || $phone === '') {
            JsonResponse::send([
                'message' => 'Name and phone are required.'
            ], 400);

            return;
        }

        $existingPatient = $this->patientRepository->findByPhone($phone);

        if ($existingPatient !== null) {
            JsonResponse::send([
                'message' => 'A patient with this phone number already exists.'
            ], 409);

            return;
        }

        $patient = $this->patientRepository->create(
            new Patient(
                name: $name,
                phone: $phone
            )
        );

        JsonResponse::send([
            'message' => 'Patient created successfully.',
            'data' => [
                'id' => $patient->getId(),
                'name' => $patient->getName(),
                'phone' => $patient->getPhone(),
            ]
        ], 201);
    }
}
