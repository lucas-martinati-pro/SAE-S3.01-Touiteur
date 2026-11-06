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
            <form method="post" action="?action=addTouiteTag">
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
        $w = RepositoryFactory::getWriter();
        $r = RepositoryFactory::getReader();
        
        foreach ($_SESSION['tag'] as $nomtag => $tag) {
            $txt = $_POST[$nomtag];
            if (strlen($txt) > 235 || strlen($txt) === 0) {
                $message = strlen($txt) === 0
                ? 'Le contenu du touite ne peut pas être vide.'
                : 'Le contenu du touite doit comporter entre 1 et 235 caractères.';

               return HtmlHelper::formError(
                    errors: $message ,
                    backUrl: '?action=addTouite',
                    backLabel: 'Modifier le touite',
                    title: 'Impossible de publier le touite'
                );
            } 

            $desc = filter_var($txt, FILTER_SANITIZE_SPECIAL_CHARS);

            $tag->setDescription($desc);

            if (!($r->tagExist($tag->getLibelle))) {
                $w->addTag($tag);
            }

            
        }
        unset($_SESSION['tag']);
            
       return "";
    }
}
