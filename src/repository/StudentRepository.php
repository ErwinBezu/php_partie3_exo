<?php

namespace src\repository;

use PDO;
use src\configs\MySqlConnection;
use src\mapper\StudentMapper;
use src\model\Student;

class StudentRepository {

    private PDO $db;

    public function __construct() {
        $this->db = MySqlConnection::getConnection();
    }

    public function findAll() {
        // Requête pour récupérer tous les étudiants trié par nom puis prénom
        $request = "SELECT id, firstname, lastname, date_of_birth, email FROM student ORDER BY lastname, firstname";
        $statement = $this->db->prepare($request);

        $statement->execute();

        // On récupère tous les enregistrements sous forme de tableaux associatifs
        $students = $statement->fetchAll(PDO::FETCH_ASSOC);

        return StudentMapper::entitiesToStudents($students);
    }

    // Récupère un étudiant par son id
    function findById(int $id): Student|false {
        $request = "SELECT id, firstname, lastname, date_of_birth, email FROM student WHERE id=:id;";
        $statement = $this->db->prepare($request);
        $statement->execute(["id" => $id]);
        $student = StudentMapper::entityToStudent($statement->fetch(PDO::FETCH_ASSOC));
        return $student;
    }

    public function save(Student $student) {
        $request = "INSERT INTO student (firstname, lastname, date_of_birth, email)
                    VALUES (:firstname, :lastname, :date_of_birth, :email)";

        $statement = $this->db->prepare($request);

        // Le charactère ":" est facultatif lorsque l'on bind les paramètres
        $statement->bindValue(":firstname", $student->firstname);
        $statement->bindValue(":lastname", $student->lastname);
        $statement->bindValue(":date_of_birth", $student->date_of_birth);
        $statement->bindValue(":email", $student->email);

        $statement->execute();

        // Retourne le nombre de lignes affectés par la requête
        return $statement->rowCount() === 1;
    }

    public function update(Student $studentToUpdate) {
        $request = "UPDATE student SET firstname=:firstname, lastname=:lastname, date_of_birth=:date_of_birth, email=:email WHERE id=:id;";
        $statement = $this->db->prepare($request);

        $statement->bindValue(":firstname", $studentToUpdate->firstname);
        $statement->bindValue(":lastname", $studentToUpdate->lastname);
        $statement->bindValue(":date_of_birth", $studentToUpdate->date_of_birth);
        $statement->bindValue(":email", $studentToUpdate->email);
        $statement->bindValue(":id", $studentToUpdate->id);

        return $statement->execute();
    }

    public function deleteById($id) {
        $query = "DELETE FROM student WHERE id=:id;";
        $statement = $this->db->prepare($query);
        return $statement->execute(["id" => $id]);
    }

    public function findAllByName($input) {
        $request = "SELECT id, firstname, lastname, date_of_birth, email FROM student WHERE firstname LIKE :input OR lastname LIKE :input";
        $statement = $this->db->prepare($request);
        $statement->execute([":input" => $input]);
        $students = $statement->fetchAll(PDO::FETCH_ASSOC);
        return StudentMapper::entitiesToStudents($students);
    }
}