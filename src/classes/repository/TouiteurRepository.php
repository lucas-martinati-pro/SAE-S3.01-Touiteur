<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\exception\ConfigIOException;
use iutnc\touiteur\pagination\Pagination;
use PDO;

class TouiteurRepository {
    protected PDO $pdo;
    protected static array $config = [];

    public function __construct() {
        $this->pdo = new PDO(self::$config['dsn'], self::$config['user'], self::$config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public static function setConfig(string $file): void {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new ConfigIOException("Error reading configuration file");
        }
        self::$config = [
            'dsn' => "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}",
            'user' => $conf['username'],
            'pass' => $conf['password']
        ];
    }

    /**
     * Exécute une requête et retourne sa première ligne.
     */
    protected function fetchOne(string $sql, array $parameters = []): ?array {
        $statement = $this->pdo->prepare($sql);
        $this->bindParameters($statement, $parameters);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * Exécute une requête et retourne toutes ses lignes.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $parameters = []): array {
        $statement = $this->pdo->prepare($sql);
        $this->bindParameters($statement, $parameters);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function exists(string $sql, array $parameters = []): bool {
        return $this->fetchOne($sql, $parameters) !== null;
    }

    /**
     * Exécute une requête paginée en liant explicitement les valeurs numériques.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchPage(string $sql, Pagination $pagination, array $parameters = []): array {
        $statement = $this->pdo->prepare($sql);
        $this->bindParameters($statement, $parameters);
        $statement->bindValue('limit', $pagination->getLimit(), PDO::PARAM_INT);
        $statement->bindValue('offset', $pagination->getOffset(), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function bindParameters(\PDOStatement $statement, array $parameters): void {
        foreach ($parameters as $name => $value) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $statement->bindValue($name, $value, $type);
        }
    }
}