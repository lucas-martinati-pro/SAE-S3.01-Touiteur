<?php
namespace iutnc\touiteur\action;

use \iutnc\touiteur\touite\Touite;
use \iutnc\touiteur\render\RendererFactory;
use Override;
class AddTouiteAction extends Action
{
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string
    {

        $page = <<<HTML
         <form method="post" action="?action=add-touite" enctype="multipart/form-data">
                    <label> contenu : </label>
                    <input type="text" name="contenu" required>

                    <label> image : </label>
                    <input type="file" name="img">

                    <label> description image: </label>
                    <input type="text" name="descrip_img">

                    <label> tag : </label>
                    <input type="text" name="tag" placeholder = "#nom_tag">


                    <button type="submit" name="publier"> publier </button>
        </form>
        HTML;

        return $page;
    }

    #[Override]
    public function post(): string
    {
        //values input
        $contenu = filter_var($_POST["contenu"], FILTER_SANITIZE_SPECIAL_CHARS);
        $desc_img = filter_var($_POST["descrip_img"], FILTER_SANITIZE_SPECIAL_CHARS);
        $tag = filter_var($_POST["tag"], FILTER_SANITIZE_SPECIAL_CHARS);

        //capture de la date
        if (isset($_POST['mon_bouton'])) {
            // Définit le fuseau horaire 
            date_default_timezone_set('Europe/Paris');

            // Format AAAA-MM-JJ HH:MM:SS
            $dateClic = date('Y-m-d H:i:s');
        }

        //capture de l'image

        $img = "";
        $img_name = basename($_FILES['img']['name']); // on capture le nom du fichier en s'assurant d'avoir que le nom du fichier
        $type = mime_content_type($_FILES['img']['tmp_name']); // contient le type du fichier ex : image/png
        $destination = "";
        $img = $_FILES["img"]["tmp_name"]; // chemin temporaire
        $filename = uniqid(); // nom aléatoire

        // on regarde le type du fichier pour avoir la bonne destination final
        if ($type === "image/png") {

            $destination = "image/touite_img" . $filename . ".png";


        } else if ($type === "image/jpeg") {

            $destination = "image/touite_img" . $filename . ".png";



        } else {
            return "<p>" . $_FILES["img"]["tmp_name"] . "n'est pas un fichier jpeg ou png</p>";
        }

        if (move_uploaded_file($img, $destination)) {

            print "<p> téléchargement avec succès </p>";

        } else {
            return "<p> téléchargement non valide</p>";
        }

        // affichage
        $touite = new Touite($contenu, $dateClic, $destination, $desc_img);
        $render = RendererFactory::getRenderer($touite);
        
        $html = $render->render();


        $page =<<<HTML
            $html
            <a href="?action=displayFeed"></a>

        HTML;

        return $page;

    }

}
