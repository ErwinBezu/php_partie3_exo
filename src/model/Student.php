<?php

namespace src\model;

class Student{
    public function __construct(
        public ?int $id, 
        public string $firstname, 
        public string $lastname, 
        public string $date_of_birth, 
        public string $email 
    ){}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getDateOfBirth(): string
    {
        return $this->date_of_birth;
    }

    public function setDateOfBirth(string $date_of_birth): void
    {
        $this->date_of_birth = $date_of_birth;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function __toString(){
        return "Student n°$this->id : $this->firstname $this->lastname, née le $this->date_of_birth, email : $this->email.";
    }
}

