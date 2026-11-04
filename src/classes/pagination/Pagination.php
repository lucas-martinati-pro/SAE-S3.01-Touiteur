<?php

namespace iutnc\touiteur\pagination;

class Pagination {
    public const PER_PAGE = 20;
    private int $page;

    public function __construct(int $page = 1) {
        $this->page = max(1, $page);
    }

    public function getPage(): int {
        return $this->page;
    }

    public function getLimit(): int {
        return self::PER_PAGE;
    }

    public function getOffset(): int {
        return ($this->page - 1) * self::PER_PAGE;
    }

    public function previous(): int {
        return max(1, $this->page - 1);
    }

    public function next(): int {
        return $this->page + 1;
    }
}