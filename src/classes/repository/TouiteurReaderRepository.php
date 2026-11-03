<?php

namespace iutnc\touiteur\repository;

use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\touite\Feed;
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
                $res['id'],
                $res['content'],
                $res['posted_at'],
                $res['image_path'] ?? null,
                $res['image_description'] ?? null
            );

        return $touite;
    }

    public function findAllTouites(): ?Feed {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT *
            FROM touite
            order by posted_at desc;
        SQL);

        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$res) return null;

        $touites = [];

        foreach ($res as $touite) {
            $touites[] = $this->rowToTouite($touite);
        }

        return new Feed($touites);
    }

    public function findFollowedFeed(string $email): ?Feed {
        $stmt = $this->pdo->prepare(<<<SQL
            SELECT *
            FROM touite
            inner join users on users.id = touites.user_id
            WHERE email = :email;
        SQL);

        $stmt->execute(['email' => $email]);

        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) return null;

        return null;
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