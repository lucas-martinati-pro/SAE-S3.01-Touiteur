<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\enum\TypeNote;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\tag\Tag;

/**
 * Repository centralisant les opérations d'accès à la base de données.
 */
class TouiteurWriterRepository extends TouiteurRepository {
    /**========================================================================
     * GESTION DES FOLLOWS
     *========================================================================*/

    /**
     * Abonne un utilisateur à un utilisateur ou à un tag.
     * @param int $userId identifiant de l’utilisateur.
     * @param int $followedId identifiant de l’utilisateur ou du tag suivi.
     * @param string $type type demandé.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function follow(int $userId, int $followedId, string $type): void {
        match ($type) {
            'user' => $this->updateRequest(<<<SQL
                        INSERT INTO follow (follower_id, followed_id) VALUES (:follower_id, :followed_id);
                    SQL, ['follower_id' => $userId, 'followed_id' => $followedId]),
            'tag' => $this->updateRequest(<<<SQL
                        INSERT INTO users2tag (users_id, tag_id) VALUES (:user_id, :tag_id);
                    SQL, ['user_id' => $userId, 'tag_id' => $followedId]),
        };
    }

    /**
     * Retire l’abonnement d’un utilisateur à un utilisateur ou à un tag.
     * @param int $userId identifiant de l’utilisateur.
     * @param int $followedId identifiant de l’utilisateur ou du tag suivi.
     * @param string $type type demandé.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function unfollow(int $userId, int $followedId, string $type): void {
        match ($type) {
            'user' => $this->updateRequest(<<<SQL
                        DELETE FROM follow
                        WHERE follower_id = :follower_id
                        AND followed_id = :followed_id;
                    SQL, ['follower_id' => $userId, 'followed_id' => $followedId]),
            'tag' => $this->updateRequest(<<<SQL
                        DELETE FROM users2tag
                        WHERE users_id = :user_id
                        AND tag_id = :tag_id;
                    SQL, ['user_id' => $userId, 'tag_id' => $followedId]),
        };
    }

    /**========================================================================
     * GESTION DES TOUITES
     *========================================================================*/

    /**
     * Enregistre ou annule la note donnée à un touite.
     * @param int $userId identifiant de l’utilisateur.
     * @param int $touiteId identifiant du touite.
     * @param TypeNote $state note à enregistrer.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function noteTouite(int $userId, int $touiteId, TypeNote $state): void {
        if ($state === TypeNote::annuler) {
            $this->updateRequest(<<<SQL
                DELETE FROM note2touite
                WHERE users_id = :user_id
                AND touite_id = :touite_id
            SQL, ['user_id' => $userId, 'touite_id' => $touiteId]);
        } else {
            $this->updateRequest(<<<SQL
                INSERT INTO note2touite (users_id, touite_id, state)
                VALUES (:user_id, :touite_id, :state)
                ON DUPLICATE KEY UPDATE state = VALUES(state)
            SQL, ['user_id' => $userId, 'touite_id' => $touiteId, 'state' => $state->getNote()]);
        }
    }

    /**
     * Enregistre un touite et retourne l’objet mis à jour.
     * @param Touite $touite touite à afficher ou à enregistrer.
     * @param int $userId identifiant de l’utilisateur.
     * @return Touite Touite créé ou converti.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function addTouite(Touite $touite, int $userId): Touite {
        $this->updateRequest(<<<SQL
                INSERT INTO touite (content, note, media_path,media_description, posted_at, users_id) VALUES (:content, :note, :media_path, :media_desc, :posted_at, :user_id)
        SQL, ['content' => $touite->getContent(), 'note' => 0, 'media_path' => $touite->getMediaPath(), 'media_desc' => $touite->getMediaDescription(), 'posted_at' => $touite->getPostedAt(), 'user_id' => $userId]);

        $touite->setId($this->pdo->lastInsertId());

        return $touite;
    }

    /**
     * Associe un tag à un touite et crée le tag si nécessaire.
     * @param Tag $tag valeur de tag.
     * @param int $touiteId identifiant du touite.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function addTouiteTag(Tag $tag, int $touiteId): void {
        $tagId = $tag->getId();
        if ($tagId === null) {
            $this->updateRequest(<<<SQL
                INSERT INTO tag (libelle, description) VALUES (:nom, :desc);
            SQL, ['nom' => $tag->getLibelle(), 'desc' => $tag->getDescription()]);

            $tagId = $this->pdo->lastInsertId();
        }

        $this->updateRequest(<<<SQL
            INSERT INTO tag2touite (touite_id, tag_id) VALUES (:touite_id, :tag_id);
        SQL, ['touite_id' => $touiteId, 'tag_id' => $tagId]);
    }

    /**
     * Supprime un touite, ses réactions et ses tags associés.
     * @param Touite $touite touite à afficher ou à enregistrer.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function deleteTouite(Touite $touite): void {
        $this->updateRequest(<<<SQL
            DELETE FROM note2touite WHERE touite_id = :touite_id;
        SQL, ['touite_id' => $touite->getId()]);

        $this->updateRequest(<<<SQL
            DELETE FROM tag2touite WHERE touite_id = :touite_id;
        SQL, ['touite_id' => $touite->getId()]);

        $this->updateRequest(<<<SQL
            DELETE FROM touite WHERE id = :touite_id;
        SQL, ['touite_id' => $touite->getId()]);
    }

    /**============================================
     *               GESTION DE L'AUTH
     *=============================================**/

    /**
     * Crée un compte utilisateur avec un mot de passe haché.
     * @param string $email adresse e-mail de l’utilisateur.
     * @param string $password mot de passe de l’utilisateur.
     * @param string $nom nom de l’utilisateur.
     * @param string $prenom prénom de l’utilisateur.
     * @return void Aucune valeur.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public function addUser(string $email, string $password, string $nom, string $prenom): void {
        // Double par précaution;
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $nom = filter_var($nom, FILTER_SANITIZE_SPECIAL_CHARS);
        $prenom = filter_var($prenom, FILTER_SANITIZE_SPECIAL_CHARS);
        $password = password_hash($password, PASSWORD_DEFAULT);

        $this->updateRequest(<<<SQL
            INSERT INTO users (email, passwd, nom, prenom, counter_touite) VALUES (:email, :password, :nom, :prenom, 0)
        SQL, ['email' => $email, 'password' => $password, 'nom' => $nom, 'prenom' => $prenom]);
    }
}
