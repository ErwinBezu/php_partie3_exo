<?php

namespace src\repository;

use MongoDB\Collection;
use src\configs\MongoConnection;
use src\interface\LogRepoInterface;
use src\mapper\LogMapper;
use src\model\Log;

class LogRepository implements LogRepoInterface
{
    private Collection $collection;

    public function __construct(){
        $this->collection = MongoConnection::getMongoCollection("Logs", "logs");
    }

    public function insert(Log $log){
        $document = LogMapper::logToDocument($log);
        $result = $this->collection->insertOne($document);
        return $result->getInsertedId()?->__toString();
    }

    public function findAll(int $limit = 0): array{
        $cursor = $this->collection->find([], $limit > 0 ? ['limit' => $limit] : []);
        return LogMapper::documentsToLogs($cursor);
    }

    public function clear(): int{
        $deleteResult = $this->collection->deleteMany([]);
        return $deleteResult->getDeletedCount();
    }
}