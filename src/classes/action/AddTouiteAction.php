<?php
namespace iutnc\touiteur\action;
use Override;
class AddTouiteAction extends Action
{
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string
    {

        $page = <<<HTML
         <form method="post" action="?action=add-Touite">
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
        $tag = filter_var($_POST["descrip_img"], FILTER_SANITIZE_SPECIAL_CHARS);

        //capture de la date
        if (isset($_POST['mon_bouton'])) {
            // Définit le fuseau horaire 
            date_default_timezone_set('Europe/Paris');

            // Format AAAA-MM-JJ HH:MM:SS
            $dateClic = date('Y-m-d H:i:s');
        }

        




    }

}
