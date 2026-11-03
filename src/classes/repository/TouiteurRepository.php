<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\touite\Touite;

class TouiteurRepository {
    protected \PDO $pdo;
    protected static array $config = [];

    public function __construct() {
        $this->pdo = new \PDO(self::$config['dsn'], self::$config['user'], self::$config['pass'],
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    public static function setConfig(string $file): void {
        $conf = parse_ini_file($file);
        if ($conf === false) throw new \Exception("Error reading configuration file");
        self::$config = [
            'dsn' => "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}",
            'user' => $conf['username'],
            'pass' => $conf['password']
        ];
    }
}