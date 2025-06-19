<?php

require './vendor/autoload.php';

use src\repository\LogRepository;
use src\repository\StudentRepository;
use src\service\LogService;
use src\service\StudentService;

// Affichage du menu
function menu(): void
{
    echo "
       _             _ _             _
   ___| |_ _   _  __| (_) __ _ _ __ | |_ ___
  / _ \ __| | | |/ _` | |/ _` | '_ \| __/ __|
 |  __/ |_| |_| | (_| | | (_| | | | | |_\__ \
  \___|\__|\__,_|\__,_|_|\__,_|_| |_|\__|___/" . PHP_EOL;

    echo "1. Afficher les étudiants
2. Créer un étudiant
3. Editer un étudiant
4. Supprimer un étudiant
5. Chercher par nom ou prénom
6. Afficher les logs
7. Vider les logs
8. Quitter" . PHP_EOL;
}

// CONTROLLER
$studentRepo = new StudentRepository();
$logRepository = new LogRepository();
$logService = new LogService($logRepository);
$studentService = new StudentService($studentRepo, $logRepository);

/// VIEW
while (true) {
    menu();
    match (readline("Votre choix : ")) {
        "1" => $studentService->getStudents(),
        "2" => $studentService->createStudent(),
        "3" => $studentService->editStudent(),
        "4" => $studentService->deleteStudent(),
        "5" => $studentService->searchStudentsByIdentity(),
        "6" => $logService->getTenLastLogs(),
        "7" => $logService->clearLogs(),
        "8" => exit(),
        default => print("saisie invalide"),
    };

    echo "\n---Press enter to continue---\n";
    readline();
}