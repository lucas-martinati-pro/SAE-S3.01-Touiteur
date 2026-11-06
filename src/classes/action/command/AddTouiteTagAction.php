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
        $tags = $_SESSION['tags'] ?? [];
        if (!is_array($tags) || $tags === [] || !isset($_SESSION['touite'])) {
            return HtmlHelper::errorPage(
                title: 'Aucun tag à décrire',
                message: 'Publie d’abord un touite contenant un nouveau tag.',
                backUrl: '?action=addTouite',
                backLabel: 'Créer un touite'
            );
        }

        $page = HtmlHelper::title('Vérifier les tags', 'bi-hash') . <<<HTML
            <p class="text-secondary">Les tags existants gardent leur description. Ajoute une description pour chaque nouveau tag.</p>
            <form method="post" action="?action=addTouiteTag" class="vstack gap-3">
        HTML;

        $index = 0;
        foreach ($tags as $tagName => $tag) {
            $tagLabel = htmlspecialchars($tag->getLibelle(), ENT_QUOTES, 'UTF-8');
            $fieldName = htmlspecialchars((string) $tagName, ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($tag->getDescription() ?? '', ENT_QUOTES, 'UTF-8');

            if ($tag->getId() !== null) {
                $page .= <<<HTML
                    <div class="p-3 border rounded">
                        <div class="fw-semibold">#{$tagLabel}</div>
                        <p class="mb-0 text-secondary">{$description}</p>
                    </div>
                HTML;
                continue;
            }

            $page .= <<<HTML
                <div>
                    <label for="{$fieldName}" class="form-label fw-semibold">#{$tagLabel}</label>
                    <textarea
                        class="form-control"
                        id="{$fieldName}"
                        name="{$fieldName}"
                        maxlength="235"
                        rows="3"
                        placeholder="Décris ce tag en quelques mots"
                        required
                    >{$description}</textarea>
                    <div class="form-text">235 caractères maximum.</div>
                </div>
            HTML;
        }

        $submitButton = HtmlHelper::button('Publier le touite', 'bi-send-fill');
        $cancelButton = HtmlHelper::linkButton('Annuler', '?action=addTouite', '', 'outline-secondary');
        $page .= <<<HTML
                <div class="d-flex gap-2">
                    {$submitButton}
                    {$cancelButton}
                </div>
            </form>
        HTML;

        return $page;
    }

    #[Override]
    public function post(): string {
        $tags = $_SESSION['tags'] ?? null;
        $touite = $_SESSION['touite'] ?? null;

        if (!is_array($tags) || $tags === [] || $touite === null) {
            return HtmlHelper::errorPage(
                title: 'Formulaire invalide',
                message: 'Les tags ou le touite à publier sont introuvables. Recommence la publication.',
                backUrl: '?action=addTouite',
                backLabel: 'Créer un touite'
            );
        }

        foreach ($tags as $tagName => $tag) {
            if ($tag->getId() === null) {
                $postedDescription = $_POST[$tagName] ?? null;
                $description = is_string($postedDescription) ? trim($postedDescription) : '';
                $length = mb_strlen($description);

                if ($length === 0 || $length > 235) {
                    $message = $length === 0
                        ? 'La description du tag ne peut pas être vide.'
                        : 'La description du tag doit comporter au maximum 235 caractères.';

                    return HtmlHelper::formError(
                        errors: $message,
                        backUrl: '?action=addTouiteTag',
                        backLabel: 'Reprendre les descriptions',
                        title: 'Impossible de publier le touite'
                    );
                }

                $tags[$tagName]->setDescription($description);
            }
        }

        $w = RepositoryFactory::getWriter();
        $touite = $w->addTouite($touite, $this->user['id']);

        foreach ($tags as $tag) {
            $w->addTouiteTag($tag, $touite->getId());
        }

        unset($_SESSION['tags'], $_SESSION['touite']);

        header('Location: ?action=touite&id=' . $touite->getId());
        exit;
    }
}
