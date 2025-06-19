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

    function updateStudent(Student $student): bool
    {
        try {
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

    function deleteStudent(int $id): bool
    {
        try {
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
}