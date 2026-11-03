<?php
namespace iutnc\touiteur\action;
use Override;
class AddTouiteAction extends Action
{
    protected bool $requireAuth = false;

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
                    <input type="text" name="dscrip_img">

                    <label> tag : </label>
                    <input type="text" name="tag" placeholder = "#nom_tag">


                    <button type="submit"> publier </button>
        </form>
        HTML;

        return $page;
    }

    #[Override]
    public function post(): string
    {


    }

}