<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\exception\AuthnException;

/**
 * Classe abstraite de base pour l'ensemble des actions de l'application.
 */
abstract class Action {
    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    /**
     * Indique si l'action nécessite une authentification préalable.
     * Vaut true par défaut, à surcharger à false pour les actions publiques.
     */
    protected bool $requireAuth = true;

    /**
     * Données de l'utilisateur connecté sous forme de tableau , ou null s'il n'est pas connecté.
     */
    protected ?array $user = null;

    /**
     * Constructeur d'action.
     * Initialise les attributs d'environnement du serveur et récupère l'utilisateur connecté s'il existe.
     */
    public function __construct() {
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];

        try {
            $this->user = AuthnProvider::getSignedInUser();
        } catch (AuthnException) {
            $this->user = null;
        }
    }

    /**
     * Exécute l'action appropriée après vérification de l'authentification requise.
     * Appelle get() ou post() selon la méthode HTTP courante.
     *
     * @return string Balises HTML générées en réponse à la requête.
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
     *
     * @return string Résultat de l'exécution de l'action.
     */
    public function __invoke(): string {
        return $this->execute();
    }

    /**
     * Traite les requêtes HTTP de type GET.
     *
     * @return string Code HTML généré pour l'affichage (formulaire, vue, etc.).
     */
    public function get(): string {
        return '';
    }

    /**
     * Traite les requêtes HTTP de type POST.
     *
     * @return string Code HTML résultant du traitement (succès, redirection, erreur).
     */
    public function post(): string {
        return '';
    }

    /**
     * Vérifie les conditions de l'action (existence des paramètres, droits, ressources).
     *
     * @return string|null Message d'erreur HTML si la vérification échoue, null sinon.
     */
    protected function check(): ?string {
        return null;
    }
}