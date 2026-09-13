<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Repositories\AppointmentRepository;
use App\Services\BookingService;
use DateTimeImmutable;
use InvalidArgumentException;

class AppointmentController
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private BookingService $bookingService,
        private Request $request
    ) {
    }

    public function index(): void
    {
        $date = $this->request->getQuery('date');
        $status = $this->request->getQuery('status');

        $dateObject = null;

        if ($date !== null) {
            $dateObject = DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $date
            );

            if (
                $dateObject === false ||
                $dateObject->format('Y-m-d') !== $date
            ) {
                throw new InvalidArgumentException(
                    'Invalid date format. Expected Y-m-d.'
                );
            }
        }

        if ($status !== null) {
            $statuses = $this->parseStatuses($status);

            $appointments = $this->appointmentRepository
                ->findByStatuses(
                    statuses: $statuses,
                    date: $dateObject
                );
        } else {
            $appointments = $this->appointmentRepository
                ->findAll($dateObject);
        }

        $data = array_map(
            function ($appointment): array {
                return [
                    'id' => $appointment->getId(),
                    'patient_id' => $appointment->getPatientId(),
                    'doctor_id' => $appointment->getDoctorId(),
                    'start_time' =>
                        $appointment
                            ->getStartTime()
                            ->format('Y-m-d H:i:s'),
                    'end_time' =>
                        $appointment
                            ->getEndTime()
                            ->format('Y-m-d H:i:s'),
                    'status' =>
                        $appointment->getStatus()->value,
                ];
            },
            $appointments
        );

        JsonResponse::send([
            'data' => $data
        ]);
    }

    public function show(int $id): void
    {
        $appointment = $this->appointmentRepository->findById($id);

        if ($appointment === null) {
            JsonResponse::send([
                'message' => 'Appointment not found.'
            ], 404);

            return;
        }

        JsonResponse::send([
            'data' => [
                'id' => $appointment->getId(),
                'patient_id' => $appointment->getPatientId(),
                'doctor_id' => $appointment->getDoctorId(),
                'start_time' =>
                    $appointment
                        ->getStartTime()
                        ->format('Y-m-d H:i:s'),
                'end_time' =>
                    $appointment
                        ->getEndTime()
                        ->format('Y-m-d H:i:s'),
                'status' =>
                    $appointment->getStatus()->value,
            ]
        ]);
    }

    public function store(): void
    {
        $data = $this->request->getJsonBody();

        $patientName = trim($data['patient_name'] ?? '');
        $patientPhone = trim($data['patient_phone'] ?? '');
        $doctorId = $data['doctor_id'] ?? null;
        $date = trim($data['date'] ?? '');
        $time = trim($data['time'] ?? '');

        if (
            $patientName === '' ||
            $patientPhone === '' ||
            $doctorId === null ||
            $date === '' ||
            $time === ''
        ) {
            throw new InvalidArgumentException(
                'patient_name, patient_phone, doctor_id, date and time are required.'
            );
        }

        if (
            !is_int($doctorId) &&
            !ctype_digit((string) $doctorId)
        ) {
            throw new InvalidArgumentException(
                'doctor_id must be an integer.'
            );
        }

        $appointment = $this->bookingService->book(
            patientName: $patientName,
            patientPhone: $patientPhone,
            doctorId: (int) $doctorId,
            date: $date,
            time: $time
        );

        JsonResponse::send([
            'message' => 'Appointment created successfully.',
            'data' => [
                'id' => $appointment->getId(),
                'patient_id' => $appointment->getPatientId(),
                'doctor_id' => $appointment->getDoctorId(),
                'start_time' =>
                    $appointment
                        ->getStartTime()
                        ->format('Y-m-d H:i:s'),
                'end_time' =>
                    $appointment
                        ->getEndTime()
                        ->format('Y-m-d H:i:s'),
                'status' =>
                    $appointment->getStatus()->value,
            ]
        ], 201);
    }

    public function updateStatus(int $id): void
    {
        $data = $this->request->getJsonBody();

        $status = trim($data['status'] ?? '');

        if ($status === '') {
            throw new InvalidArgumentException(
                'Status is required.'
            );
        }

        try {
            $newStatus = BookingStatus::from($status);
        } catch (\ValueError) {
            throw new InvalidArgumentException(
                'Invalid booking status.'
            );
        }

        $appointment = $this->bookingService->changeStatus(
            appointmentId: $id,
            newStatus: $newStatus
        );

        JsonResponse::send([
            'message' => 'Appointment status updated successfully.',
            'data' => [
                'id' => $appointment->getId(),
                'status' => $appointment->getStatus()->value,
            ]
        ]);
    }

    /**
     * @return array<BookingStatus>
     */
    private function parseStatuses(string $status): array
    {
        $statusValues = array_filter(
            array_map(
                'trim',
                explode(',', $status)
            )
        );

        if (empty($statusValues)) {
            throw new InvalidArgumentException(
                'At least one status is required.'
            );
        }

        $statuses = [];

        foreach ($statusValues as $statusValue) {
            try {
                $statuses[] = BookingStatus::from($statusValue);
            } catch (\ValueError) {
                throw new InvalidArgumentException(
                    "Invalid booking status: {$statusValue}."
                );
            }
        }

        return $statuses;
    }
}