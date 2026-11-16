<?php

namespace iutnc\touiteur\pagination;

/**
 * Classe Pagination de l’application.
 */
class Pagination {
    /** Nombre maximal de touites affichés par page. */
    public const PER_PAGE = 20;
    /**
     * Valeur utilisée pour page.
     */
    private int $page;
    /**
     * Valeur utilisée pour isLastPage.
     */
    private bool $isLastPage;

    /**
     * Initialise les données nécessaires à cet objet.
     * @param int $page numéro de page.
     */
    public function __construct(int $page = 1) {
        $this->page = max(1, $page);
        $this->isLastPage = false;
    }

    /**
     * Retourne le numéro de la page courante.
     * @return int Valeur numérique calculée.
     */
    public function getPage(): int {
        return $this->page;
    }

    // + 1 pour vérifier si un touite seras présent ou non dans la page suivante
    /**
     * Retourne le nombre maximal d’éléments par page.
     * @return int Valeur numérique calculée.
     */
    public function getLimit(): int {
        return self::PER_PAGE + 1;
    }

    /**
     * Retourne le décalage des éléments pour la page courante.
     * @return int Valeur numérique calculée.
     */
    public function getOffset(): int {
        return ($this->page - 1) * self::PER_PAGE;
    }

    /**
     * Indique si la page courante est la dernière.
     * @return bool Résultat de la vérification.
     */
    public function getIsLastPage(): bool {
        return $this->isLastPage;
    }

    /**
     * Met à jour l’état de la dernière page à partir du nombre d’éléments trouvés.
     * @param int $length nombre d’éléments trouvés.
     * @return void Aucune valeur.
     */
    public function setIsLastPage(int $length): void {
        $this->isLastPage = $length < $this->getLimit();
    }

    /**
     * Passe à la page précédente et retourne son numéro.
     * @return int Valeur numérique calculée.
     */
    public function previous(): int {
        return max(1, $this->page - 1);
    }

    /**
     * Passe à la page suivante et retourne son numéro.
     * @return int Valeur numérique calculée.
     */
    public function next(): int {
        return $this->page + 1;
    }
}