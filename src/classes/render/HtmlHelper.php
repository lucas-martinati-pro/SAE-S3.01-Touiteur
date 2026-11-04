<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\enum\TypeRender;

/**
 * Classe d'assistance pour la génération des composants HTML de Touiteur.
 */
class HtmlHelper {

    public static function subscriptionButton(int $userId, bool $isSubscribed): string {
        $type = $isSubscribed ? 'unsub' : 'sub';
        $label = $isSubscribed ? 'Abonné' : "S'abonner";
        $variant = $isSubscribed ? 'outline-primary' : 'primary';

        return <<<HTML
            <form method="POST" action="?action=subscribe">
                <input type="hidden" name="type" value="{$type}">
                <input type="hidden" name="id" value="{$userId}">
                <button type="submit" class="btn btn-sm btn-{$variant}">{$label}</button>
            </form>
        HTML;
    }

    public static function touiteActions(int $touiteId, int $note, TypeRender $selector): string {
        $noteClass = $note < 0
            ? 'touite-note-negative'
            : ($note > 0 ? 'touite-note-positive' : 'touite-note-neutral');
        $noteHtml = "<span class=\"touite-note {$noteClass}\" aria-label=\"Note du touite\"><i class=\"bi bi-heart\"></i> {$note}</span>";

        if ($selector === TypeRender::COMPACT) {
            return $noteHtml;
        }

        return <<<HTML
            {$noteHtml}
            <form method="POST" action="?action=LikeAction&id={$touiteId}&type=like">
                <button type="submit" aria-label="J'aime"><i class="bi bi-hand-thumbs-up"></i> J'aime</button>
            </form>
            <form method="POST" action="?action=LikeAction&id={$touiteId}&type=dislike">
                <button type="submit" aria-label="Je n'aime pas"><i class="bi bi-hand-thumbs-down"></i> Je n'aime pas</button>
            </form>
            <form method="POST" action="?action=LikeAction&id={$touiteId}&type=annuler">
                <button type="submit" aria-label="Annuler ma note"><i class="bi bi-x-circle"></i> Annuler</button>
            </form>
        HTML;
    }

    public static function button(string $label, string $icon = '', string $variant = 'primary', string $type = 'submit'): string {
        $iconHtml = $icon !== '' ? "<i class=\"bi {$icon}\" aria-hidden=\"true\"></i>" : '';

        return "<button type=\"{$type}\" class=\"btn btn-{$variant}\">{$iconHtml}{$label}</button>";
    }

    public static function linkButton(string $label, string $url, string $icon = '', string $variant = 'primary'): string {
        $iconHtml = $icon !== '' ? "<i class=\"bi {$icon}\" aria-hidden=\"true\"></i>" : '';

        return "<a class=\"btn btn-{$variant}\" href=\"{$url}\">{$iconHtml}{$label}</a>";
    }

    public static function backLink(string $url = '?action=displayFeed', string $label = 'Retour au feed'): string {
        return <<<HTML
            <a class="back-link" href="{$url}" aria-label="{$label}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
        HTML;
    }

    public static function detailHeader(string $title, string $backUrl = '?action=displayFeed'): string {
        $backLink = self::backLink($backUrl);

        return <<<HTML
            <header class="feed-header detail-header">
                {$backLink}
                <h1>{$title}</h1>
            </header>
        HTML;
    }

    public static function pagination(string $ariaLabel, string $previousUrl, string $nextUrl, int $page): string {
        $previousAttributes = $page === 1 ? ' aria-disabled="true" tabindex="-1"' : '';

        return <<<HTML
            <nav class="feed-pagination" aria-label="{$ariaLabel}">
                <a class="btn btn-outline-secondary"{$previousAttributes} href="{$previousUrl}">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    Page précédente
                </a>
                <span>Page {$page}</span>
                <a class="btn btn-outline-secondary" href="{$nextUrl}">
                    Page suivante
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            </nav>
        HTML;
    }

    /**
     * Génère un titre de page cohérent avec le design de Touiteur.
     *
     * @param string $title Texte du titre à afficher.
     * @param string $icon Nom ou classe de l'icône Bootstrap Icons (ex: 'bi-music-note', 'music-note' ou 'bi bi-music-note').
     * @param string $color Classe CSS de couleur (ex: 'text-primary', 'text-danger'). Défaut: 'text-primary'.
     * @return string Balises HTML du titre formaté.
     */
    public static function title(string $title = '', string $icon = '', string $color = 'text-primary'): string {
        return <<<HTML
            <h1 class="page-title {$color}">
                <i class="bi {$icon}" aria-hidden="true"></i>
                {$title}
            </h1>
        HTML;
    }

    /**
     * Génère une alerte Touiteur (texte simple ou liste non ordonnée).
     *
     * @param string $type Type d'alerte ('success', 'warning', 'danger', 'info'). Par défaut 'info'.
     * @param string|array $content Texte unique ou tableau de messages à afficher.
     * @return string Balises HTML de l'alerte Bootstrap.
     */
    public static function alert(string $type = 'info', string|array $content = ''): string {
        $icon = match ($type) {
            'success' => 'bi-check-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'danger'  => 'bi-exclamation-octagon-fill',
            'info'    => 'bi-info-circle-fill',
            default   => 'bi-bell-fill',
        };

        if (is_array($content)) {
            $items = '';
            foreach ($content as $msg) {
                $items .= "<li>{$msg}</li>";
            }
            $body = "<ul class=\"mb-0 ps-3\">{$items}</ul>";
        } else {
            $body = $content;
        }

        return <<<HTML
            <div class="alert alert-{$type}" role="alert">
                <i class="bi {$icon}" aria-hidden="true"></i>
                <div>
                    {$body}
                </div>
            </div>
        HTML;
    }

    /**
     * Génère un écran d'erreur générique complet avec bouton de retour.
     *
     * @param string $title Titre affiché en tête de page (défaut: 'Erreur').
     * @param string|array $message Message d'erreur ou liste d'erreurs.
     * @param string $backUrl URL du lien de retour (défaut: 'index.php').
     * @param string $backLabel Libellé du bouton de retour (défaut: 'Retour à l'accueil').
     * @param string $type Type d'alerte Bootstrap ('danger', 'warning', etc. Défaut: 'danger').
     * @return string Balises HTML de la page d'erreur.
     */
    public static function errorPage(string $title = "Erreur", string|array $message = "Une erreur inattendue est survenue.", string $backUrl = 'index.php', string $backLabel = "Retour à l'accueil", string $type = 'danger'): string {
        $alertHtml = self::alert($type, $message);

        $titleColor = match ($type) {
            'success' => 'text-success',
            'warning' => 'text-warning',
            'danger'  => 'text-danger',
            default   => 'text-primary',
        };

        // Icône Bootstrap - https://icons.getbootstrap.com/icons/exclamation-triangle-fill/
        $titleHtml = self::title($title, 'bi-exclamation-triangle-fill', $titleColor);

        return <<<HTML
            {$titleHtml}
            {$alertHtml}
            <p class="mt-3">
                <a class="btn btn-secondary d-inline-flex align-items-center" href="{$backUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/arrow-left/ -->
                    <i class="bi bi-arrow-left me-2"></i>
                    {$backLabel}
                </a>
            </p>
        HTML;
    }

    /**
     * Génère un écran d'accès refusé quand l'utilisateur n'est pas connecté.
     *
     * @param string $message Message explicatif (défaut: 'Vous devez être connecté pour accéder à cette page.').
     * @param string $loginUrl URL vers la page de connexion (défaut: '?action=signin').
     * @param string $loginLabel Libellé du bouton de connexion (défaut: 'Se connecter').
     * @param string $backUrl URL du bouton retour (défaut: 'index.php').
     * @param string $backLabel Libellé du bouton retour (défaut: 'Retour à l'accueil').
     * @return string Balises HTML de l'écran d'accès refusé.
     */
    public static function authRequired(string $message = "Vous devez être connecté pour accéder à cette page.", string $loginUrl = "?action=signin", string $loginLabel = "Se connecter", string $backUrl = "index.php", string $backLabel = "Retour à l'accueil"): string {
        $alert = self::alert('warning', $message);
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/shield-lock-fill/
        $titleHtml = self::title("Accès refusé", "bi-shield-lock-fill", "text-danger");

        return <<<HTML
            {$titleHtml}
            {$alert}
            <p class="mt-3">
                <a class="btn btn-primary d-inline-flex align-items-center" href="{$loginUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/box-arrow-in-right/ -->
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    {$loginLabel}
                </a>
                <a class="btn btn-secondary ms-2 d-inline-flex align-items-center" href="{$backUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/house-door-fill/ -->
                    <i class="bi bi-house-door-fill me-1"></i>
                    {$backLabel}
                </a>
            </p>
        HTML;
    }

    /**
     * Génère un écran d'accès interdit pour un utilisateur sans droits suffisants.
     *
     * @param string $message Message d'erreur explicatif.
     * @param string $backUrl URL de retour (défaut: '?action=displayFeed').
     * @param string $backLabel Libellé du bouton retour (défaut: 'Retour à mon feed').
     * @return string Balises HTML de l'écran d'interdiction.
     */
    public static function forbidden(string $message = "Vous n'êtes pas autorisé à effectuer cette action.", string $backUrl = "?action=displayFeed", string $backLabel = "Retour à mon feed"): string {
        $alert = self::alert('danger', $message);
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/shield-lock-fill/
        $titleHtml = self::title("Accès refusé", "bi-shield-lock-fill", "text-danger");

        return <<<HTML
            {$titleHtml}
            {$alert}
            <p class="mt-3">
                <a class="btn btn-secondary d-inline-flex align-items-center" href="{$backUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/arrow-left/ -->
                    <i class="bi bi-arrow-left me-2"></i>
                    {$backLabel}
                </a>
            </p>
        HTML;
    }

    /**
     * Génère un écran pour ressource introuvable.
     *
     * @param string $item Nom de la ressource introuvable.
     * @param string $message Message d'erreur explicatif.
     * @param string $backUrl URL du lien de retour (défaut: '?action=displayFeed').
     * @param string $backLabel Libellé du bouton de retour (défaut: 'Retour à mon compte').
     * @return string Balises HTML de la page 404.
     */
    public static function notFound(string $item = "Ressource", string $message = "L'élément demandé n'existe pas ou est introuvable.", string $backUrl = "?action=displayFeed", string $backLabel = "Retour à mon feed"): string {
        $alert = self::alert('danger', $message);
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/exclamation-triangle-fill/
        $titleHtml = self::title("{$item} introuvable", "bi-exclamation-triangle-fill", "text-danger");

        return <<<HTML
            {$titleHtml}
            {$alert}
            <p class="mt-3">
                <a class="btn btn-secondary d-inline-flex align-items-center" href="{$backUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/arrow-left/ -->
                    <i class="bi bi-arrow-left me-2"></i>
                    {$backLabel}
                </a>
            </p>
        HTML;
    }

    /**
     * Génère un écran d'erreur de formulaire avec bouton de retour arrière vers le formulaire.
     *
     * @param string|array $errors Message d'erreur unique ou tableau de messages.
     * @param string $backUrl URL de retour (par défaut: 'index.php').
     * @param string $backLabel Libellé du bouton de retour (défaut: 'Retour au formulaire').
     * @param string $title Titre de l'écran d'erreur (défaut: 'Erreur dans le formulaire').
     * @return string Balises HTML de l'écran d'erreur de formulaire.
     */
    public static function formError(string|array $errors = "Une erreur est survenue lors de la validation du formulaire.", string $backUrl = "index.php", string $backLabel = "Retour au formulaire", string $title = "Erreur dans le formulaire"): string {
        $alert = self::alert('danger', $errors);
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/exclamation-triangle-fill/
        $titleHtml = self::title($title, "bi-exclamation-triangle-fill", "text-danger");

        return <<<HTML
            {$titleHtml}
            {$alert}
            <p class="mt-3">
                <a class="btn btn-secondary d-inline-flex align-items-center" href="{$backUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/arrow-counterclockwise/ -->
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    {$backLabel}
                </a>
            </p>
        HTML;
    }

    /**
     * Génère un écran de confirmation de succès avec bouton d'action suivante.
     *
     * @param string $title Titre du message de succès (défaut: 'Opération réussie').
     * @param string $message Message de confirmation textuel ou HTML.
     * @param string $backUrl URL de redirection ou d'action suivante (défaut: '?action=displayFeed').
     * @param string $backLabel Libellé du bouton d'action suivante (défaut: 'Retour à mon feed').
     * @return string Balises HTML de la page de confirmation de succès.
     */
    public static function successPage(string $title = "Opération réussie", string $message = "L'opération s'est déroulée avec succès.", string $backUrl = "?action=displayFeed", string $backLabel = "Retour à mon feed"): string {
        $alert = self::alert('success', $message);
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/check-circle-fill/
        $titleHtml = self::title($title, "bi-check-circle-fill", "text-success");

        return <<<HTML
            {$titleHtml}
            {$alert}
            <p class="mt-3">
                <a class="btn btn-primary d-inline-flex align-items-center" href="{$backUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/arrow-left/ -->
                    <i class="bi bi-arrow-left me-2"></i>
                    {$backLabel}
                </a>
            </p>
        HTML;
    }
}