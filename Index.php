<?php
require_once __DIR__ . '/vendor/autoload.php';

use src\controller\StudentController;
use src\service\StudentService;
use src\repository\LogRepository;
use src\repository\StudentRepository;
use src\service\LogService;


// CONTROLLER
//$studentRepo = new StudentRepository();
//$logRepository = new LogRepository();
//$logService = new LogService($logRepository);
//$studentService = new StudentService($studentRepo, $logRepository);
//$studentController = new StudentController($studentService);

/// VIEW
//while (true) {
//    menu();
//    match ($_GET["page"]) {
//        "1" => $studentController->displayStudents(),
//
//        "2" => $studentService->createStudent(),
//        "3" => $studentService->editStudent(),
//        "4" => $studentService->deleteStudent(),
//        "5" => $studentService->searchStudentsByIdentity(),
//        "6" => $logService->getTenLastLogs(),
//        "7" => $logService->clearLogs(),
//        "8" => exit(),
//        default => print("saisie invalide"),
//    };
//
//    echo "\n---Press enter to continue---\n";
//    readline();
//}

//$studentController->displayStudents();

try {
    $studentRepository = new StudentRepository();
    $logRepository = new LogRepository();
    $studentService = new StudentService($studentRepository, $logRepository);
    $controller = new StudentController($studentService);

    // Gestion des routes
    if (isset($_GET['page'])) {
        switch ($_GET['page']) {
            case 'displayByName':
                if (isset($_GET['name'])) {
                    $controller->displayByName($_GET['name']);
                } else {
                    $controller->displayStudents();
                }
                break;
            default:
                $controller->displayStudents();
                break;
        }
    } else {
        $controller->displayStudents();
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}