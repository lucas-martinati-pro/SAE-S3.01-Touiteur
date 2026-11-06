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
     * GESTION DES USERS
     *=============================================**/

    public function followUser(int $followerId, int $followedId): void {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO follow (follower_id, followed_id) VALUES (:follower_id, :followed_id);
        SQL);

        $stmt->execute(['follower_id' => $followerId, 'followed_id' => $followedId]);
    }

    public function unfollowUser(int $followerId, int $followedId): void {
        $stmt = $this->pdo->prepare(<<<SQL
            DELETE FROM follow
            WHERE follower_id = :follower_id
            AND followed_id = :followed_id;
        SQL);

        $stmt->execute(['follower_id' => $followerId, 'followed_id' => $followedId]);
    }

    /**============================================
     * GESTION DES TAGS
     *=============================================**/

    public function followTag(int $userId, int $tagId): void {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO users2tag (users_id, tag_id) VALUES (:user_id, :tag_id);
        SQL);

        $stmt->execute(['user_id' => $userId, 'tag_id' => $tagId]);
    }

    public function unfollowTag(int $userId, int $tagId): void {
        $stmt = $this->pdo->prepare(<<<SQL
            DELETE FROM users2tag
            WHERE users_id = :user_id
            AND tag_id = :tag_id;
        SQL);

        $stmt->execute(['user_id' => $userId, 'tag_id' => $tagId]);
    }

    /**============================================
     * GESTION DES TOUITES
     *=============================================**/

    public function noteTouite(int $userId, int $touiteId, TypeNote $state) {
        if ($state === TypeNote::annuler) {
            $stmt = $this->pdo->prepare(<<<SQL
                DELETE FROM note2touite
                WHERE users_id = :user_id
                AND touite_id = :touite_id
            SQL);

            $stmt->execute(['user_id' => $userId, 'touite_id' => $touiteId]);
            return;
        }

        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO note2touite (users_id, touite_id, state)
            VALUES (:user_id, :touite_id, :state)
            ON DUPLICATE KEY UPDATE state = VALUES(state)
        SQL);

        $stmt->execute(['user_id' => $userId, 'touite_id' => $touiteId, 'state' => $state->getNote()]);
    }

    public function addTouite(Touite $touite, int $userId): Touite {
        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO touite (content, note, media_path,media_description, posted_at, users_id) VALUES (:content, :note, :media_path, :media_desc, :posted_at, :user_id)
        SQL);

        $stmt->execute(['content' => $touite->getContent(), 'note' => 0, 'media_path' => $touite->getMediaPath(), 'media_desc' => $touite->getMediaDescription(), 'posted_at' => $touite->getPostedAt(), 'user_id' => $userId]);

        $touite->setId($this->pdo->lastInsertId());

        return $touite;
    }

    public function addTouiteTag(Tag $tag, int $touiteId): void {
        $tagId = $tag->getId();
        if ($tagId === null) {
            $stmt = $this->pdo->prepare(<<<SQL
                INSERT INTO tag (libelle, description) VALUES (:nom, :desc);
            SQL);

            $stmt->execute(['nom' => $tag->getLibelle(), 'desc' => $tag->getDescription()]);
            $tagId = (int) $this->pdo->lastInsertId();
        }

        $stmt = $this->pdo->prepare(<<<SQL
            INSERT INTO tag2touite (touite_id, tag_id) VALUES (:touite_id, :tag_id);
        SQL);

        $stmt->execute(['touite_id' => $touiteId, 'tag_id' => $tagId]);
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
}
