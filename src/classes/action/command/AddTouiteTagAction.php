<?php

namespace iutnc\touiteur\action\command;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\action\Action;
use Override;

class AddTouiteTagAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function get(): string {
        $page = <<<HTML
            <form method="post" action="?action=addTouiteTag">
        HTML;

        foreach ($_SESSION['tags'] as $nomtag => $tag) { //nomtag = minecraft tag = Tag(null, minecraft, null)
            $libelle = $tag->getLibelle();

            $page .= <<<HTML
            <label for="tag-gestion">$libelle</label>
            <textarea
                    name="$nomtag"
                    maxlength="235"
                    rows="4"
                    placeholder="description tag"
                    required
                ></textarea>
            HTML;
        }

        $page .= <<<HTML
           <button type="submit"> valider </button> </form>
        HTML;

        return $page;
    }

    #[Override]
    public function post(): string {
        if (isset($_SESSION['tags']) && isset($_SESSION['touite'])) {
            foreach ($_SESSION['tags'] as $nomtag => $tag) {
                $txt = $_POST[$nomtag];
                if (strlen($txt) > 235 || strlen($txt) === 0) {

                    $message = strlen($txt) === 0
                        ? 'Le contenu du touite ne peut pas être vide.'
                        : 'Le contenu du touite doit comporter entre 1 et 235 caractères.';

                    unset($_SESSION['tag']);
                    unset($_SESSION['touite']);

                    return HtmlHelper::formError(
                        errors: $message,
                        backUrl: '?action=addTouite',
                        backLabel: 'Modifier le touite',
                        title: 'Impossible de publier le touite'
                    );

                }

                $tag->setDescription(filter_var($txt, FILTER_SANITIZE_SPECIAL_CHARS));
            }

            $w = RepositoryFactory::getWriter();
            $touite = $w->addTouite($_SESSION['touite'], $this->user['id']);

            foreach ($_SESSION['tags'] as $nomtag => $tag) {
                $w->addTag($touite->getId(), $tag);
            }

            unset($_SESSION['tags']);
            unset($_SESSION['touite']);
        }

        header("Location: ?action=touite&id=" . $_SESSION['touite']->getId());
        exit;
    }
}