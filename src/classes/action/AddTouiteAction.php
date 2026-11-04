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

                <div class="mb-3">
                    <label for="img" class="form-label">Image <span class="text-muted">(optionnelle)</span></label>
                    <input
                        type="file"
                        name="img"
                        class="form-control"
                        id="img"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                    >
                    <div class="form-text">Formats acceptés : JPG, PNG, WEBP et GIF.</div>
                </div>

                <div class="mb-3">
                    <label for="descrip_img" class="form-label">Description de l'image <span class="text-muted">(optionnelle)</span></label>
                    <input
                        type="text"
                        name="descrip_img"
                        class="form-control"
                        id="descrip_img"
                        maxlength="235"
                        placeholder="Décrivez votre image"
                    >
                </div>

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
        $desc_img = filter_var($_POST["descrip_img"], FILTER_SANITIZE_SPECIAL_CHARS);

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

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
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
