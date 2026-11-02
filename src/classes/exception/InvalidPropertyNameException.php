<?php

namespace iutnc\touiteur\exception;

use Exception;

/**
 * Exception levée lors d'une tentative d'accès ou de modification d'une propriété inexistante ou interdite.
 */
class InvalidPropertyNameException extends Exception {}