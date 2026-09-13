<?php

declare(strict_types=1);

use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Http\Router;
use App\Repositories\AppointmentRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\PatientRepository;
use App\Services\BookingService;

require_once __DIR__ . '/../vendor/autoload.php';
date_default_timezone_set('Asia/Damascus');

try {
    /*
     * Database connection
     */
    $databaseHost = '127.0.0.1';
    $databaseName = 'clinic_booking_system';
    $databaseUser = 'root';
    $databasePassword = '';

    $connection = new PDO(
        "mysql:host={$databaseHost};dbname={$databaseName};charset=utf8mb4",
        $databaseUser,
        $databasePassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    /*
     * Repositories
     */
    $patientRepository = new PatientRepository(
        $connection
    );

    $doctorRepository = new DoctorRepository(
        $connection
    );

    $appointmentRepository = new AppointmentRepository(
        $connection
    );

    /*
     * Services
     */
    $bookingService = new BookingService(
        patientRepository: $patientRepository,
        doctorRepository: $doctorRepository,
        appointmentRepository: $appointmentRepository
    );

    /*
     * HTTP dependencies
     */
    $request = new Request();

    /*
     * Controllers
     */
    $patientController = new PatientController(
        patientRepository: $patientRepository,
        request: $request
    );

    $doctorController = new DoctorController(
        doctorRepository: $doctorRepository,
        bookingService: $bookingService,
        request: $request
    );

    $appointmentController = new AppointmentController(
        appointmentRepository: $appointmentRepository,
        bookingService: $bookingService,
        request: $request
    );

    /*
     * Router
     */
    $router = new Router();

    /*
     * Patients
     */
    $router->get(
        '/patients',
        [$patientController, 'index']
    );

    $router->get(
        '/patients/{id}',
        [$patientController, 'show']
    );

    $router->post(
        '/patients',
        [$patientController, 'store']
    );

    /*
     * Doctors
     */
    $router->get(
        '/doctors',
        [$doctorController, 'index']
    );

    $router->get(
        '/doctors/{id}/slots',
        [$doctorController, 'slots']
    );

    /*
     * Appointments
     */
    $router->get(
        '/appointments',
        [$appointmentController, 'index']
    );

    $router->get(
        '/appointments/{id}',
        [$appointmentController, 'show']
    );

    $router->post(
        '/appointments',
        [$appointmentController, 'store']
    );

    $router->patch(
        '/appointments/{id}/status',
        [$appointmentController, 'updateStatus']
    );

    /*
     * Dispatch request
     */
    $router->dispatch(
        $request->getMethod(),
        $_SERVER['REQUEST_URI'] ?? '/'
    );
} catch (InvalidStatusTransitionException $exception) {
    JsonResponse::send([
        'message' => $exception->getMessage()
    ], 422);
} catch (InvalidArgumentException $exception) {
    JsonResponse::send([
        'message' => $exception->getMessage()
    ], 400);
} catch (PDOException $exception) {
    JsonResponse::send([
        'message' => 'Database error.'
    ], 500);
} catch (Throwable $exception) {
    JsonResponse::send([
        'message' => 'Internal server error.'
    ], 500);
}
