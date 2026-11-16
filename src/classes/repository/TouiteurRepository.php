<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\exception\ConfigIOException;
use iutnc\touiteur\pagination\Pagination;
use PDO;

/**
 * Classe TouiteurRepository de l’application.
 */
class TouiteurRepository {
    protected PDO $pdo;
    protected static array $config = [];

    /**
     * Initialise les données nécessaires à cet objet.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function __construct() {
        $this->pdo = new PDO(self::$config['dsn'], self::$config['user'], self::$config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * Charge la configuration de connexion à la base de données.
     * @param string $file chemin du fichier de configuration.
     * @return void Aucune valeur.
     * @throws ConfigIOException Si le traitement échoue.
     */
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
     * @param string $sql requête SQL.
     * @param array<string, int|float|string|bool|null> $parameters paramètres de la requête.
     * @return array<string, mixed>|null Ligne trouvée, ou null si aucun résultat.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    protected function fetchOne(string $sql, array $parameters = []): ?array {
        $stmt = $this->pdo->prepare($sql);
        $this->bindParameters($stmt, $parameters);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * Exécute une requête et retourne les lignes trouvées.
     * @param string $sql requête SQL.
     * @param array<string, int|float|string|bool|null> $parameters paramètres de la requête.
     * @return list<array<string, mixed>> Lignes trouvées.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    protected function fetchAll(string $sql, array $parameters = []): array {
        $stmt = $this->pdo->prepare($sql);
        $this->bindParameters($stmt, $parameters);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Exécute une requête et retourne les lignes trouvées.
     * @param string $sql requête SQL.
     * @param Pagination $pagination paramètres de pagination.
     * @param array<string, int|float|string|bool|null> $parameters paramètres de la requête.
     * @return list<array<string, mixed>> Lignes trouvées.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    protected function fetchPage(string $sql, Pagination $pagination, array $parameters = []): array {
        $stmt = $this->pdo->prepare($sql);
        $this->bindParameters($stmt, $parameters);
        $stmt->bindValue('limit', $pagination->getLimit(), PDO::PARAM_INT);
        $stmt->bindValue('offset', $pagination->getOffset(), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Exécute une requête qui modifie les données.
     * @param string $sql requête SQL.
     * @param array<string, int|float|string|bool|null> $parameters paramètres de la requête.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    protected function updateRequest(string $sql, array $parameters = []): void {
        $stmt = $this->pdo->prepare($sql);
        $this->bindParameters($stmt, $parameters);
        $stmt->execute();
    }

    /**
     * Indique si les données recherchées existent.
     * @param string $sql requête SQL.
     * @param array<string, int|float|string|bool|null> $parameters paramètres de la requête.
     * @return bool Résultat de la vérification.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    protected function exists(string $sql, array $parameters = []): bool {
        return $this->fetchOne($sql, $parameters) !== null;
    }

    /**
     * Associe les paramètres à la requête préparée.
     * @param \PDOStatement $stmt requête préparée.
     * @param array<string, int|float|string|bool|null> $parameters paramètres de la requête.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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