<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\touite\Feed;
use iutnc\touiteur\tag\Tag;

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
                $res['note'],
                $res['media_path'] ?? null,
                $res['media_description'] ?? null,
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
                $res['description'],
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
        $res = $this->fetchPage(<<<SQL
            SELECT *
            FROM touite
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset;
        SQL, $pagination);

        return $this->rowsToFeed($res);
    }

    public function findFollowedFeed(int $id, Pagination $pagination = new Pagination): Feed {
        $res = $this->fetchPage(<<<SQL
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
        SQL, $pagination, ['follower_id' => $id, 'tag_user_id' => $id]);

        return $this->rowsToFeed($res);
    }

    public function findFeedSearched(string $search, Pagination $pagination = new Pagination): Feed {
        $res = $this->fetchPage(<<<SQL
            SELECT *
            FROM touite
            WHERE content LIKE :search
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset;
        SQL, $pagination, ['search' => '%' . $search . '%']);

        return $this->rowsToFeed($res);
    }

    public function findFollowedFeedSearched(int $id, string $search, Pagination $pagination = new Pagination): Feed {
        $res = $this->fetchPage(<<<SQL
            SELECT touite.*
            FROM touite
            WHERE (touite.users_id IN (
                SELECT followed_id
                FROM follow
                WHERE follower_id = :follower_id
            )
            OR touite.id IN (
                SELECT tag2touite.touite_id
                FROM tag2touite
                INNER JOIN users2tag ON users2tag.tag_id = tag2touite.tag_id
                WHERE users2tag.users_id = :tag_user_id
            ))
            AND content LIKE :search
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset;
        SQL, $pagination, ['follower_id' => $id, 'tag_user_id' => $id, 'search' => '%' . $search . '%']);

        return $this->rowsToFeed($res);
    }

    public function findFeedByUserId(int $id, Pagination $pagination = new Pagination): Feed {
        $res = $this->fetchPage(<<<SQL
            SELECT *
            FROM touite
            WHERE users_id = :id
            LIMIT :limit OFFSET :offset;
        SQL, $pagination, ['id' => $id]);

        return $this->rowsToFeed($res);
    }

    public function findFeedWithMediasByUserId(int $id, Pagination $pagination = new Pagination): Feed {
        $res = $this->fetchPage(<<<SQL
            SELECT *
            FROM touite
            WHERE users_id = :id
            AND media_path IS NOT NULL
            LIMIT :limit OFFSET :offset;
        SQL, $pagination, ['id' => $id]);

        return $this->rowsToFeed($res);
    }

    public function findFeedByTagId(int $id, Pagination $pagination = new Pagination): Feed {
        $res = $this->fetchPage(<<<SQL
            SELECT touite.*
            FROM touite
            INNER JOIN tag2touite on touite.id = tag2touite.touite_id
            WHERE tag2touite.tag_id = :id
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset;
        SQL, $pagination, ['id' => $id]);

        return $this->rowsToFeed($res);
    }

    public function findTouiteById(int $id) : ?Touite {
        $res = $this->fetchOne(<<<SQL
            SELECT *
            FROM touite
            WHERE id = :id;
        SQL, ['id' => $id]);

        if (!$res) return null;

        return $this->rowToTouite($res);
    }

    public function findTagById(int $id): ?Tag {
        $res = $this->fetchOne(<<<SQL
            SELECT *
            FROM tag
            WHERE id = :id;
        SQL, ['id' => $id]);

        if (!$res) return null;

        return $this->rowToTag($res);
    }

    /**
     * @return Tag[] Touts les tags
     */
    public function findTagsByTouiteId(int $id): ?array {
        $res = $this->fetchAll(<<<SQL
            SELECT tag.*
            FROM tag
            INNER JOIN tag2touite on tag.id = tag2touite.tag_id
            WHERE touite_id = :id;
        SQL, ['id' => $id]);

        if (!$res) return null;

        return $this->rowsToTag($res);
    }

    public function getUserByTouiteId(int $touite_id) : ?array {
        return $this->fetchOne(<<<SQL
            SELECT users_id as id, nom, prenom
            FROM touite
            INNER JOIN users on users.id = touite.users_id
            WHERE touite.id = :id
        SQL, ['id' => $touite_id]);
    }

    public function getUserById(int $user_id) : ?array {
        return $this->fetchOne(<<<SQL
            SELECT id, nom, prenom, counter_touite
            FROM users
            WHERE id = :id
        SQL, ['id' => $user_id]);
    }

    public function isSub(int $followed_id, int $follower_id) : bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM follow
            WHERE follower_id = :follower_id
            AND followed_id = :followed_id
        SQL, ['follower_id' => $follower_id, 'followed_id' => $followed_id]);
    }

    public function isTagSub(int $user_id, int $tag_id) : bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM users2tag
            WHERE users_id = :user_id
            AND tag_id = :tag_id
        SQL, ['user_id' => $user_id, 'tag_id' => $tag_id]);
    }

    /**
     * @return Tag[] Touts les tags
     */
    public function findAllTags(int $limit = 1844674407370955161): ?array {
        if ($limit < 0) $limit = 0;

        $res = $this->fetchAll(<<<SQL
            SELECT tag.*
            FROM tag
            INNER JOIN users2tag ON users2tag.tag_id = tag.id
            GROUP BY tag_id
            ORDER BY COUNT(users_id) DESC, tag.id ASC
            LIMIT :limit;
        SQL, ['limit' => $limit]);

        if (!$res) return null;

        return $this->rowsToTag($res);
    }

    public function getAllFollowedTag(int $user_id) : ?array {
        $res = $this->fetchAll(<<<SQL
            SELECT libelle, tag.id, description
            FROM tag
            INNER JOIN users2tag on users2tag.tag_id = tag.id
            WHERE users2tag.users_id = :user_id
        SQL, ['user_id' => $user_id]);

        if (!$res) return null;

        return $res;
    }

    public function findTagByLibelle(string $libelle) : ?Tag {
        $res = $this->fetchOne(<<<SQL
                SELECT *
                FROM tag
                WHERE libelle = :libelle
        SQL, ['libelle' => $libelle]);

        if (!$res) return null;

        return $this->rowToTag($res);
    }

    public function getFollowers(int $user_id) : ?array {
        $res = $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom
            FROM users
            INNER JOIN follow on follow.follower_id = users.id
            WHERE followed_id = :user_id
        SQL, ['user_id' => $user_id]);

        if (!$res) return null;

        return $res;
    }

    public function getFollowed(int $user_id) : ?array {
        $res = $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom
            FROM users
            INNER JOIN follow on follow.followed_id = users.id
            WHERE follower_id = :user_id
        SQL, ['user_id' => $user_id]);

        if (!$res) return null;

        return $res;
    }

    public function getMoyenneNoteTouite(int $user_id) : float {
        $res = $this->fetchAll(<<<SQL
            SELECT ROUND(AVG(note),2) AS moyenne
            FROM note2touite
            INNER JOIN touite on touite.id = note2touite.touite_id
            WHERE touite.users_id = :user_id
        SQL, ['user_id' => $user_id]);

        if (!$res) {
            return 0.0;
        }

        return (float) $res[0]['moyenne'];
    }

    public function isLikedBy(int $user_id, int $touite_id) : bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM note2touite
            WHERE users_id = :user_id
            AND touite_id = :touite_id
            AND state = 1
        SQL, ['user_id' => $user_id, 'touite_id' => $touite_id]);
    }

    public function isDislikedBy(int $user_id, int $touite_id) : bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM note2touite
            WHERE users_id = :user_id
            AND touite_id = :touite_id
            AND state = -1
        SQL, ['user_id' => $user_id, 'touite_id' => $touite_id]);
    }

    public function getTrendUser() : ?array {
        $res = $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom, COUNT(follow.follower_id) AS nbFol, counter_touite
            FROM users
            INNER JOIN follow on users.id = follow.followed_id  
            GROUP BY users.id
            ORDER BY nbFol DESC
        SQL);

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
        return $this->fetchOne(<<<SQL
            SELECT id, email, passwd, nom, prenom, counter_touite
            FROM users
            WHERE email = :email
        SQL, ['email' => $email]);
    }

    /**
     * Vérifie si un compte utilisateur existe déjà avec l'adresse email spécifiée.
     *
     * @param string $email Adresse email à vérifier.
     * @return bool True si l'email existe déjà, false sinon.
     */
    public function existsByEmail(string $email): bool {
        return $this->exists(<<<SQL
            SELECT id
            FROM users
            WHERE email = :email
        SQL, ['email' => $email]);
    }

    public function isAdminUser(array $user): bool {
        return $this->exists(<<<SQL
            SELECT id
            from users
            WHERE id = :id
            AND role = 100
        SQL, ['id' => $user['id']]);
    }
}
