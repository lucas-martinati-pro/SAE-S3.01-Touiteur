<?php

namespace iutnc\touiteur\pagination;

class Pagination {
    public const PER_PAGE = 20;
    private int $page;
    private bool $isLastPage;

    public function __construct(int $page = 1) {
        $this->page = max(1, $page);
        $this->isLastPage = false;
    }

    public function getPage(): int {
        return $this->page;
    }

    // + 1 pour vérifier si un touite seras présent ou non dans la page suivante
    public function getLimit(): int {
        return self::PER_PAGE + 1;
    }

    public function getOffset(): int {
        return ($this->page - 1) * self::PER_PAGE;
    }

    public function getIsLastPage(): bool {
        return $this->isLastPage;
    }

    public function setIsLastPage(int $length) {
        $this->isLastPage = $length < $this->getLimit();
    }

    public function previous(): int {
        return max(1, $this->page - 1);
    }

    public function next(): int {
        return $this->page + 1;
    }
}