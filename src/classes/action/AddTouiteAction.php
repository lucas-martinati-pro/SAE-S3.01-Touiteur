<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use \iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\render\HtmlHelper;
use Override;

class AddTouiteAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        $title = HtmlHelper::title("Publier un touite", "bi-pencil-square");
        $publishButton = HtmlHelper::button('Publier', 'bi-send-fill');
        $cancelButton = HtmlHelper::linkButton('Annuler', '?action=displayFeed', '', 'outline-secondary');

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

                <div class="mb-3 image-upload">
                    <label for="img" class="form-label">Image ou vidéo <span class="text-muted">(optionnelle)</span></label>
                    <input
                        type="file"
                        name="img"
                        class="form-control image-input"
                        id="img"
                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4"
                    >
                    <div class="image-upload-help">
                        <i class="bi bi-image" aria-hidden="true"></i>
                        <span>Ajoutez une image ou une vidéo à votre touite</span>
                        <small>JPG, PNG, WEBP, GIF ou MP4 · 2 Mo maximum</small>
                    </div>
                </div>

                <details class="image-description mb-3">
                    <summary>
                        <i class="bi bi-card-text" aria-hidden="true"></i>
                        Ajouter une description au média
                    </summary>
                    <label for="descrip_img" class="form-label">Description <span class="text-muted">(optionnelle)</span></label>
                    <input
                        type="text"
                        name="descrip_img"
                        class="form-control"
                        id="descrip_img"
                        maxlength="235"
                        placeholder="Décrivez votre image ou votre vidéo"
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
        //values input
        $contenu = filter_var($_POST["contenu"], FILTER_SANITIZE_SPECIAL_CHARS);
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
        $desc_img = filter_var($_POST["descrip_img"], FILTER_SANITIZE_SPECIAL_CHARS);

        if (
            isset($_FILES['img']) &&
            $_FILES['img']['error'] === UPLOAD_ERR_OK &&
            $_FILES['img']['size'] > 2 * 1024 * 1024
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
        $date = date('Y-m-d H:i:s');

        $imageName = $this->saveImage($_FILES);

        // ajout à la bd
        $w = RepositoryFactory::getWriter();
        $touite = $w->addTouite($contenu, $date, $imageName, $desc_img, $this->user["id"]);

        header("Location: ?action=displayTouite&id=" . $touite->getId());
        exit;
    }

    private function saveImage(array $imgFile): ?String {
        if (isset($imgFile['img']) && $imgFile['img']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($imgFile['img']['name'], PATHINFO_EXTENSION));

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4'])) {
                $imgDir = 'image' . DIRECTORY_SEPARATOR . 'touite_img' . DIRECTORY_SEPARATOR;
                $imageName = $imgDir . uniqid('touite_img_', true) . bin2hex(random_bytes(4)) . '.' . $extension;

                if (move_uploaded_file($imgFile['img']['tmp_name'], $imageName)) {
                    return $imageName;
                }
            }
        }
        return null;
    }
}
