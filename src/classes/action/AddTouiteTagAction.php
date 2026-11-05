<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\repository\RepositoryFactory;
use \iutnc\touiteur\render\RendererFactory;
use iutnc\touiteur\render\HtmlHelper;
use Override;


class AddTouiteTagAction extends Action
{


    protected bool $requireAuth = true;

    #[Override]
    public function get(): string
    {
        
        $page = <<<HTML
            <form method="post" action="?action=AddTouiteTagAction">
        HTML;

        


        foreach ($_SESSION['tag'] as $nomtag => $tag) {
            $libelle = $tag->getLibelle();
            $form = <<<HTML
            <label for="tag-gestion"> $libelle </label>
            <textarea
                        name="$nomtag"
                        
                        maxlength="235"
                        rows="4"
                        placeholder="description tag"
                        required
                    ></textarea>

                
            HTML;

            $page .= $form;
        }

        $page .= <<<HTML
           <button type="submit"> valider </button> </form>
        HTML;
        
        return $page;
    }

    #[Override]
    public function post(): string
    {
        




    }
}
