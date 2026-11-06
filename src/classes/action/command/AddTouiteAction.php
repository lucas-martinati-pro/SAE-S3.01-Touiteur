<?php
namespace iutnc\touiteur\action\command;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\tag\Tag;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\action\Action;
use Override;

class AddTouiteAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        $title = HtmlHelper::title("Publier un touite", "bi-pencil-square");
        $publishButton = HtmlHelper::button('Publier', 'bi-send-fill');
        $cancelButton = HtmlHelper::linkButton('Annuler', '?action=feed', '', 'outline-secondary');

        return <<<HTML
            {$title}
            <form method="post" action="?action=addTouite" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="contenu" class="form-label">Contenu</label>
                    <textarea
                        name="contenu"
                        class="form-control"
                        id="contenu"
                        maxlength="235"
                        rows="4"
                        placeholder="Quoi de neuf ?"
                        required
                    ></textarea>
                    <div class="form-text">235 caractères maximum.</div>
                </div>

                <div class="mb-3 media-upload">
                    <label for="media" class="form-label">Média <span class="text-muted">(optionnel)</span></label>
                    <input
                        type="file"
                        name="media"
                        class="form-control media-input"
                        id="media"
                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4"
                    >
                    <div class="media-upload-help">
                        <i class="bi bi-image" aria-hidden="true"></i>
                        <span>Ajoutez un média à votre touite</span>
                        <small>JPG, PNG, WEBP, GIF ou MP4 · 2 Mo maximum</small>
                    </div>
                </div>

                <details class="media-description mb-3">
                    <summary>
                        <i class="bi bi-card-text" aria-hidden="true"></i>
                        Ajouter une description au média
                    </summary>
                    <label for="descrip_media" class="form-label">Description <span class="text-muted">(optionnelle)</span></label>
                    <input
                        type="text"
                        name="descrip_media"
                        class="form-control"
                        id="descrip_media"
                        maxlength="235"
                        placeholder="Décrivez votre média"
                    >
                </details>

                <div class="d-flex gap-2">
                    {$publishButton}
                    {$cancelButton}
                </div>
            </form>
        HTML;
    }

    #[Override]
    public function post(): string {
        $contenu = trim($_POST["contenu"] ?? '');
        $longueur = mb_strlen($contenu);
        if ($longueur === 0 || $longueur > 235) {
            $message = $longueur === 0
                ? 'Le contenu du touite ne peut pas être vide.'
                : 'Le contenu du touite doit comporter entre 1 et 235 caractères.';

            return HtmlHelper::formError(
                errors: $message,
                backUrl: '?action=addTouite',
                backLabel: 'Modifier le touite',
                title: 'Impossible de publier le touite'
            );
        }
        $mediaDesc = trim($_POST["descrip_media"] ?? '');

        if (
            isset($_FILES['media']) &&
            $_FILES['media']['error'] === UPLOAD_ERR_OK &&
            $_FILES['media']['size'] > 2 * 1024 * 1024
        ) {
            return HtmlHelper::formError(
                errors: 'Le fichier ne doit pas dépasser 2 Mo.',
                backUrl: '?action=addTouite',
                backLabel: 'Modifier le touite',
                title: 'Impossible de publier le touite'
            );
        }

        //capture de la date
        date_default_timezone_set('Europe/Paris');
        // Format AAAA-MM-JJ HH:MM:SS
        $postedAt = date('Y-m-d H:i:s');

        $mediaName = $this->saveMedia($_FILES);

        $touite = new Touite($contenu, $postedAt, 0, $mediaName, $mediaDesc);

        //on evalue s'il y un tag pour savoir où rediriger
        // on regarde si un # est présent
        if (str_contains($contenu, '#')) {
            $r = RepositoryFactory::getReader();
            $mots = explode(' ', $contenu); // on divise la chaîne

            foreach ($mots as $mot) {
                if (str_starts_with($mot, '#')) {
                    $mot = substr($mot,1);

                    if (!($r->tagExist($mot))) {
                        $_SESSION["tags"][$mot]=  new Tag($mot);
                    }
                }
            }

            if (isset ($_SESSION["tags"])) {
                $_SESSION['touite'] = $touite;
                header("Location: ?action=addTouiteTag");
                exit;
            }
        }

        $w = RepositoryFactory::getWriter();
        $touite = $w->addTouite($touite, $this->user["id"]);
        header("Location: ?action=touite&id=" . $touite->getId());
        exit;
    }

    private function saveMedia(array $mediaFile): ?String {
        if (isset($mediaFile['media']) && $mediaFile['media']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($mediaFile['media']['name'], PATHINFO_EXTENSION));

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4'])) {
                $mediaDir = 'image' . DIRECTORY_SEPARATOR . 'touite_media' . DIRECTORY_SEPARATOR;
                $mediaName = $mediaDir . uniqid('touite_media_', true) . bin2hex(random_bytes(4)) . '.' . $extension;

                if (move_uploaded_file($mediaFile['media']['tmp_name'], $mediaName)) {
                    return $mediaName;
                }
            }
        }
        return null;
    }
}
