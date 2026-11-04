<?php
namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use \iutnc\touiteur\render\RendererFactory;
use Override;

class AddTouiteAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        $page = <<<HTML
         <form method="post" action="?action=addTouite" enctype="multipart/form-data">
            <label> contenu : </label>
            <input type="text" name="contenu" maxlength="235" required>

            <label> image : </label>
            <input type="file" name="img">

            <label> description image: </label>
            <input type="text" name="descrip_img" maxlength="235">

            <button type="submit">publier</button>
        </form>
        HTML;

        return $page;
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

        // affichage
        $render = RendererFactory::getRenderer($touite);
        $html = $render->render();

        return <<<HTML
            $html
            <a href="?action=displayFeed">Retourner vers votre feeds</a>
        HTML;
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
