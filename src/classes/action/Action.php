<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;

/**
 * Classe abstraite de base pour l'ensemble des actions de l'application.
 */
abstract class Action {
    /**
     * Valeur utilisée pour http method.
     */
    protected ?string $http_method = null;
    /**
     * Valeur utilisée pour hostname.
     */
    protected ?string $hostname = null;
    /**
     * Valeur utilisée pour script name.
     */
    protected ?string $script_name = null;

    /**
     * Indique si l'action nécessite une authentification préalable. Vaut true par défaut, à surcharger à false pour les actions publiques.
     */
    protected bool $requireAuth = true;

    /**
     * Données de l'utilisateur connecté, ou null s'il n'est pas connecté.
     * @var array{id: int|string, email: string, passwd: string, nom: string, prenom: string, counter_touite: int|string}|null
     */
    protected ?array $user = null;

    /**
     * Constructeur d'action. Initialise les attributs d'environnement du serveur et récupère l'utilisateur connecté s'il existe.
     */
    public function __construct() {
        $this->http_method = $_SERVER['REQUEST_METHOD'];

        try {
            $this->user = AuthnProvider::getSignedInUser();
        } catch (AuthnException) {
            $this->user = null;
        }
    }

    /**
     * Exécute l'action appropriée après vérification de l'authentification requise. Appelle get() ou post() selon la méthode HTTP courante.
     * @return string Texte ou contenu HTML généré.
     */
    public function execute(): string {
        if ($this->requireAuth && $this->user === null) {
            return HtmlHelper::authRequired();
        }

        $error = $this->check();
        if ($error !== null) return $error;

        return match ($this->http_method) {
            'GET' => $this->get(),
            'POST' => $this->post(),
            default => '',
        };
    }

    /**
     * Permet d'invoquer directement l'instance de l'action comme une fonction.
     * @return string Texte ou contenu HTML généré.
     */
    public function __invoke(): string {
        return $this->execute();
    }

    /**
     * Génère le contenu de la page correspondant à cette action.
     * @return string Texte ou contenu HTML généré.
     */
    public function get(): string {
        return '';
    }

    /**
     * Traite les données envoyées par le formulaire.
     * @return string Texte ou contenu HTML généré.
     */
    public function post(): string {
        return '';
    }

    /**
     * Vérifie les conditions de l'action (existence des paramètres, droits, ressources).
     * @return ?string Message d’erreur HTML, ou null si la vérification réussit.
     */
    protected function check(): ?string {
        return null;
    }
}
