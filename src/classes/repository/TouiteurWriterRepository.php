<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\enum\TypeNote;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\tag\Tag;

/**
 * Repository centralisant les opérations d'accès à la base de données.
 */
class TouiteurWriterRepository extends TouiteurRepository {
    /**============================================
     *               GESTION DES TOUITES
     *=============================================**/

    public function follow(int $follower_id, int $followed_id) : void {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO follow (follower_id, followed_id) VALUES (:follower_id, :followed_id);
        SQL);

        $stmt->execute(['follower_id' => $follower_id, 'followed_id' => $followed_id]);
    }

    public function unfollow(int $follower_id, int $followed_id) : void {
        $stmt = $this->pdo->prepare(<<<SQL
            DELETE FROM follow
            WHERE follower_id = :follower_id
            AND followed_id = :followed_id;
        SQL);

        $stmt->execute(['follower_id' => $follower_id, 'followed_id' => $followed_id]);
    }

    public function followTag(int $user_id, int $tag_id) : void {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO users2tag (users_id, tag_id) VALUES (:user_id, :tag_id);
        SQL);

        $stmt->execute(['user_id' => $user_id, 'tag_id' => $tag_id]);
    }

    public function unfollowTag(int $user_id, int $tag_id) : void {
        $stmt = $this->pdo->prepare(<<<SQL
            DELETE FROM users2tag
            WHERE users_id = :user_id
            AND tag_id = :tag_id;
        SQL);

        $stmt->execute(['user_id' => $user_id, 'tag_id' => $tag_id]);
    }

    public function noteTouite(int $user_id, int $touite_id, TypeNote $state) {
        if ($state === TypeNote::annuler) {
            $stmt = $this->pdo->prepare(<<<SQL
                DELETE FROM note2touite
                WHERE users_id = :user_id
                AND touite_id = :touite_id
            SQL);

            $stmt->execute(['user_id' => $user_id, 'touite_id' => $touite_id]);
            return;
        }

        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO note2touite (users_id, touite_id, state)
            VALUES (:user_id, :touite_id, :state)
            ON DUPLICATE KEY UPDATE state = VALUES(state)
        SQL);

        $stmt->execute(['user_id' => $user_id, 'touite_id' => $touite_id, 'state' => $state->getNote()]);
    }

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

    public function addTouite(Touite $touite, int $user_id): Touite {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO touite (content, note, media_path,media_description, posted_at, users_id) VALUES (:content, :note, :media_path, :media_desc, :posted_at, :user_id)
        SQL);

        $stmt->execute(['content' => $touite->getContent(), 'note' => 0, 'media_path' => $touite->getmedia_path(), 'media_desc' => $touite->getmedia_description(), 'posted_at' => $touite->getPosted_at(), 'user_id' => $user_id]);

        $touite->setId($this->pdo->lastInsertId());

        return $touite;
    }

    public function addTag($touiteId, Tag $tag): void {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO tag (libelle, description) VALUES (:nom, :desc);
        SQL);

        $stmt->execute(['nom' => $tag->getLibelle(), 'desc' => $tag->getDescription()]);

        $tagId = $this->pdo->lastInsertId();

        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO tag2touite (touite_id, tag_id) VALUES (:touite_id, :tag_id);
        SQL);

        $stmt->execute(['touite_id' => $touiteId, 'tag_id' => $tagId]);
    }
}
