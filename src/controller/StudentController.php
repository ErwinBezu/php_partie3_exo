<?php

namespace src\controller;

use src\service\StudentService;
use src\model\Student;

class StudentController
{
    public function __construct(private StudentService $service) {}

    public function displayStudents()
    {
        $message = '';
        $messageType = 'success';
        $editStudent = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];

            try {
                switch ($action) {
                    case 'add':
                        $this->handleAdd();
                        $message = 'Étudiant ajouté avec succès !';
                        break;

                    case 'update':
                        $this->handleUpdate();
                        $message = 'Étudiant modifié avec succès !';
                        break;

                    case 'delete':
                        $this->handleDelete();
                        $message = 'Étudiant supprimé avec succès !';
                        break;
                }
            } catch (\Exception $e) {
                $message = 'Erreur : ' . $e->getMessage();
                $messageType = 'error';
            }
        }

        try {
            $students = $this->service->getStudents();
        } catch (\Exception $e) {
            $students = [];
            $message = 'Erreur lors du chargement des étudiants : ' . $e->getMessage();
            $messageType = 'error';
        }

        include __DIR__ . "/../view/studentView.php";
    }

    private function handleAdd()
    {
        $this->validateStudentData($_POST);

        $student = new Student(
            null,
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['date_of_birth'],
            $_POST['email']
        );

        $success = $this->service->addStudent($student);
        if (!$success) {
            throw new \Exception('Échec de l\'ajout de l\'étudiant');
        }
    }

    private function handleUpdate()
    {
        $this->validateStudentData($_POST, true);

        $student = new Student(
            (int)$_POST['id'],
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['date_of_birth'],
            $_POST['email']
        );

        $success = $this->service->updateStudent($student);
        if (!$success) {
            throw new \Exception('Échec de la modification de l\'étudiant');
        }
    }

    private function handleDelete()
    {
        if (empty($_POST['delete_id']) || !is_numeric($_POST['delete_id'])) {
            throw new \Exception('ID invalide pour la suppression');
        }

        $id = (int)$_POST['delete_id'];
        $success = $this->service->deleteStudent($id);
        if (!$success) {
            throw new \Exception('Échec de la suppression de l\'étudiant');
        }
    }

    private function validateStudentData(array $data, bool $requireId = false)
    {
        if ($requireId && (empty($data['id']) || !is_numeric($data['id']))) {
            throw new \Exception('ID requis pour la modification');
        }

        if (empty($data['firstname']) || empty($data['lastname']) ||
            empty($data['date_of_birth']) || empty($data['email'])) {
            throw new \Exception('Tous les champs sont obligatoires');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Format d\'email invalide');
        }

        if (!$this->isValidDate($data['date_of_birth'])) {
            throw new \Exception('Format de date invalide (AAAA-MM-JJ requis)');
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    public function getStudentById(int $id): ?Student
    {
        return $this->service->getStudentById($id);
    }

    public function displayByName(string $name)
    {
        try {
            $students = $this->service->getStudentsByName($name);
        } catch (\Exception $e) {
            $students = [];
            $message = 'Erreur lors de la recherche : ' . $e->getMessage();
            $messageType = 'error';
        }

        include __DIR__ . "/../view/studentView.php";
    }
}