<?php 

namespace src\mapper;

use src\model\Student;

class StudentMapper {
    public static function entityToStudent($entity): Student
    {
        return new Student($entity["id"], $entity["firstname"], $entity["lastname"], $entity["date_of_birth"], $entity["email"]); 
    }

    public static function entitiesToStudents(array $entities): array
    {
        $students = [];
        if(empty($entities))
            return $students;

        foreach($entities as $student){
            $students[] = StudentMapper::entityToStudent($student); 
        }

        return $students;
    }
}