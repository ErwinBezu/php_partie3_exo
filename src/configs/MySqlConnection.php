<?php

namespace src\configs;

use PDO;
use PDOException;

class MySqlConnection
{
    private static array $config = [];
    private static ?PDO $connection = null;

    private function __construct(){
        self::loadConfig();

        try {
            $db = new PDO(
                "mysql:host=". self::$config['host']. ";dbname=". self::$config['db_name'],
                self::$config['username'],
                self::$config['password']
            );

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e){
            echo "Erreur de connexion : " . $e->getMessage(), PHP_EOL;
            return;
        }
        self::$connection = $db;
        self::initTable();
    }

    private static function loadConfig(): void
    {
        $configPath = __DIR__ . '/../../../config.ini';

        if (!file_exists($configPath)) {
            throw new \Exception("Le fichier de configuration config.ini est introuvable : " . $configPath);
        }

        $config = parse_ini_file($configPath, true);

        if ($config === false || !isset($config['database'])) {
            throw new \Exception("Impossible de lire la configuration de la base de données");
        }

        self::$config = $config['database'];
    }

    public static function getConnection(): PDO
    {
        if(self::$connection === null){
            new MySqlConnection();
        }

        return self::$connection;
    }

    private static function initTable(): void{
        $request = "CREATE TABLE IF NOT EXISTS student (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            firstname VARCHAR(50) NOT NULL, 
            lastname VARCHAR(50) NOT NULL, 
            date_of_birth DATE,
            email VARCHAR(50)
        )";

        self::$connection->exec($request);
    }
}