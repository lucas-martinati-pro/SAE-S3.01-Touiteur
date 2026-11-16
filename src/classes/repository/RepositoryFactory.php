<?php

namespace iutnc\touiteur\repository;

/**
 * Classe RepositoryFactory de l’application.
 */
class RepositoryFactory extends TouiteurRepository {
    /**
     * Valeur utilisée pour readInstance.
     */
    private static ?TouiteurReaderRepository $readInstance = null;
    /**
     * Valeur utilisée pour writeInstance.
     */
    private static ?TouiteurWriterRepository $writeInstance = null;

    // Singleton pour la lecture
    /**
     * Retourne le dépôt utilisé pour lire les données.
     * @return TouiteurReaderRepository Dépôt permettant de modifier les données.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public static function getReader(): TouiteurReaderRepository {
        if (is_null(self::$readInstance)) {
            self::$readInstance = new TouiteurReaderRepository();
        }
        return self::$readInstance;
    }

    // Singleton pour l'écriture
    /**
     * Retourne le dépôt utilisé pour modifier les données.
     * @return TouiteurWriterRepository Résultat du traitement.
     * @throws \PDOException En cas d’erreur lors de l’accès à la base de données.
     */
    public static function getWriter(): TouiteurWriterRepository {
        if (is_null(self::$writeInstance)) {
            self::$writeInstance = new TouiteurWriterRepository();
        }
        return self::$writeInstance;
    }
}