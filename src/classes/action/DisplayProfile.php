<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\enum\TypeRender;
use iutnc\touiteur\render\HtmlHelper;
use Override;

/**
 * Action par défaut affichant la page d'accueil de l'application Touiteur.
 */
class DisplayProfile extends Action {
    #[Override]
    public function get(): string {
        $r = RepositoryFactory::getReader();

        $id = (int) ($_GET['id'] ?? 0);
        $feed = $r->findAllTouitesByUserId($id);
        
        $details = $r->getUserById($id);
        $completeName = $details['nom'] . " " . $details['prenom'];

        $page = <<< HTML
            <header class="feed-header detail-header">
                <a class="back-link" href="?action=displayFeed" aria-label="Retour au feed">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h1>Profil</h1>
            </header>
            <section class="profile-header">
                <div class="profile-cover"></div>
                <div class="profile-main">
                    <div class="profile-avatar">T</div>
                    <a class="btn btn-outline-primary profile-button" href="?action=displayFeed">Retour au feed</a>
                    <h2>@{$completeName}</h2>
                    <p class="profile-handle">{$completeName}</p>
                    <p class="profile-bio">Découvrez les touites publiés par cet utilisateur.</p>
                    <div class="profile-meta">
                        <span><i class="bi bi-calendar3"></i> Membre de Touiteur</span>
                    </div>
                </div>
                <nav class="profile-tabs" aria-label="Navigation du profil">
                    <a class="active" href="?action=displayProfile&id={$id}">Touites</a>
                </nav>
            </section>
        HTML;

        if ($feed === null) {
            return $page . HtmlHelper::alert('info', 'Cet utilisateur n’a encore publié aucun touite.');
        }

        return $page . RendererFactory::getRenderer($feed)->render(TypeRender::COMPACT);
    }
}