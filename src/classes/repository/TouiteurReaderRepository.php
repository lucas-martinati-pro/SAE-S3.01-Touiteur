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
    static $limitTrendTag = 200;

    /**============================================
     * CREATIONS DES TOUITES/TAGS
     *=============================================**/

    private function rowToTouite(array $result): Touite {
        $touite = new Touite(
                $result['content'],
                $result['posted_at'],
                $result['note'],
                $result['media_path'] ?? null,
                $result['media_description'] ?? null,
                $result['id']
            );

        return $touite;
    }

    private function rowsToFeed(array $result): Feed {
        $touites = [];

        foreach ($result as $touite) {
            $touites[] = $this->rowToTouite($touite);
        }

        return new Feed($touites);
    }

    public function rowToTag(array $result): Tag {
        $tag = new Tag(
                $result['libelle'],
                $result['description'],
                $result['id']
            );

        return $tag;
    }

    /**
     * @return Tag[] Touts les tags de la base de données
     */
    public function rowsToTags(array $result): array {
        $tags = [];

        foreach ($result as $tag) {
            $tags[] = $this->rowToTag($tag);
        }

        return $tags;
    }

    /**============================================
     * CREATIONS DES FEEDS
     *=============================================**/

    public function findFeed(string $search = '', Pagination $pagination = new Pagination()): ?Feed {
        $result = $this->fetchPage(<<<SQL
            SELECT *
            FROM touite
            WHERE content LIKE :search
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset
        SQL, $pagination, ['search' => '%' . $search . '%']);

        return $this->rowsToFeed($result);
    }

    public function findFollowedFeedByUserId(int $userId, string $search = '', Pagination $pagination = new Pagination()): Feed {
        $result = $this->fetchPage(<<<SQL
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
            AND content LIKE :search
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset
        SQL, $pagination, ['follower_id' => $userId, 'tag_user_id' => $userId, 'search' => '%' . $search . '%']);

        return $this->rowsToFeed($result);
    }

    public function findFeedByUserId(int $id, Pagination $pagination = new Pagination()): Feed {
        $result = $this->fetchPage(<<<SQL
            SELECT *
            FROM touite
            WHERE users_id = :id
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset
        SQL, $pagination, ['id' => $id]);

        return $this->rowsToFeed($result);
    }

    public function findFeedWithMediasByUserId(int $id, Pagination $pagination = new Pagination()): Feed {
        $result = $this->fetchPage(<<<SQL
            SELECT *
            FROM touite
            WHERE users_id = :id
            AND media_path IS NOT NULL
            LIMIT :limit OFFSET :offset
        SQL, $pagination, ['id' => $id]);

        return $this->rowsToFeed($result);
    }

    public function findFeedByTagId(int $id, Pagination $pagination = new Pagination()): Feed {
        $result = $this->fetchPage(<<<SQL
            SELECT touite.*
            FROM touite
            INNER JOIN tag2touite on touite.id = tag2touite.touite_id
            WHERE tag2touite.tag_id = :id
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset
        SQL, $pagination, ['id' => $id]);

        return $this->rowsToFeed($result);
    }

    /**============================================
     * CREATIONS DES TOUITES
     *=============================================**/

    public function findTouiteById(int $id): ?Touite {
        $result = $this->fetchOne(<<<SQL
            SELECT *
            FROM touite
            WHERE id = :id
        SQL, ['id' => $id]);

        if (empty($result)) return null;

        return $this->rowToTouite($result);
    }

    /**============================================
     * CREATIONS DES TAGS
     *=============================================**/

    /**
     * @return Tag[] Touts les tags
     */
    public function findAllTags(int $limit = 1844674407370955161): array {
        if ($limit < 0) $limit = 0;

        $result = $this->fetchAll(<<<SQL
            SELECT tag.*
            FROM tag
            INNER JOIN users2tag ON users2tag.tag_id = tag.id
            GROUP BY tag_id
            ORDER BY COUNT(users_id) DESC, tag.id ASC
            LIMIT :limit
        SQL, ['limit' => $limit]);

        return $this->rowsToTags($result);
    }

    public function findTagByLibelle(string $libelle): ?Tag {
        $result = $this->fetchOne(<<<SQL
                SELECT *
                FROM tag
                WHERE libelle = :libelle
        SQL, ['libelle' => $libelle]);

        if (empty($result)) return null;

        return $this->rowToTag($result);
    }

    /**
     * @return Tag[]
     */
    public function findAllFollowedTagByUserId(int $userId): array {
        $result = $this->fetchAll(<<<SQL
            SELECT tag.*
            FROM tag
            INNER JOIN users2tag on users2tag.tag_id = tag.id
            WHERE users2tag.users_id = :user_id
        SQL, ['user_id' => $userId]);

        return $this->rowsToTags($result);
    }

    public function findTagById(int $id): ?Tag {
        $result = $this->fetchOne(<<<SQL
            SELECT *
            FROM tag
            WHERE id = :id;
        SQL, ['id' => $id]);

        if (empty($result)) return null;

        return $this->rowToTag($result);
    }

    /**
     * @return Tag[]
     */
    public function findTagsByTouiteId(int $id): array {
        $result = $this->fetchAll(<<<SQL
            SELECT tag.*
            FROM tag
            INNER JOIN tag2touite on tag.id = tag2touite.tag_id
            WHERE touite_id = :id;
        SQL, ['id' => $id]);

        return $this->rowsToTags($result);
    }

    /**============================================
     * CREATIONS DES USERS
     *=============================================**/

    public function findUserByTouiteId(int $touiteId): ?array {
        return $this->fetchOne(<<<SQL
            SELECT users_id as id, nom, prenom
            FROM touite
            INNER JOIN users on users.id = touite.users_id
            WHERE touite.id = :id
        SQL, ['id' => $touiteId]);
    }

    public function findUserById(int $userId): ?array {
        return $this->fetchOne(<<<SQL
            SELECT id, nom, prenom, counter_touite
            FROM users
            WHERE id = :id
        SQL, ['id' => $userId]);
    }

    public function findFollowersByUserId(int $userId): array {
        return $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom
            FROM users
            INNER JOIN follow on follow.follower_id = users.id
            WHERE followed_id = :user_id
        SQL, ['user_id' => $userId]);
    }

    public function findFollowedByUserId(int $userId): array {
        return $result = $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom
            FROM users
            INNER JOIN follow on follow.followed_id = users.id
            WHERE follower_id = :user_id
        SQL, ['user_id' => $userId]);
    }

    public function findAverageNoteTouitesByUserId(int $userId): float {
        $result = $this->fetchAll(<<<SQL
            SELECT ROUND(AVG(note),2) AS moyenne
            FROM note2touite
            INNER JOIN touite on touite.id = note2touite.touite_id
            WHERE touite.users_id = :user_id
        SQL, ['user_id' => $userId]);

        if (empty($result)) return 0.0;

        return (float) $result[0]['moyenne'];
    }

    /**============================================
     * CREATIONS DES CONDITIONS
     *=============================================**/

    public function isFollowingUser(int $followedId, int $followerId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM follow
            WHERE follower_id = :follower_id
            AND followed_id = :followed_id
        SQL, ['follower_id' => $followerId, 'followed_id' => $followedId]);
    }

    public function isFollowingTag(int $userId, int $tagId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM users2tag
            WHERE users_id = :user_id
            AND tag_id = :tag_id
        SQL, ['user_id' => $userId, 'tag_id' => $tagId]);
    }

    public function isLikedBy(int $userId, int $touiteId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM note2touite
            WHERE users_id = :user_id
            AND touite_id = :touite_id
            AND state = 1
        SQL, ['user_id' => $userId, 'touite_id' => $touiteId]);
    }

    public function isDislikedBy(int $userId, int $touiteId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM note2touite
            WHERE users_id = :user_id
            AND touite_id = :touite_id
            AND state = -1
        SQL, ['user_id' => $userId, 'touite_id' => $touiteId]);
    }

    /**============================================
     * GESTION DES PANELS ADMIN
     *=============================================**/

    public function findTrendingUsers(): array {
        return $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom, COUNT(follow.follower_id) AS nbFol, counter_touite
            FROM users
            INNER JOIN follow on users.id = follow.followed_id
            GROUP BY users.id
            ORDER BY nbFol DESC
            LIMIT 200
        SQL);
    }

        public function findTrendingTags(): array {
        return $this->fetchAll(<<<SQL
            SELECT tag.id, libelle, description, COUNT(tag2touite.tag_id) AS nbAparition
            FROM tag
            INNER JOIN tag2touite on tag2touite.tag_id = tag.id
            GROUP BY tag.id
            ORDER BY nbAparition DESC
            LIMIT :limit
        SQL, ['limit' => self::$limitTrendTag]);
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
