<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\exception\ConfigIOException;
use PDO;

/**
 * Repository centralisant les opérations d'accès à la base de données.
 */
class TouiteurRepository {
    private PDO $pdo;

    /**
     * Instance unique du repository (patron Singleton).
     */
    private static ?TouiteurRepository $instance = null;

    /**
     * Configuration de connexion à la base de données extraite du fichier .ini.
     */
    private static array $config = [];

    /**
     * Constructeur privé empêchant l'instanciation directe.
     */
    private function __construct() {
        $this->pdo = new PDO(self::$config['dsn'], self::$config['user'], self::$config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    /**
     * Définit la configuration de connexion à la base de données à partir d'un fichier .ini.
     *
     * @param string $file Chemin absolu vers le fichier de configuration INI.
     * @return void
     * @throws ConfigIOException Si le fichier INI ne peut pas être lu ou analysé.
     */
    public static function setConfig(string $file) : void {
        $conf = parse_ini_file($file);
        if ($conf === false) throw new ConfigIOException("Error reading configuration file");
        self::$config = [
            'dsn' => "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}",
            'user' => $conf['username'],
            'pass' => $conf['password']
        ];
    }

    /**
     * Retourne l'instance unique du repository.
     *
     * @return TouiteurRepository Instance du repository.
     */
    public static function getInstance() : TouiteurRepository {
        if (is_null(self::$instance)) self::$instance = new TouiteurRepository();
        return self::$instance;
    }

    /**========================================================================
     *                           READER
     *========================================================================**/

    

    /**============================================
     *               GESTION DE L'AUTH
     *=============================================**/

    /**
     * Recherche un utilisateur par son adresse email.
     *
     * @param string $email Adresse email recherchée.
     * @return array|null Données de l'utilisateur ou null si introuvable.
     */
    public function findUserByEmail(string $email) : ?array {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT id, email, passwd
            FROM users
            WHERE email = :email
        SQL);

        $stmt->execute(['email' => $email]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $res;
    }

    /**
     * Vérifie si un compte utilisateur existe déjà avec l'adresse email spécifiée.
     *
     * @param string $email Adresse email à vérifier.
     * @return bool True si l'email existe déjà, false sinon.
     */
    public function existsByEmail(string $email) : bool {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT id
            FROM users
            WHERE email = :email
        SQL);

        $stmt->execute(['email' => $email]);

        return $stmt->fetch() !== false;
    }

    /**========================================================================
     *                           WRITER
     *========================================================================**/

    

    /**============================================
     *               GESTION DE L'AUTH
     *=============================================**/

    /**
     * Crée un nouvel utilisateur en base de données avec un mot de passe haché (bcrypt).
     *
     * @param string $email Adresse email nettoyée de l'utilisateur.
     * @param string $password Mot de passe en clair à hacher.
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @return void
     */
    public function addUser(string $email, string $password, string $nom, string $prenom) : void {
        // Double par précaution;
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $nom = filter_var($nom, FILTER_SANITIZE_SPECIAL_CHARS);
        $prenom = filter_var($prenom, FILTER_SANITIZE_SPECIAL_CHARS);


        $password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO users (email, passwd, nom, prenom, counter_touite) VALUES (:email, :password, :nom, :prenom, 0)
        SQL);

        $stmt->execute(['email' => $email, 'password' => $password, 'nom' => $nom, 'prenom' => $prenom]);
    }
}