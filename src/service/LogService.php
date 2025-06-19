<?php

namespace src\service;

use src\enum\LogType;
use src\mapper\LogMapper;
use src\repository\LogRepository;
use MongoDB\Driver\Exception\Exception as MongoDBException;

class LogService
{
    public function __construct(private LogRepository $logRepository){}

    public function getTenLastLogs(): array
    {
        $allLogs = [];
        try {
            $allLogs = $this->logRepository->findAll();
        }catch (MongoDBException $exception){
            echo "Erreur findAll logs : ".$exception->getMessage();
        }
        $this->displayLogs($allLogs);
        return $allLogs;
    }

    public function clearLogs(): void{
        try{
            $result = $this->logRepository->clear();
            echo "$result éléments supprimés.";
        }catch (MongoDBException $exception){
            echo "Erreur clearLogs : ".$exception->getMessage();
        }
    }

    public function displayLogs($logs): void
    {
        foreach ($logs as $log){
            print($log.PHP_EOL);
        }
    }
}