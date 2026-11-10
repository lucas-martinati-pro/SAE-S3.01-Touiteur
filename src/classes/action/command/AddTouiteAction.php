<?php
namespace iutnc\touiteur\action\command;

use iutnc\touiteur\repository\RepositoryFactory;
use iutnc\touiteur\render\HtmlHelper;
use iutnc\touiteur\tag\Tag;
use iutnc\touiteur\touite\Touite;
use iutnc\touiteur\action\Action;
use Override;

class AddTouiteAction extends Action {
    protected bool $requireAuth = true;

    #[Override]
    public function post(): string {
        unset($_SESSION['tags'], $_SESSION['touite']);
        $content = trim($_POST['contenu'] ?? '');
        $length = mb_strlen($content);
        if ($length === 0 || $length > 235) {
            $message = $length === 0
                ? 'Le contenu du touite ne peut pas être vide.'
                : 'Le contenu du touite doit comporter entre 1 et 235 caractères.';

            return HtmlHelper::formError(
                errors: $message,
                backLabel: 'Modifier le touite',
                title: 'Impossible de publier le touite'
            );
        }
        $mediaDescription = trim($_POST['descrip_media'] ?? '');

        if (
            isset($_FILES['media']) &&
            $_FILES['media']['error'] === UPLOAD_ERR_OK &&
            $_FILES['media']['size'] > 2 * 1024 * 1024
        ) {
            return HtmlHelper::formError(
                errors: 'Le fichier ne doit pas dépasser 2 Mo.',
                backLabel: 'Modifier le touite',
                title: 'Impossible de publier le touite'
            );
        }

        //capture de la date
        date_default_timezone_set('Europe/Paris');
        // Format AAAA-MM-JJ HH:MM:SS
        $postedAt = date('Y-m-d H:i:s');

        $mediaName = $this->saveMedia($_FILES);

        $touite = new Touite($content, $postedAt, 0, $mediaName, $mediaDescription);

        //on evalue s'il y un tag pour savoir où rediriger
        // on regarde si un # est présent
        if (str_contains($content, '#')) {
            $r = RepositoryFactory::getReader();
            $words = explode(' ', $content); // on divise la chaîne

            foreach ($words as $word) {
                if (str_starts_with($word, '#')) {
                    $tagLibelle = rtrim(substr($word, 1), '.,\'"');

                    if (strlen($tagLibelle) > 0) $_SESSION['tags'][$word] = $r->findTagByLibelle($tagLibelle) ?? new Tag($tagLibelle);
                }
            }

            if (isset($_SESSION['tags'])) {
                $_SESSION['touite'] = $touite;
                header('Location: ?action=addTouiteTag');
                exit;
            }
        }

        $w = RepositoryFactory::getWriter();
        $touite = $w->addTouite($touite, $this->user['id']);
        // header('Location: ?action=touite&id=' . $touite->getId());
        header('Location: ?action=feed&type=global');
        exit;
    }

    private function saveMedia(array $mediaFile): ?string {
        if (isset($mediaFile['media']) && $mediaFile['media']['error'] === UPLOAD_ERR_OK) {
            $extension = strtolower(pathinfo($mediaFile['media']['name'], PATHINFO_EXTENSION));

            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4'])) {
                $mediaDir = 'image' . DIRECTORY_SEPARATOR . 'touite_media' . DIRECTORY_SEPARATOR;
                $mediaName = $mediaDir . uniqid('touite_media_', true) . bin2hex(random_bytes(4)) . '.' . $extension;

                if (move_uploaded_file($mediaFile['media']['tmp_name'], $mediaName)) {
                    return $mediaName;
                }
            }
        }
        return null;
    }
}
