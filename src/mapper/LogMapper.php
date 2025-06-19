<?php

namespace src\mapper;

use MongoDB\BSON\ObjectId;
use src\enum\LogType;
use src\model\Log;

class LogMapper
{
    public static function documentToLog($document): Log
    {
        $type = $document['type'] ?? null;
        $type = $type ? LogType::tryFrom($document['type']) : null;

        return new Log(
            $document['_id'] ?? null,
            $type,
            $document['operation'] ?? null,
            $document['message'] ?? null
        );
    }

    public static function documentsToLogs($documents): array{
        $allLogs = [];
        foreach($documents as $document){
            $allLogs[] = self::documentToLog($document);
        }
        return $allLogs;
    }

    public static function logToDocument(Log $log) : array{
        if($log->getId()){
            $document['_id'] = new ObjectId($log->getId());
        }

        return $document = [
            'type' => $log->getType(),
            'operation' => $log->getOperation(),
            'message' => $log->getMessage()
        ];
    }
}