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
        $destination = "";

        //capture de la date
        date_default_timezone_set('Europe/Paris');
        // Format AAAA-MM-JJ HH:MM:SS
        $date = date('Y-m-d H:i:s');

        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            //capture de l'image

            $img = "";
            $img_name = basename($_FILES['img']['name']); // le nom de l'image
            $type = mime_content_type($_FILES['img']['tmp_name']); // contient le type du fichier ex : image/png
            
            $img = $_FILES["img"]["tmp_name"];// chemin temporaire

            $filename = uniqid(); // nom aléatoire

            // on regarde le type du fichier pour avoir la bonne destination final
            if ($type === "image/png") {
                $destination = "image/touite_img/" . $filename . ".png";
            } else if ($type === "image/jpeg") {
                $destination = "image/touite_img/" . $filename . ".jpg";
            } else {
                return "<p>{$_FILES["img"]["tmp_name"]} n'est pas un fichier jpeg ou png</p>";
            }

            if (!move_uploaded_file($img, $destination)) {
                return "<p>téléchargement non valide</p>";
            }
        }

        // ajout à la bd
        $w= RepositoryFactory::getWriter();
        $touite =  $w->addTouite($contenu, $date, $destination, $desc_img, $this->user["id"]);

        // affichage
        $render = RendererFactory::getRenderer($touite);
        $html = $render->render();

        return <<<HTML
            $html
            <a href="?action=displayFeed">Retourner vers votre feeds</a>
        HTML;
    }
}
