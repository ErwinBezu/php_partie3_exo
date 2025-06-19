<?php

namespace src\service;

use PDOException;
use src\enum\LogType;
use src\model\Log;
use src\model\Student;
use src\repository\LogRepository;
use src\repository\StudentRepository;

class StudentService
{
    // Définition des regex à utiliser sous forme de constantes
    const DATE_PATTERN = "/^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/";
    const EMAIL_PATTERN = "/^[\w\-\.]+@([\w-]+\.)+[\w-]{2,}$/";

    public function __construct(
        private StudentRepository $studentRepository,
        private LogRepository $logRepository
    ){}

    function insertLog(LogType $logType, string $operation, string $message): void{
        $this->logRepository->insert(new Log(null, $logType, $operation, $message));
    }

    // Permet d'afficher les étudiants
    function getStudents(): array
    {
        $students = [];
        try{
            $students = $this->studentRepository->findAll();
            $this->insertLog(LogType::DEBUG, "Affichage", "Affichage de tous les étudiants");
        } catch (PDOException $e) {
            $this->insertLog(LogType::ERR, "Affichage", "Echec de l'affichage de tous les étudiants");
            throw new \Exception("Erreur lors de la récupération des étudiants : " . $e->getMessage());
        }

        return $students;
    }

    // Ajouter un étudiant (version web)
    function addStudent(Student $student): bool
    {
        try {
            $success = $this->studentRepository->save($student);
            if ($success) {
                $this->insertLog(LogType::DEBUG, "Création", "Création d'un étudiant");
            }
            return $success;
        } catch (PDOException $e) {
            $this->insertLog(LogType::ERR, "Création", "Erreur lors de la création d'un étudiant");
            throw new \Exception("Erreur lors de l'ajout de l'étudiant : " . $e->getMessage());
        }
    }

    // Modifier un étudiant (version web)
    function updateStudent(Student $student): bool
    {
        try {
            // Vérifier si l'étudiant existe
            $existingStudent = $this->studentRepository->findById($student->getId());
            if ($existingStudent === false) {
                throw new \Exception("Étudiant non trouvé avec l'ID : " . $student->getId());
            }

            $success = $this->studentRepository->update($student);
            if ($success) {
                $this->insertLog(LogType::DEBUG, "Modification", "Étudiant d'ID " . $student->getId() . " modifié");
            }
            return $success;
        } catch (PDOException $e) {
            $this->insertLog(LogType::ERR, "Modification", "Erreur lors de la modification de l'étudiant");
            throw new \Exception("Erreur lors de la modification de l'étudiant : " . $e->getMessage());
        }
    }

    // Supprimer un étudiant par son id (version web)
    function deleteStudent(int $id): bool
    {
        try {
            // Vérifier si l'étudiant existe
            $existingStudent = $this->studentRepository->findById($id);
            if ($existingStudent === false) {
                throw new \Exception("Étudiant non trouvé avec l'ID : " . $id);
            }

            $success = $this->studentRepository->deleteById($id);
            if ($success) {
                $this->insertLog(LogType::DEBUG, "Suppression", "Étudiant d'ID $id supprimé");
            }
            return $success;
        } catch (PDOException $e) {
            $this->insertLog(LogType::ERR, "Suppression", "Impossible de supprimer l'étudiant d'ID $id");
            throw new \Exception("Erreur lors de la suppression de l'étudiant : " . $e->getMessage());
        }
    }

    // Récupérer un étudiant par son ID
    function getStudentById(int $id): ?Student
    {
        try {
            $student = $this->studentRepository->findById($id);
            if ($student !== false) {
                $this->insertLog(LogType::DEBUG, "Recherche par ID", "Étudiant d'ID $id trouvé");
                return $student;
            }
            return null;
        } catch (PDOException $e) {
            $this->insertLog(LogType::ERR, "Recherche par ID", "Étudiant d'ID $id introuvable");
            throw new \Exception("Erreur lors de la recherche de l'étudiant : " . $e->getMessage());
        }
    }

    // Rechercher des étudiants par nom/prénom
    function getStudentsByName(string $name): array
    {
        $input = '%' . $name . '%';
        $students = [];
        try{
            $students = $this->studentRepository->findAllByName($input);
            $this->insertLog(LogType::DEBUG, "Recherche par nom", "Étudiants avec '$name' dans le nom trouvés");
        } catch (PDOException $e) {
            $this->insertLog(LogType::ERR, "Recherche par nom", "Étudiants avec '$name' dans le nom introuvables");
            throw new \Exception("Erreur lors de la recherche par nom : " . $e->getMessage());
        }

        return $students;
    }

    // Validation des données d'étudiant
    public function validateStudentData(array $data, bool $requireId = false): bool
    {
        if ($requireId && (empty($data['id']) || !is_numeric($data['id']))) {
            throw new \Exception('ID requis pour la modification');
        }

        if (empty($data['firstname']) || empty($data['lastname']) ||
            empty($data['date_of_birth']) || empty($data['email'])) {
            throw new \Exception('Tous les champs sont obligatoires');
        }

        // Validation de l'email
        if (!preg_match(self::EMAIL_PATTERN, $data['email'])) {
            throw new \Exception('Format d\'email invalide');
        }

        // Validation de la date
        if (!preg_match(self::DATE_PATTERN, $data['date_of_birth'])) {
            throw new \Exception('Format de date invalide (AAAA-MM-JJ requis)');
        }

        return true;
    }

    /*
     *
     *    MÉTHODES POUR L'INTERFACE CONSOLE (conservées pour compatibilité)
     *
     */

    // Créé un étudiant et effectue des vérifications (version console)
    function createStudent(): bool
    {
        $student = $this->askStudentInfos();
        if ($student === false) {
            return false;
        }

        $studentToSave = new Student(null, $student['firstname'], $student['lastname'], $student['dob'], $student['email']);
        try {
            $this->studentRepository->save($studentToSave);
            $this->insertLog(LogType::DEBUG, "Création", "Création d'un étudiant");
            return true;
        } catch (PDOException $e) {
            echo "Erreur lors de save : " . $e->getMessage();
            $this->insertLog(LogType::ERR, "Création", "Erreur lors de la création d'un étudiant");
            return false;
        }
    }

    // Permet d'éditer un étudiant (version console)
    function editStudent(): void
    {
        $id = $this->askStudentId();

        try{
            $student = $this->studentRepository->findById($id);
            $this->insertLog(LogType::DEBUG, "Rechercher par ID", "Étudiant d'ID ($id) trouvé");
        } catch (PDOException $e) {
            echo "Erreur lors de findById : " . $e->getMessage();
            $this->insertLog(LogType::ERR, "Rechercher par ID", "Étudiant d'ID ($id) introuvable");
            $student = false;
        }

        if(!$student)
            return;

        $this->askStudentUpdateInfo($student);

        try {
            $this->studentRepository->update($student);
            $this->insertLog(LogType::DEBUG, "Update", "Étudiant d'ID ($id) mis à jour");
        } catch (PDOException $e) {
            echo "Erreur lors de update : " . $e->getMessage();
            $this->insertLog(LogType::ERR, "Update", "Erreur pour l'étudiant d'ID ($id) lors de la mise à jour");
        }
    }

    function searchStudentsByIdentity(): void {
        $input = $this->askStudentName();

        $students = [];
        try{
            $students = $this->studentRepository->findAllByName($input);
            $this->insertLog(LogType::DEBUG, "Rechercher par nom", "Étudiant avec ($input) dans le nom trouvé");
        } catch (PDOException $e) {
            $this->insertLog(LogType::ERR, "Rechercher par nom", "Étudiant avec ($input) dans le nom introuvable");
            echo "Erreur lors de findAllByName : " . $e->getMessage();
        }

        $this->displayStudentFoundByName($input, $students);
    }

    /*
     *  MÉTHODES D'INTERFACE CONSOLE (À CONSERVER POUR COMPATIBILITÉ)
     */

    public function displayStudent(array $students): void
    {
        echo "=== Affichage des étudiants ===\n";
        if (empty($students))
            echo "Aucun étudiant";

        foreach ($students as $student) {
            echo $student . PHP_EOL;
        }
    }

    public function askStudentInfos()
    {
        echo "Saisir le prénom : ";
        $firstname = readline();

        if (empty($firstname)) {
            echo "Prénom incorrect";
            $this->insertLog(LogType::WARN, "Création", "Prénom incorrect");
            return false;
        }

        echo "Saisir le nom : ";
        $lastname = readline();

        if (empty($lastname)) {
            echo "Nom incorrect";
            $this->insertLog(LogType::WARN, "Création", "Nom incorrect");
            return false;
        }

        echo "Saisir date naissance (aaaa-mm-jj): ";
        $dob = readline();

        if (!preg_match(self::DATE_PATTERN, $dob)) {
            echo "Date incorrecte";
            $this->insertLog(LogType::WARN, "Création", "Date incorrecte");
            return false;
        }

        echo "Saisir email: ";
        $email = readline();

        if (!preg_match(self::EMAIL_PATTERN, $email)) {
            echo "Email incorrect";
            $this->insertLog(LogType::WARN, "Création", "Email incorrect");
            return false;
        }

        return ['firstname' => $firstname,
            'lastname' => $lastname,
            'dob' => $dob,
            'email' => $email];
    }

    public function askStudentId(): int
    {
        echo "Saisir l'id de l'étudiant: ";
        return (int)readline();
    }

    public function askStudentUpdateInfo(Student $student): void
    {
        readline();

        echo "Saisir prénom: ";
        $firstname = readline();

        if (!empty($firstname)) {
            $student->firstname = $firstname;
        }

        echo "Saisir nom: ";
        $lastname = readline();

        if (!empty($lastname)) {
            $student->lastname = $lastname;
        }

        echo "Saisir date naissance: ";
        $dob = readline();

        if (!empty($dob) && preg_match(self::DATE_PATTERN, $dob)) {
            $student->date_of_birth = $dob;
        }

        echo "Saisir email: ";
        $email = readline();

        if (!empty($email) && preg_match(self::EMAIL_PATTERN, $email)) {
            $student->email = $email;
        }
    }

    public function displayDeleteSuccess(bool $success, int $id): void
    {
        if ($success)
            echo "L'étudiant avec l'ID $id a été supprimé.\n";
        else
            echo "L'ID est incorrect.\n";
    }

    public function askStudentName(): string
    {
        echo "Saisir le nom ou prénom de l'étudiant: ";
        $input = '%' . readline() . '%';
        return $input;
    }

    public function displayStudentFoundByName(string $input, array $students): void
    {
        echo "=== Affichage de tous étudiants ayant $input dans leur nom ou prénom === \n";
        foreach ($students as $student) {
            echo $student . PHP_EOL;
        }
    }
}