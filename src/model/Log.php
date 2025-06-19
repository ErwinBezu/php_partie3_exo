<?php

namespace src\model;

use src\enum\LogType;

class Log
{
    public function __construct(
        private ?string $id,
        private LogType $type,
        private string $operation,
        private string $message,
    ){}

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type->value;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString(){
        return "Log n°{$this->getId()} : [{$this->getType()}] {$this->getOperation()} : {$this->getMessage()}.";
    }

}