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
            throw new ConfigIOException('Error reading configuration file');
        }
        // On ajoute le charset=utf8mb4 dans la ligne du dns pour que sur webetu les caractères codés sur plusieurs bits s'affichent correctement (sinon on avait des problèmes)
        self::$config = [
            'dsn' => "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']};charset=utf8mb4",
            'user' => $conf['username'],
            'pass' => $conf['password']
        ];
    }

    /**
     * Exécute une requête et retourne sa première ligne.
     */
    protected function fetchOne(string $sql, array $parameters = []): ?array {
        $stmt = $this->pdo->prepare($sql);
        $this->bindParameters($stmt, $parameters);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * Exécute une requête et retourne toutes ses lignes.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $parameters = []): array {
        $stmt = $this->pdo->prepare($sql);
        $this->bindParameters($stmt, $parameters);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->pdo->prepare($sql);
        $this->bindParameters($stmt, $parameters);
        $stmt->bindValue('limit', $pagination->getLimit(), PDO::PARAM_INT);
        $stmt->bindValue('offset', $pagination->getOffset(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function bindParameters(\PDOStatement $stmt, array $parameters): void {
        foreach ($parameters as $name => $value) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $stmt->bindValue($name, $value, $type);
        }
    }
}