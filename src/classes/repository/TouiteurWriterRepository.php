<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\touite\Touite;

/**
 * Repository centralisant les opérations d'accès à la base de données.
 */
class TouiteurWriterRepository extends TouiteurRepository {
    /**============================================
     *               GESTION DES TOUITES
     *=============================================**/

    

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
    public function addUser(string $email, string $password, string $nom, string $prenom): void {
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

    public function addTouite(string $content, string $posted_at, ?string $image_path, ?string $image_description, $user_id): Touite {
        $content = filter_var($content, FILTER_SANITIZE_SPECIAL_CHARS);
        $posted_at = filter_var($posted_at, FILTER_SANITIZE_SPECIAL_CHARS);
        $image_path = filter_var($image_path, FILTER_SANITIZE_SPECIAL_CHARS);
        $image_description = filter_var($image_description, FILTER_SANITIZE_SPECIAL_CHARS);
        $user_id = filter_var($user_id, FILTER_SANITIZE_NUMBER_INT);

        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO touite (content, note, image_path,image_description,  posted_at, users_id) VALUES (:content, :note, :img_path, :img_desc, :date, :user_id)
        SQL);

        $stmt->execute(['content' => $content, 'note' => 0, 'img_path' => $image_path,'img_desc' => $image_description, 'date' => $posted_at, 'user_id' => $user_id]);

        $touite = new Touite($content, $posted_at, $image_path, $image_description, $user_id);
        $touite->setId($this->pdo->lastInsertId());

        return $touite;
    }
}