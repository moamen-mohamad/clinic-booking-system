<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use App\Http\Request;
use App\Repositories\DoctorRepository;
use App\Services\BookingService;
use DateTimeImmutable;
use InvalidArgumentException;

class DoctorController
{
    public function __construct(
        private DoctorRepository $doctorRepository,
        private BookingService $bookingService,
        private Request $request
    ) {
    }

    public function index(): void
    {
        $doctors = $this->doctorRepository->findAll();

        $data = array_map(
            function ($doctor): array {
                return [
                    'id' => $doctor->getId(),
                    'name' => $doctor->getName(),
                    'specialty' => $doctor->getSpecialty(),
                    'work_start_time' => $doctor->getWorkStartTime(),
                    'work_end_time' => $doctor->getWorkEndTime(),
                    'slot_duration_minutes' =>
                        $doctor->getSlotDurationMinutes(),
                ];
            },
            $doctors
        );

        JsonResponse::send([
            'data' => $data
        ]);
    }

    public function slots(int $doctorId): void
    {
        $date = $this->request->getQuery('date');

        if ($date === null) {
            throw new InvalidArgumentException(
                'The date query parameter is required.'
            );
        }

        $slots = $this->bookingService->getAvailableSlots(
            doctorId: $doctorId,
            date: $date
        );

        $data = array_map(
            function (array $slot): array {
                return [
                    'start_time' => $slot['start_time']->format('H:i'),
                    'end_time' => $slot['end_time']->format('H:i'),
                ];
            },
            $slots
        );

        JsonResponse::send([
            'data' => $data
        ]);
    }
}