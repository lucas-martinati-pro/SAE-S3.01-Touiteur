<?php
/**
 * Point d’entrée du back-office.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use iutnc\touiteur\admin\dispatch\AdminDispatcher;
use iutnc\touiteur\exception\ConfigIOException;
use iutnc\touiteur\repository\TouiteurRepository;

require_once __DIR__ . '/../src/vendor/autoload.php';

session_start();

try {
    TouiteurRepository::setConfig(__DIR__ . '/../src/config/touiteur.db.ini');
} catch (ConfigIOException) {
    http_response_code(500);
    exit('Unable to load the database configuration.');
}

(new AdminDispatcher($_GET['action'] ?? 'default'))->run();
