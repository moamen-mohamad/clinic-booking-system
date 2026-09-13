<?php

namespace App\Services;

use App\Entities\Appointment;
use App\Entities\Patient;
use App\Enums\BookingStatus;
use App\Enums\DayOfWeek;
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;
use DateTimeImmutable;
use InvalidArgumentException;

class BookingService
{
    public function __construct(
        private PatientRepository $patientRepository,
        private DoctorRepository $doctorRepository,
        private AppointmentRepository $appointmentRepository
    ) {}

    public function getAvailableSlots(
        int $doctorId,
        string $date
    ): array {
        $dateObject = $this->parseDate($date);

        $workingHours = $this->getWorkingHours(
            doctorId: $doctorId,
            date: $dateObject
        );

        $dayStart = new DateTimeImmutable(
            $date . ' ' . $workingHours['start']
        );

        $dayEnd = new DateTimeImmutable(
            $date . ' ' . $workingHours['end']
        );

        $slots = [];
        $currentStart = $dayStart;

        while ($currentStart < $dayEnd) {
            $currentEnd = $currentStart->modify(
                '+' . $workingHours['duration'] . ' minutes'
            );

            if ($currentEnd > $dayEnd) {
                break;
            }

            $slots[] = [
                'start_time' => $currentStart,
                'end_time' => $currentEnd,
            ];

            $currentStart = $currentEnd;
        }

        $isAvailable = function (array $slot) use ($doctorId): bool {
            return $this->appointmentRepository->findOverlapping(
                doctorId: $doctorId,
                startTime: $slot['start_time'],
                endTime: $slot['end_time']
            ) === null;
        };

        return array_values(
            array_filter($slots, $isAvailable)
        );
    }

    public function book(
        string $patientName,
        string $patientPhone,
        int $doctorId,
        string $date,
        string $time
    ): Appointment {
        $dateObject = $this->parseDate($date);
        $time = $this->parseTime($time);

        $now = new DateTimeImmutable();
        $today = $now->setTime(0, 0, 0);

        // منع الحجز في تاريخ سابق
        if ($dateObject < $today) {
            throw new InvalidArgumentException(
                'Cannot book an appointment in the past.'
            );
        }

        $workingHours = $this->getWorkingHours(
            doctorId: $doctorId,
            date: $dateObject
        );

        $doctor = $workingHours['doctor'];

        $workStart = new DateTimeImmutable(
            $date . ' ' . $workingHours['start']
        );

        $workEnd = new DateTimeImmutable(
            $date . ' ' . $workingHours['end']
        );

        $startTime = new DateTimeImmutable(
            $date . ' ' . $time
        );

        // منع الحجز في وقت سابق من الوقت الحالي
        if ($startTime <= $now) {
            throw new InvalidArgumentException(
                'Cannot book an appointment in the past.'
            );
        }

        if (!$this->isValidSlot(
            requestedStart: $startTime,
            workStart: $workStart,
            workEnd: $workEnd,
            duration: $workingHours['duration']
        )) {
            throw new InvalidArgumentException(
                'The selected time is not a valid appointment slot.'
            );
        }

        $endTime = $startTime->modify(
            '+' . $workingHours['duration'] . ' minutes'
        );

        $overlappingAppointment =
            $this->appointmentRepository->findOverlapping(
                doctorId: $doctorId,
                startTime: $startTime,
                endTime: $endTime
            );

        if ($overlappingAppointment !== null) {
            throw new InvalidArgumentException(
                'The selected time slot is already booked.'
            );
        }

        $patient = $this->patientRepository->findByPhone(
            $patientPhone
        );

        if ($patient === null) {
            $patient = $this->patientRepository->create(
                new Patient(
                    name: $patientName,
                    phone: $patientPhone
                )
            );
        }

        $appointment = new Appointment(
            patientId: $patient->getId(),
            doctorId: $doctor->getId(),
            startTime: $startTime,
            endTime: $endTime,
            status: BookingStatus::Pending
        );

        return $this->appointmentRepository->create(
            $appointment
        );
    }

    public function changeStatus(
        int $appointmentId,
        BookingStatus $newStatus
    ): Appointment {
        $appointment = $this->appointmentRepository->findById(
            $appointmentId
        );

        if ($appointment === null) {
            throw new InvalidArgumentException(
                'Appointment not found.'
            );
        }

        $appointment->changeStatus($newStatus);

        return $this->appointmentRepository->updateStatus(
            appointmentId: $appointmentId,
            status: $newStatus
        );
    }

    private function getWorkingHours(
        int $doctorId,
        DateTimeImmutable $date
    ): array {
        $doctor = $this->doctorRepository->findById($doctorId);

        if ($doctor === null) {
            throw new InvalidArgumentException(
                'Doctor not found.'
            );
        }

        $dayOfWeek = DayOfWeek::from(
            strtolower($date->format('l'))
        );

        $workingDay = $this->doctorRepository->getWorkingDay(
            doctorId: $doctorId,
            dayOfWeek: $dayOfWeek
        );

        if ($workingDay === null) {
            throw new InvalidArgumentException(
                'Doctor does not work on this day.'
            );
        }

        return [
            'doctor' => $doctor,
            'start' => $workingDay->getStartTime()
                ?? $doctor->getWorkStartTime(),
            'end' => $workingDay->getEndTime()
                ?? $doctor->getWorkEndTime(),
            'duration' => $doctor->getSlotDurationMinutes(),
        ];
    }

    private function isValidSlot(
        DateTimeImmutable $requestedStart,
        DateTimeImmutable $workStart,
        DateTimeImmutable $workEnd,
        int $duration
    ): bool {
        if ($requestedStart < $workStart) {
            return false;
        }

        $currentSlot = $workStart;

        while ($currentSlot < $workEnd) {
            $currentSlotEnd = $currentSlot->modify(
                '+' . $duration . ' minutes'
            );

            if ($currentSlotEnd > $workEnd) {
                break;
            }

            if ($currentSlot == $requestedStart) {
                return true;
            }

            $currentSlot = $currentSlotEnd;
        }

        return false;
    }

    private function parseDate(string $date): DateTimeImmutable
    {
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

        return $dateObject;
    }

    private function parseTime(string $time): string
    {
        $timeObject = DateTimeImmutable::createFromFormat(
            '!H:i',
            $time
        );

        if (
            $timeObject === false ||
            $timeObject->format('H:i') !== $time
        ) {
            throw new InvalidArgumentException(
                'Invalid time format. Expected H:i.'
            );
        }

        return $time;
    }
}