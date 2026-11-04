<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\touite\Feed;
use iutnc\touiteur\tag\Tag;
use PDO;

/**
 * Repository centralisant les opérations d'accès à la base de données.
 */
class TouiteurReaderRepository extends TouiteurRepository {
    /**============================================
     *               GESTION DES TOUITES
     *=============================================**/

    private function rowToTouite(array $res): Touite {
        $touite = new Touite(
                $res['content'],
                $res['posted_at'],
                $res['image_path'] ?? null,
                $res['image_description'] ?? null,
                $res['id']
            );

        return $touite;
    }

    private function rowsToFeed(array $res): Feed {
        $touites = [];

        foreach ($res as $touite) {
            $touites[] = $this->rowToTouite($touite);
        }

        return new Feed($touites);
    }

    public function rowToTag(array $res): Tag {
        $tag = new Tag(
                $res['libelle'],
                $res['id']
            );

        return $tag;
    }

    /**
     * @return Tag[] Touts les tags de la base de données
     */
    public function rowsToTag(array $res): array {
        $tags = [];

        foreach ($res as $tag) {
            $tags[] = $this->rowToTag($tag);
        }

        return $tags;
    }

    public function findFeed(Pagination $pagination = new Pagination): ?Feed {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT *
            FROM touite
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset;
        SQL);

        $stmt->bindValue('limit', $pagination->getLimit(), PDO::PARAM_INT);
        $stmt->bindValue('offset', $pagination->getOffset(), PDO::PARAM_INT);
        $stmt->execute(); // vide
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowsToFeed($res);
    }

    public function findFollowedFeed(int $id, Pagination $pagination = new Pagination): ?Feed {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT touite.*
            FROM touite
            WHERE touite.users_id IN (
                SELECT followed_id
                FROM follow
                WHERE follower_id = :follower_id
            )
            OR touite.id IN (
                SELECT tag2touite.touite_id
                FROM tag2touite
                INNER JOIN users2tag ON users2tag.tag_id = tag2touite.tag_id
                WHERE users2tag.users_id = :tag_user_id
            )
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset;
        SQL);

        $stmt->bindValue('follower_id', $id, PDO::PARAM_INT);
        $stmt->bindValue('tag_user_id', $id, PDO::PARAM_INT);
        $stmt->bindValue('limit', $pagination->getLimit(), PDO::PARAM_INT);
        $stmt->bindValue('offset', $pagination->getOffset(), PDO::PARAM_INT);
        $stmt->execute();

        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowsToFeed($res);
    }

    public function findFeedByUserId(int $id, Pagination $pagination = new Pagination): ?Feed {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT *
            FROM touite
            WHERE users_id = :id
            LIMIT :limit OFFSET :offset;
        SQL);

        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->bindValue('limit', $pagination->getLimit(), PDO::PARAM_INT);
        $stmt->bindValue('offset', $pagination->getOffset(), PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowsToFeed($res);
    }

    public function findFeedByTagId(int $id, Pagination $pagination = new Pagination): ?Feed {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT touite.*
            FROM touite
            INNER JOIN tag2touite on touite.id = tag2touite.touite_id
            WHERE tag2touite.tag_id = :id
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset;
        SQL);

        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->bindValue('limit', $pagination->getLimit(), PDO::PARAM_INT);
        $stmt->bindValue('offset', $pagination->getOffset(), PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowsToFeed($res);
    }

    public function findTouiteById(int $id) : ?Touite {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT *
            FROM touite
            WHERE id = :id;
        SQL);

        $stmt->execute(['id' => $id]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowToTouite($res);
    }

    public function findTagById(int $id): ?Tag {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT *
            FROM tag
            WHERE id = :id;
        SQL);

        $stmt->execute(['id' => $id]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowToTag($res);
    }

    /**
     * @return Tag[] Touts les tags
     */
    public function findTagsByTouiteId(int $id): ?array {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT tag.*
            FROM tag
            INNER JOIN tag2touite on tag.id = tag2touite.tag_id
            WHERE touite_id = :id;
        SQL);

        $stmt->execute(['id' => $id]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowsToTag($res);
    }

    public function getUserByTouiteId(int $touite_id) : ?array {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT users_id, nom, prenom
            FROM touite
            INNER JOIN users on users.id = touite.users_id
            WHERE touite.id = :id
        SQL);

        $stmt->execute(['id' => $touite_id]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $res;
    }

    public function getUserById(int $user_id) : ?array {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT id, nom, prenom
            FROM users
            WHERE id = :id
        SQL);

        $stmt->execute(['id' => $user_id]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $res;
    }

    public function isSub(int $followed_id, int $follower_id) : bool {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT 1
            FROM follow
            WHERE follower_id = :follower_id
            AND followed_id = :followed_id
        SQL);

        $stmt->execute(['follower_id' => $follower_id, 'followed_id' => $followed_id]);

        return $stmt->fetch() !== false;
    }

    public function isTagSub(int $user_id, int $tag_id) : bool {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT 1
            FROM users2tag
            WHERE users_id = :user_id
            AND tag_id = :tag_id
        SQL);

        $stmt->execute(['user_id' => $user_id, 'tag_id' => $tag_id]);

        return $stmt->fetch() !== false;
    }

    /**
     * @return Tag[] Touts les tags
     */
    public function findAllTags(int $limit = 1844674407370955161): ?array {
        if ($limit < 0) $limit = 0;

        $stmt = $this->pdo->prepare(<<<SQL
            SELECT *
            FROM tag
            LIMIT :limit;
        SQL);

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $this->rowsToTag($res);
    }

    public function getAllFollowedTag(int $user_id) : ?array {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT libelle, tag.id
            FROM tag
            INNER JOIN users2tag on users2tag.tag_id = tag.id
            WHERE users2tag.users_id = :user_id
        SQL);

        $stmt->execute(['user_id' => $user_id]);

        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return $res;
    }

    /**============================================
     *               GESTION DE L'AUTH
     *=============================================**/

    /**
     * Recherche un utilisateur par son adresse email.
     *
     * @param string $email Adresse email recherchée.
     * @return array|null Données de l'utilisateur ou null si introuvable.
     */
    public function findUserByEmail(string $email): ?array {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT id, email, passwd, nom, prenom, counter_touite
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
    public function existsByEmail(string $email): bool {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT id
            FROM users
            WHERE email = :email
        SQL);

        $stmt->execute(['email' => $email]);

        return $stmt->fetch() !== false;
    }
}