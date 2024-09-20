<?php

namespace Leandroferreirama\Paginator;

class Paginator
{
    private ?int $page;
    private int $pages;
    private int $rows;
    private ?int $limit;
    private ?int $offset;
    private int $range;
    private ?string $link;
    private string $title;
    private ?string $hash;
    private array $first;
    private array $last;
    private string $params;

    /**
     * Paginator constructor.
     * @param string|null $link
     * @param string|null $title
     * @param array|null $first
     * @param array|null $last
     */
    public function __construct(string $link = null, string $title = null, array $first = null, array $last = null)
    {
        $this->link = ($link ?? "?page=");
        $this->title = ($title ?? "Página");
        $this->first = ($first ?? ["Primeira página", "<<"]);
        $this->last = ($last ?? ["Última página", ">>"]);
    }

    /**
     * @param int $rows
     * @param int $limit
     * @param int|null $page
     * @param int $range
     * @param string|null $hash
     * @param array $params
     */
    public function pager(
        int $rows,
        int $limit = 10,
        int $page = null,
        int $range = 3,
        string $hash = null,
        array $params = []
    ): void {
        $this->rows = $this->toPositive($rows);
        $this->limit = $this->toPositive($limit);
        $this->range = $this->toPositive($range);
        $this->pages = (int)ceil($this->rows / $this->limit);
        $this->page = ($page <= $this->pages ? $this->toPositive($page) : $this->pages);

        $this->offset = (($this->page * $this->limit) - $this->limit >= 0 ? ($this->page * $this->limit) - $this->limit : 0);
        $this->hash = (!empty($hash) ? "#{$hash}" : null);

        $this->addGetParams($params);

        if ($this->rows && $this->offset >= $this->rows) {
            header("Location: {$this->link}" . ceil($this->rows / $this->limit));
            exit;
        }
    }

    /**
     * @return int
     */
    public function limit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function offset(): ?int
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function page(): ?int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function pages(): int
    {
        return $this->pages;
    }

    /**
     * @param string|null $cssClass
     * @param bool $fixedFirstAndLastPage
     * @return null|string
     */
    public function render(string $class = "justify-content-center", bool $fixedFirstAndLastPage = true): ?string
    {
        if ($this->rows > $this->limit):
            $paginator = "<nav>";
            $paginator = "<ul class=\"pagination {$class}\">";
            $paginator .= $this->firstPage($fixedFirstAndLastPage);
            $paginator .= $this->beforePages();
            $paginator .= "<li class=\"page-item active\"><a class=\"page-link \">{$this->page}</a></li>";
            $paginator .= $this->afterPages();
            $paginator .= $this->lastPage($fixedFirstAndLastPage);
            $paginator .= "</ul></nav>";
            return $paginator;
        endif;

        return null;
    }

    /**
     * @return null|string
     */
    private function beforePages(): ?string
    {
        $before = null;
        for ($iPag = $this->page - $this->range; $iPag <= $this->page - 1; $iPag++):
            if ($iPag >= 1):
                $before .= "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"{$this->title} {$iPag}\" title=\"{$this->title} {$iPag}\" href=\"{$this->link}{$iPag}{$this->hash}{$this->params}\">{$iPag}</a></li>";
            endif;
        endfor;

        return $before;
    }

    /**
     * @return string|null
     */
    private function afterPages(): ?string
    {
        $after = null;
        for ($dPag = $this->page + 1; $dPag <= $this->page + $this->range; $dPag++):
            if ($dPag <= $this->pages):
                $after .= "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"{$this->title} {$dPag}\" title=\"{$this->title} {$dPag}\" href=\"{$this->link}{$dPag}{$this->hash}{$this->params}\">{$dPag}</a></li>";
            endif;
        endfor;

        return $after;
    }

    /**
     * @param bool $fixedFirstAndLastPage
     * @return string|null
     */
    public function firstPage(bool $fixedFirstAndLastPage = true): ?string
    {
        if ($fixedFirstAndLastPage || $this->page != 1) {
            return "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"{$this->first[0]}\" title=\"{$this->first[0]}\" href=\"{$this->link}1{$this->hash}{$this->params}\">{$this->first[1]}</a></li>";
        }
        return null;
    }

    /**
     * @param bool $fixedFirstAndLastPage
     * @return string|null
     */
    public function lastPage(bool $fixedFirstAndLastPage = true): ?string
    {
        if ($fixedFirstAndLastPage || $this->page != $this->pages) {
            return "<li class=\"page-item\"><a class=\"page-link\" aria-label=\"{$this->last[0]}\" title=\"{$this->last[0]}\" href=\"{$this->link}{$this->pages}{$this->hash}{$this->params}\">{$this->last[1]}</a></li>";
        }
        return null;
    }

    /**
     * @param $number
     * @return int
     */
    private function toPositive($number): int
    {
        return ($number >= 1 ? $number : 1);
    }

    /**
     * Add get parameters
     * @param array $params
     * @return Paginator
     */
    private function addGetParams(array $params): Paginator
    {
        $this->params = '';

        if (count($params) > 0) {
            if (isset($params['page'])) {
                unset($params['page']);
            }

            $this->params = '&';
            $this->params .= http_build_query($params);
        }

        return $this;
    }
}