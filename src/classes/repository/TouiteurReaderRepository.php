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
    /**
     * Nombre de tags affichés dans la liste des tendances.
     */
    static $limitTrendTag = 200;

    /**========================================================================
     * CREATIONS DES TOUITES/TAGS
     *========================================================================*/

    /**
     * Convertit une ligne de résultat en objet Touite.
     * @param array{id: int|string, content: string, posted_at: string, note: int|string, media_path?: string|null, media_description?: string|null} $result résultat d’une requête de base de données.
     * @return Touite Touite converti.
     */
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

    /**
     * Convertit des lignes de résultat en fil de touites.
     * @param list<array{id: int|string, content: string, posted_at: string, note: int|string, media_path?: string|null, media_description?: string|null}> $result résultat d’une requête de base de données.
     * @return Feed Fil construit à partir des touites.
     */
    private function rowsToFeed(array $result): Feed {
        $touites = [];

        foreach ($result as $touite) {
            $touites[] = $this->rowToTouite($touite);
        }

        return new Feed($touites);
    }

    /**
     * Convertit une ligne de résultat en objet Tag.
     * @param array{id: int|string, libelle: string, description: string} $result résultat d’une requête de base de données.
     * @return Tag Tag converti.
     */
    public function rowToTag(array $result): Tag {
        $tag = new Tag(
                $result['libelle'],
                $result['description'],
                $result['id']
            );

        return $tag;
    }

    /**
     * Convertit des lignes de résultat en liste de tags.
     * @param list<array{id: int|string, libelle: string, description: string}> $result résultat d’une requête de base de données.
     * @return list<Tag> Liste des tags convertis.
     */
    public function rowsToTags(array $result): array {
        $tags = [];

        foreach ($result as $tag) {
            $tags[] = $this->rowToTag($tag);
        }

        return $tags;
    }

    /**========================================================================
     * CREATIONS DES FEEDS
     *========================================================================*/

    /**
     * Recherche les touites qui correspondent au texte demandé.
     * @param string $search texte à rechercher.
     * @param Pagination $pagination paramètres de pagination.
     * @return ?Feed Fil correspondant à la recherche, ou null.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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

    /**
     * Recherche les touites publiés par les comptes et les tags suivis par un utilisateur.
     * @param int $userId identifiant de l’utilisateur.
     * @param string $search texte à rechercher.
     * @param Pagination $pagination paramètres de pagination.
     * @return Feed Fil des comptes et tags suivis.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function findFollowedFeedByUserId(int $userId, string $search = '', Pagination $pagination = new Pagination()): Feed {
        $result = $this->fetchPage(<<<SQL
            SELECT touite.*
            FROM touite
            WHERE (
                touite.users_id IN (
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
            )
            AND content LIKE :search
            ORDER BY posted_at DESC
            LIMIT :limit OFFSET :offset
        SQL, $pagination, ['follower_id' => $userId, 'tag_user_id' => $userId, 'search' => '%' . $search . '%']);

        return $this->rowsToFeed($result);
    }

    /**
     * Recherche les touites publiés par un utilisateur.
     * @param int $id identifiant de la ressource.
     * @param Pagination $pagination paramètres de pagination.
     * @return Feed Fil publié par l’utilisateur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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

    /**
     * Recherche les touites avec média publiés par un utilisateur.
     * @param int $id identifiant de la ressource.
     * @param Pagination $pagination paramètres de pagination.
     * @return Feed Fil des touites avec média.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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

    /**
     * Recherche les touites associés à un tag.
     * @param int $id identifiant de la ressource.
     * @param Pagination $pagination paramètres de pagination.
     * @return Feed Fil des touites associés au tag.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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

    /**========================================================================
     * CREATIONS DES TOUITES
     *========================================================================*/

    /**
     * Recherche un touite à partir de son identifiant.
     * @param int $id identifiant de la ressource.
     * @return ?Touite Touite trouvé, ou null.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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
     * Retourne les tags les plus suivis.
     * @param int $limit nombre maximal d’éléments.
     * @return list<Tag> Liste des tags les plus suivis.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
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

    /**
     * Recherche un tag à partir de son libellé.
     * @param string $libelle libellé du tag.
     * @return ?Tag Tag trouvé, ou null.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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
     * Retourne les tags suivis par un utilisateur.
     * @param int $userId identifiant de l’utilisateur.
     * @return list<Tag> Liste des tags suivis.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
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

    /**
     * Recherche un tag à partir de son identifiant.
     * @param int $id identifiant de la ressource.
     * @return ?Tag Tag trouvé, ou null.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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
     * Retourne les tags associés à un touite.
     * @param int $id identifiant de la ressource.
     * @return list<Tag> Liste des tags associés au touite.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
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

    /**========================================================================
     * CREATIONS DES USERS
     *========================================================================*/

    /**
     * Recherche l’auteur d’un touite.
     * @param int $touiteId identifiant du touite.
     * @return array{id: int|string, nom: string, prenom: string}|null Données de l’auteur, ou null.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function findUserByTouiteId(int $touiteId): ?array {
        return $this->fetchOne(<<<SQL
            SELECT users_id as id, nom, prenom
            FROM touite
            INNER JOIN users on users.id = touite.users_id
            WHERE touite.id = :id
        SQL, ['id' => $touiteId]);
    }

    /**
     * Recherche un utilisateur à partir de son identifiant.
     * @param int $userId identifiant de l’utilisateur.
     * @return array{id: int|string, nom: string, prenom: string, counter_touite: int|string}|null Données de l’utilisateur, ou null.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function findUserById(int $userId): ?array {
        return $this->fetchOne(<<<SQL
            SELECT id, nom, prenom, counter_touite
            FROM users
            WHERE id = :id
        SQL, ['id' => $userId]);
    }

    /**
     * Retourne les abonnés d’un utilisateur.
     * @param int $userId identifiant de l’utilisateur.
     * @return list<array{id: int|string, nom: string, prenom: string}> Liste des abonnés.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function findFollowersByUserId(int $userId): array {
        return $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom
            FROM users
            INNER JOIN follow on follow.follower_id = users.id
            WHERE followed_id = :user_id
        SQL, ['user_id' => $userId]);
    }

    /**
     * Retourne les utilisateurs suivis par un utilisateur.
     * @param int $userId identifiant de l’utilisateur.
     * @return list<array{id: int|string, nom: string, prenom: string}> Liste des utilisateurs suivis.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function findFollowedByUserId(int $userId): array {
        return $result = $this->fetchAll(<<<SQL
            SELECT users.id, nom, prenom
            FROM users
            INNER JOIN follow on follow.followed_id = users.id
            WHERE follower_id = :user_id
        SQL, ['user_id' => $userId]);
    }

    /**
     * Calcule la note moyenne des touites publiés par un utilisateur.
     * @param int $userId identifiant de l’utilisateur.
     * @return float Note moyenne des touites.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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

    /**========================================================================
     * CREATIONS DES CONDITIONS
     *========================================================================*/

    /**
     * Indique si un utilisateur suit un autre utilisateur.
     * @param int $followedId identifiant de l’utilisateur ou du tag suivi.
     * @param int $followerId identifiant de l’abonné.
     * @return bool true si l’abonnement existe, sinon false.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function isFollowingUser(int $followedId, int $followerId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM follow
            WHERE follower_id = :follower_id
            AND followed_id = :followed_id
        SQL, ['follower_id' => $followerId, 'followed_id' => $followedId]);
    }

    /**
     * Indique si un utilisateur suit un tag.
     * @param int $userId identifiant de l’utilisateur.
     * @param int $tagId identifiant du tag.
     * @return bool true si l’abonnement existe, sinon false.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function isFollowingTag(int $userId, int $tagId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM users2tag
            WHERE users_id = :user_id
            AND tag_id = :tag_id
        SQL, ['user_id' => $userId, 'tag_id' => $tagId]);
    }

    /**
     * Indique si un utilisateur a aimé un touite.
     * @param int $userId identifiant de l’utilisateur.
     * @param int $touiteId identifiant du touite.
     * @return bool true si l’utilisateur a aimé le touite, sinon false.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function isLikedBy(int $userId, int $touiteId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM note2touite
            WHERE users_id = :user_id
            AND touite_id = :touite_id
            AND state = 1
        SQL, ['user_id' => $userId, 'touite_id' => $touiteId]);
    }

    /**
     * Indique si un utilisateur n’a pas aimé un touite.
     * @param int $userId identifiant de l’utilisateur.
     * @param int $touiteId identifiant du touite.
     * @return bool true si l’utilisateur n’a pas aimé le touite, sinon false.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function isDislikedBy(int $userId, int $touiteId): bool {
        return $this->exists(<<<SQL
            SELECT 1
            FROM note2touite
            WHERE users_id = :user_id
            AND touite_id = :touite_id
            AND state = -1
        SQL, ['user_id' => $userId, 'touite_id' => $touiteId]);
    }

    /**========================================================================
     * GESTION DES PANELS ADMIN
     *========================================================================*/

    /**
     * Retourne les utilisateurs les plus suivis.
     * @return list<array{id: int|string, nom: string, prenom: string, nbFol: int|string, counter_touite: int|string}> Liste des utilisateurs les plus suivis.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
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

        /**
         * Retourne les tags les plus présents dans les touites.
         * @return list<array{id: int|string, libelle: string, description: string|null, nbAparition: int|string}> Liste des tags les plus présents.
         * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
         */
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
     * Recherche un utilisateur par son adresse e-mail.
     * @param string $email adresse e-mail de l’utilisateur.
     * @return array{id: int|string, email: string, passwd: string, nom: string, prenom: string, counter_touite: int|string}|null Données du compte, ou null.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function findUserByEmail(string $email): ?array {
        return $this->fetchOne(<<<SQL
            SELECT id, email, passwd, nom, prenom, counter_touite
            FROM users
            WHERE email = :email
        SQL, ['email' => $email]);
    }

    /**
     * Indique si l’adresse e-mail est déjà utilisée.
     * @param string $email adresse e-mail de l’utilisateur.
     * @return bool true si l’adresse est déjà utilisée, sinon false.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function existsByEmail(string $email): bool {
        return $this->exists(<<<SQL
            SELECT id
            FROM users
            WHERE email = :email
        SQL, ['email' => $email]);
    }

    /**
     * Indique si un utilisateur possède les droits d’administration.
     * @param array{id: int|string} $user données de l’utilisateur.
     * @return bool true si l’utilisateur est administrateur, sinon false.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function isAdminUser(array $user): bool {
        return $this->exists(<<<SQL
            SELECT id
            from users
            WHERE id = :id
            AND role = 100
        SQL, ['id' => $user['id']]);
    }
}
