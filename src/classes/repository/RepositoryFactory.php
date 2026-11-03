<?php

namespace iutnc\touiteur\repository;

class RepositoryFactory extends TouiteurRepository {
    private static ?TouiteurReaderRepository $readInstance = null;
    private static ?TouiteurWriterRepository $writeInstance = null;

    // Singleton pour la lecture
    public static function getReader(): TouiteurReaderRepository {
        if (is_null(self::$readInstance)) {
            self::$readInstance = new TouiteurReaderRepository();
        }
        return self::$readInstance;
    }

    // Singleton pour l'écriture
    public static function getWriter(): TouiteurWriterRepository {
        if (is_null(self::$writeInstance)) {
            self::$writeInstance = new TouiteurWriterRepository();
        }
        return self::$writeInstance;
    }
}