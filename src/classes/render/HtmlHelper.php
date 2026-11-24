<?php

namespace iutnc\touiteur\render;

use iutnc\touiteur\auth\AuthnProvider;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\exception\AuthnException;
use iutnc\touiteur\pagination\Pagination;
use iutnc\touiteur\repository\RepositoryFactory;

/**
 * Classe d'assistance pour la génération des composants HTML de Touiteur.
 */
class HtmlHelper {
    /**
     * Génère le formulaire de publication d’un touite.
     * @param array{id: int|string, nom?: string, prenom?: string} $user données de l’utilisateur.
     * @return string Texte ou contenu HTML généré.
     */
    public static function addTouite(array $user): string {
        $profileAvatar = HtmlHelper::profileAvatar($user);
        return <<<HTML
            <section class="compose-box" aria-label="Write a touite">
                {$profileAvatar}
                <form class="compose-form" method="post" action="?action=addTouite" enctype="multipart/form-data">
                    <div class="compose-fields">
                        <textarea
                            name="contenu"
                            class="compose-textarea"
                            id="contenu"
                            maxlength="235"
                            rows="1"
                            placeholder="What's new?"
                            aria-label="Touite content"
                            required
                        ></textarea>
                    </div>
                    <div class="compose-footer">
                        <details class="compose-attachment">
                            <summary aria-label="Add an image or video" title="Add an image or video">
                                <i class="bi bi-image" aria-hidden="true"></i>
                            </summary>
                            <div class="compose-attachment-panel">
                                <label class="compose-file-label" for="media">Add an image or video</label>
                                <input
                                    type="file"
                                    name="media"
                                    class="compose-file-input"
                                    id="media"
                                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4"
                                    aria-describedby="media-help"
                                >
                                <small id="media-help">JPG, PNG, WEBP, GIF or MP4 · 2 MB max</small>
                                <div class="compose-description">
                                    <label for="descrip_media">Image description <span>(optional)</span></label>
                                    <textarea
                                        name="descrip_media"
                                        id="descrip_media"
                                        maxlength="235"
                                        rows="2"
                                        placeholder="Describe the image for people who cannot see it"
                                    ></textarea>
                                </div>
                            </div>
                        </details>
                        <span class="compose-character-limit">235 characters max</span>
                        <button type="submit" class="btn btn-primary compose-submit">Publish</button>
                    </div>
                </form>
            </section>
        HTML;
    }

    /**
     * Génère les liens des onglets avec leurs paramètres.
     * @param array<string, string> $tabs onglets à afficher.
     * @param string $active onglet actif.
     * @param string $action nom de l’action.
     * @param array<string, scalar|null> $params paramètres à conserver.
     * @return string Texte ou contenu HTML généré.
     */
    public static function tabs(array $tabs, string $active, string $action, array $params = []): string {
        $html = '';

        foreach ($tabs as $value => $label) {
            $class = (string) $value === $active ? 'active' : '';
            $current = $class === 'active' ? ' aria-current="page"' : '';
            $url = '?' . http_build_query(array_merge($params, [
                'action' => $action,
                'type' => $value,
            ]));
            $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

            $html .= <<<HTML
                <a class="{$class}" href="{$url}"{$current}>{$label}</a>
            HTML;
        }

        return $html;
    }

    /**
     * Génère un message indiquant que l’utilisateur est déjà connecté.
     * @param string $email adresse e-mail de l’utilisateur.
     * @return string Texte ou contenu HTML généré.
     */
    public static function alreadyLogin(string $email): string {
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

        $title = HtmlHelper::title('Already signed in', 'bi-person-plus-fill');
        $feedButton = HtmlHelper::linkButton('My feed', '?action=feed', 'bi-collection-play-fill');
        $signoutButton = HtmlHelper::linkButton('Sign out', '?action=signout', 'bi-box-arrow-right', 'danger');
        $homeButton = HtmlHelper::linkButton('Home', 'index.php', 'bi-house-door-fill', 'secondary');
        return <<<HTML
            {$title}
            <p>You are already signed in with <strong>{$email}</strong>.</p>
            <p>
                {$feedButton}
                {$signoutButton}
                {$homeButton}
            </p>
        HTML;
    }

    /**
     * Choisit une couleur d’avatar à partir de l’identifiant utilisateur.
     * @param int $userId identifiant de l’utilisateur.
     * @return string Texte ou contenu HTML généré.
     */
    public static function profileColor(int $userId): string {
        $colors = [
            'profile-color-blue',
            'profile-color-purple',
            'profile-color-green',
            'profile-color-orange',
            'profile-color-pink',
            'profile-color-teal',
        ];

        return $colors[$userId % count($colors)];
    }

    /**
     * Génère l’avatar d’un utilisateur à partir de ses initiales.
     * @param array{id: int|string, nom?: string, prenom?: string} $user données de l’utilisateur.
     * @param string $class classe CSS de l’avatar.
     * @return string Texte ou contenu HTML généré.
     */
    public static function profileAvatar(array $user, string $class = 'touite-avatar'): string {
        $userId = (int) $user['id'];
        $displayName = trim(($user['nom'] ?? '') . ' ' . ($user['prenom'] ?? ''));
        $initial = htmlspecialchars(mb_substr($displayName, 0, 1), ENT_QUOTES, 'UTF-8');
        $color = self::profileColor($userId);

        return "<div class=\"{$class} {$color}\">{$initial}</div>";
    }

    /**
     * Génère le bouton permettant de suivre ou de ne plus suivre un utilisateur.
     * @param int $userId identifiant de l’utilisateur.
     * @param bool $isSubscribed état actuel de l’abonnement.
     * @param string $type type demandé.
     * @param string $buttonClass classes CSS du bouton.
     * @return string Texte ou contenu HTML généré.
     */
    public static function subscriptionButton(
        int $userId,
        bool $isSubscribed,
        string $type = 'user',
        string $buttonClass = 'btn btn-sm'
    ): string {
        $state = $isSubscribed ? 'unsub' : 'sub';
        $label = $isSubscribed ? 'Following' : 'Follow';
        $variant = $isSubscribed ? 'outline-primary' : 'primary';
        $type = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
        $buttonClass = htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <form method="POST" action="?action=subscribe">
                <input type="hidden" name="state" value="{$state}">
                <input type="hidden" name="type" value="{$type}">
                <input type="hidden" name="id" value="{$userId}">
                <button type="submit" class="{$buttonClass} btn-{$variant}">{$label}</button>
            </form>
        HTML;
    }

    /**
     * Génère une ligne cliquable cohérente pour une liste (tag, profil ou raccourci). $leadingHtml est du HTML de présentation généré par l'application, par exemple un avatar.
     * @param string $url adresse de destination.
     * @param string $leadingHtml contenu HTML affiché au début du lien.
     * @param string $title titre à afficher.
     * @param string $subtitle texte secondaire.
     * @param string $meta informations complémentaires.
     * @return string Texte ou contenu HTML généré.
     */
    public static function listLink(string $url, string $leadingHtml, string $title, string $subtitle = '', string $meta = ''): string {
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
        $meta = htmlspecialchars($meta, ENT_QUOTES, 'UTF-8');
        $subtitleHtml = $subtitle !== ''
            ? "<span class=\"d-block text-secondary small fw-normal\">{$subtitle}</span>"
            : '';
        $metaHtml = $meta !== ''
            ? "<span class=\"d-block text-secondary small fw-normal\">{$meta}</span>"
            : '';

        return <<<HTML
            <a href="{$url}" class="followed-tag">
                {$leadingHtml}
                <span class="followed-tag-label">
                    <span class="d-block">{$title}</span>
                    {$subtitleHtml}
                    {$metaHtml}
                </span>
                <i class="bi bi-chevron-right followed-tag-arrow" aria-hidden="true"></i>
            </a>
        HTML;
    }

    /**
     * Génère le formulaire de recherche des touites.
     * @param string $action nom de l’action.
     * @param string $value valeur du champ.
     * @param string $placeholder texte indicatif.
     * @return string Texte ou contenu HTML généré.
     */
    public static function searchForm(string $action, string $value = '', string $placeholder = 'Search touites'): string {
        $action = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $placeholder = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <form method="post" action="{$action}" class="search-form mb-3" role="search">
                <label for="search" class="visually-hidden">{$placeholder}</label>
                <input
                    type="search"
                    name="search"
                    id="search"
                    value="{$value}"
                    class="form-control search-input"
                    placeholder="{$placeholder}"
                    aria-label="{$placeholder}"
                    required
                >
                <button type="submit" class="search-button" aria-label="Search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                </button>
            </form>
        HTML;
    }

    /**
     * Génère les boutons de réaction et de suppression d’un touite.
     * @param int $touiteId identifiant du touite.
     * @param int $note valeur numérique de la note.
     * @param TypeRender $selector type de rendu demandé.
     * @return string Texte ou contenu HTML généré.
     */
    public static function touiteActions(int $touiteId, int $note, TypeRender $selector): string {
        $noteClass = $note < 0
            ? 'touite-note-negative'
            : ($note > 0 ? 'touite-note-positive' : 'touite-note-neutral');
        $noteHtml = "<span class=\"touite-note {$noteClass}\" aria-label=\"Touite score\">{$note}</span>";

        $r = RepositoryFactory::getReader();
        $isLiked = false;
        $isDisliked = false;

        try {
            $user = AuthnProvider::getSignedInUser();
            $userId = (int) $user['id'];
            $isLiked = $r->isLikedBy($userId, $touiteId);
            $isDisliked = $r->isDislikedBy($userId, $touiteId);
        } catch (AuthnException) {
            // Utilisateur non connecté
        }

        $likeClass = $isLiked ? 'is-liked' : '';
        $likeIcon = $isLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up';

        $dislikeClass = $isDisliked ? 'is-disliked' : '';
        $dislikeIcon = $isDisliked ? 'bi-hand-thumbs-down-fill' : 'bi-hand-thumbs-down';

        if ($selector === TypeRender::COMPACT) {
            $voteClass = $isLiked ? $likeClass : $dislikeClass;
            $voteIcon = $isLiked ? $likeIcon : ($isDisliked ? $dislikeIcon : 'bi-heart');
            $voteLabel = $isLiked ? 'Your vote: like' : ($isDisliked ? 'Your vote: dislike' : 'No vote');

            return <<<HTML
                <span class="touite-vote-indicator {$voteClass}" role="img" aria-label="{$voteLabel}"><i class="bi {$voteIcon}" aria-hidden="true"></i></span>
                {$noteHtml}
            HTML;
        }

        $likeType = $isLiked ? 'annuler' : 'like';
        $dislikeType = $isDisliked ? 'annuler' : 'dislike';
        $likePressed = $isLiked ? 'true' : 'false';
        $dislikePressed = $isDisliked ? 'true' : 'false';
        $returnQuery = htmlspecialchars(http_build_query($_GET ?: ['action' => 'feed']), ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <form method="POST" action="?action=like">
                <input type="hidden" name="id" value="{$touiteId}">
                <input type="hidden" name="type" value="{$likeType}">
                <input type="hidden" name="return_query" value="{$returnQuery}">
                <button type="submit" class="touite-like {$likeClass}" aria-label="Like" aria-pressed="{$likePressed}"><i class="bi {$likeIcon}" aria-hidden="true"></i></button>
            </form>
            {$noteHtml}
            <form method="POST" action="?action=like">
                <input type="hidden" name="id" value="{$touiteId}">
                <input type="hidden" name="type" value="{$dislikeType}">
                <input type="hidden" name="return_query" value="{$returnQuery}">
                <button type="submit" class="touite-dislike {$dislikeClass}" aria-label="Dislike" aria-pressed="{$dislikePressed}"><i class="bi {$dislikeIcon}" aria-hidden="true"></i></button>
            </form>
        HTML;
    }

    /**
     * Génère un bouton HTML avec son libellé et son icône.
     * @param string $label libellé à afficher.
     * @param string $icon icône à afficher.
     * @param string $variant variante visuelle.
     * @param string $type type demandé.
     * @return string Texte ou contenu HTML généré.
     */
    public static function button(string $label, string $icon = '', string $variant = 'primary', string $type = 'submit'): string {
        $iconHtml = $icon !== '' ? "<i class=\"bi {$icon}\" aria-hidden=\"true\"></i>" : '';

        return "<button type=\"{$type}\" class=\"btn btn-{$variant}\">{$iconHtml}{$label}</button>";
    }

    /**
     * Génère un lien présenté comme un bouton.
     * @param string $label libellé à afficher.
     * @param string $url adresse de destination.
     * @param string $icon icône à afficher.
     * @param string $variant variante visuelle.
     * @return string Texte ou contenu HTML généré.
     */
    public static function linkButton(string $label, string $url, string $icon = '', string $variant = 'primary'): string {
        $iconHtml = $icon !== '' ? "<i class=\"bi {$icon}\" aria-hidden=\"true\"></i>" : '';

        return "<a class=\"btn btn-{$variant}\" href=\"{$url}\">{$iconHtml}{$label}</a>";
    }

    /**
     * Génère un lien de retour.
     * @param string $url adresse de destination.
     * @param string $label libellé à afficher.
     * @return string Texte ou contenu HTML généré.
     */
    public static function backLink(string $url = '?action=feed', string $label = 'Back to feed'): string {
        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <a class="back-link" href="{$url}" aria-label="{$label}">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </a>
        HTML;
    }

    /**
     * Génère l’en-tête d’une page de détail avec son lien de retour.
     * @param string $title titre à afficher.
     * @param string $backUrl adresse du lien de retour.
     * @param string $trailingContent contenu affiché après le titre.
     * @param string $subtitle texte secondaire.
     * @return string Texte ou contenu HTML généré.
     */
    public static function detailHeader(
        string $title,
        string $backUrl = '?action=feed',
        string $trailingContent = '',
        string $subtitle = ''
    ): string {
        $backLink = self::backLink($backUrl);
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
        $titleContent = $subtitle === ''
            ? "<h1>{$title}</h1>"
            : "<div><h1 class=\"mb-0 fw-bold\">{$title}</h1><span class=\"text-secondary small\">{$subtitle}</span></div>";

        return <<<HTML
            <header class="feed-header detail-header">
                {$backLink}
                {$titleContent}
                {$trailingContent}
            </header>
        HTML;
    }

    /**
     * Génère les liens de navigation entre les pages.
     * @param string $ariaLabel libellé accessible.
     * @param string $action nom de l’action.
     * @param Pagination $pagination paramètres de pagination.
     * @return string Texte ou contenu HTML généré.
     */
    public static function pagination(string $ariaLabel, string $action, Pagination $pagination): string {
        $previous = $pagination->previous();
        $next = $pagination->next();
        $page = $pagination->getPage();
        $previousAttributes = ($page === 1) ? ' aria-disabled="true" tabindex="-1"' : '';
        $nextAttribute = $pagination->getIsLastPage() ? ' aria-disabled="true" tabindex="-1"' : '';

        return <<<HTML
            <nav class="feed-pagination" aria-label="{$ariaLabel}">
                <a class="btn btn-outline-secondary"{$previousAttributes} href="?action={$action}&page={$previous}">
                    <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    Previous page
                </a>
                <span>Page {$page}</span>
                <a class="btn btn-outline-secondary"{$nextAttribute} href="?action={$action}&page={$next}">
                    Next page
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
            </nav>
        HTML;
    }

    /**
     * Génère un titre de page cohérent avec le design de Touiteur.
     * @param string $title titre à afficher.
     * @param string $icon icône à afficher.
     * @param string $color couleur à utiliser.
     * @return string Texte ou contenu HTML généré.
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
     * @param string $type type demandé.
     * @param string|list<string> $content contenu du touite.
     * @return string Texte ou contenu HTML généré.
     */
    public static function alert(string $type = 'info', string|array $content = ''): string {
        $icon = match ($type) {
            'success' => 'bi-check-circle-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'danger' => 'bi-exclamation-octagon-fill',
            'info' => 'bi-info-circle-fill',
            default => 'bi-bell-fill',
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
     * Génère une page présentant une erreur et un lien de retour.
     * @param string $title titre à afficher.
     * @param string|list<string> $message message à afficher.
     * @param string $backUrl adresse du lien de retour.
     * @param string $backLabel libellé du lien de retour.
     * @param string $type type demandé.
     * @return string Texte ou contenu HTML généré.
     */
    public static function errorPage(string $title = 'Error', string|array $message = 'An unexpected error occurred.', string $backUrl = 'index.php', string $backLabel = 'Back to home', string $type = 'danger'): string {
        $titleColor = match ($type) {
            'success' => 'text-success',
            'warning' => 'text-warning',
            'danger' => 'text-danger',
            default => 'text-primary',
        };

        return self::statusScreen(
            titleIcon: 'bi-exclamation-triangle-fill',
            title: $title,
            titleColorClass: $titleColor,
            alertType: $type,
            message: $message,
            backUrl: $backUrl,
            backLabel: $backLabel,
            backIcon: 'bi-arrow-left',
            backIconClass: 'me-2'
        );
    }

    /**
     * Génère un message demandant à l’utilisateur de se connecter.
     * @param string $message message à afficher.
     * @param string $loginUrl adresse de connexion.
     * @param string $loginLabel libellé du lien de connexion.
     * @param string $backUrl adresse du lien de retour.
     * @param string $backLabel libellé du lien de retour.
     * @return string Texte ou contenu HTML généré.
     */
    public static function authRequired(string $message = 'You must be signed in to access this page.', string $loginUrl = '?action=signin', string $loginLabel = 'Sign in', string $backUrl = 'index.php', string $backLabel = 'Back to home'): string {
        $alert = self::alert('warning', $message);
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/shield-lock-fill/
        $titleHtml = self::title('Access denied', 'bi-shield-lock-fill', 'text-danger');

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
     * Génère un message indiquant que l’accès est interdit.
     * @param string $message message à afficher.
     * @param string $backUrl adresse du lien de retour.
     * @param string $backLabel libellé du lien de retour.
     * @return string Texte ou contenu HTML généré.
     */
    public static function forbidden(string $message = 'You are not authorized to perform this action.', string $backUrl = '?action=feed', string $backLabel = 'Back to my feed'): string {
        return self::statusScreen(
            titleIcon: 'bi-shield-lock-fill',
            title: 'Access denied',
            titleColorClass: 'text-danger',
            alertType: 'danger',
            message: $message,
            backUrl: $backUrl,
            backLabel: $backLabel,
            backIcon: 'bi-arrow-left',
            backIconClass: 'me-2'
        );
    }

    /**
     * Génère un message indiquant que la ressource est introuvable.
     * @param string $item nom de la ressource.
     * @param string $message message à afficher.
     * @param string $backUrl adresse du lien de retour.
     * @param string $backLabel libellé du lien de retour.
     * @return string Texte ou contenu HTML généré.
     */
    public static function notFound(string $item = 'Resource', string $message = 'The requested item does not exist or could not be found.', string $backUrl = '?action=feed', string $backLabel = 'Back to my feed'): string {
        return self::statusScreen(
            titleIcon: 'bi-exclamation-triangle-fill',
            title: "{$item} not found",
            titleColorClass: 'text-danger',
            alertType: 'danger',
            message: $message,
            backUrl: $backUrl,
            backLabel: $backLabel,
            backIcon: 'bi-arrow-left',
            backIconClass: 'me-2'
        );
    }

    /**
     * Génère une page présentant les erreurs de validation d’un formulaire.
     * @param string|list<string> $errors erreurs à afficher.
     * @param string $backUrl adresse du lien de retour.
     * @param string $backLabel libellé du lien de retour.
     * @param string $title titre à afficher.
     * @return string Texte ou contenu HTML généré.
     */
    public static function formError(string|array $errors = 'An error occurred while validating the form.', string $backUrl = 'index.php', string $backLabel = 'Back to form', string $title = 'Form error'): string {
        return self::statusScreen(
            titleIcon: 'bi-exclamation-triangle-fill',
            title: $title,
            titleColorClass: 'text-danger',
            alertType: 'danger',
            message: $errors,
            backUrl: $backUrl,
            backLabel: $backLabel,
            backIcon: 'bi-arrow-counterclockwise',
            backIconClass: 'me-1'
        );
    }

    /**
     * Génère une page de confirmation avec les liens proposés.
     * @param string $title titre à afficher.
     * @param string $message message à afficher.
     * @param string $backUrl adresse du lien de retour.
     * @param string $backLabel libellé du lien de retour.
     * @param string $secondaryUrl adresse du lien secondaire.
     * @param string $secondaryLabel libellé du lien secondaire.
     * @return string Texte ou contenu HTML généré.
     */
    public static function successPage(
        string $title = 'Operation successful',
        string $message = 'The operation completed successfully.',
        string $backUrl = '?action=feed',
        string $backLabel = 'Back to my feed',
        string $secondaryUrl = '',
        string $secondaryLabel = ''
    ): string {
        $alert = self::alert('success', $message);
        // Icône Bootstrap - https://icons.getbootstrap.com/icons/check-circle-fill/
        $titleHtml = self::title($title, 'bi-check-circle-fill', 'text-success');
        $secondaryAction = '';
        if ($secondaryUrl !== '' && $secondaryLabel !== '') {
            $secondaryUrl = htmlspecialchars($secondaryUrl, ENT_QUOTES, 'UTF-8');
            $secondaryLabel = htmlspecialchars($secondaryLabel, ENT_QUOTES, 'UTF-8');
            $secondaryAction = <<<HTML
                <a class="btn btn-secondary ms-2 d-inline-flex align-items-center" href="{$secondaryUrl}">
                    {$secondaryLabel}
                </a>
            HTML;
        }

        return <<<HTML
            {$titleHtml}
            {$alert}
            <p class="mt-3">
                <a class="btn btn-primary d-inline-flex align-items-center" href="{$backUrl}">
                    <!-- Icône Bootstrap - https://icons.getbootstrap.com/icons/arrow-left/ -->
                    <i class="bi bi-arrow-left me-2"></i>
                    {$backLabel}
                </a>
                {$secondaryAction}
            </p>
        HTML;
    }

    /**
     * Génère une page de statut avec un message et un lien de retour.
     * @param string $titleIcon icône du titre.
     * @param string $title titre à afficher.
     * @param string $titleColorClass classe CSS de la couleur du titre.
     * @param string $alertType type d’alerte à afficher.
     * @param string|list<string> $message message à afficher.
     * @param string $backUrl adresse du lien de retour.
     * @param string $backLabel libellé du lien de retour.
     * @param string $backIcon icône du lien de retour.
     * @param string $backIconClass classe CSS de l’icône de retour.
     * @return string Texte ou contenu HTML généré.
     */
    private static function statusScreen(
        string $titleIcon,
        string $title,
        string $titleColorClass,
        string $alertType,
        string|array $message,
        string $backUrl,
        string $backLabel,
        string $backIcon,
        string $backIconClass
    ): string {
        $alertHtml = self::alert($alertType, $message);
        $titleHtml = self::title($title, $titleIcon, $titleColorClass);

        return <<<HTML
            {$titleHtml}
            {$alertHtml}
            <p class="mt-3">
                <a class="btn btn-secondary d-inline-flex align-items-center" href="{$backUrl}">
                    <i class="bi {$backIcon} {$backIconClass}"></i>
                    {$backLabel}
                </a>
            </p>
        HTML;
    }
}
