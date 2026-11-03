<?php

namespace iutnc\touiteur\repository;

class RepositoryFactory extends TouiteurRepository {
    
    private static ?TouiteurReaderRepository $readInstance = null;
    private static ?TouiteurWritterRepository $writeInstance = null;

    // Singleton pour la lecture
    public static function getReader(): TouiteurReaderRepository {
        if (is_null(self::$readInstance)) {
            self::$readInstance = new TouiteurReaderRepository();
        }
        return self::$readInstance;
    }

    // Singleton pour l'écriture
    public static function getWriter(): TouiteurWritterRepository {
        if (is_null(self::$writeInstance)) {
            self::$writeInstance = new TouiteurWritterRepository();
        }
        return self::$writeInstance;
    }
}