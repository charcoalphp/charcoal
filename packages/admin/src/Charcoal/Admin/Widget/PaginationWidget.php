<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
// From 'charcoal-core'
use Charcoal\Source\Pagination;
use Charcoal\Source\PaginationInterface;

/**
 * Pagination Widget.
 */
class PaginationWidget extends AdminWidget
{
    /**
     * Max pages count in the pagination
     */
    public const MAX_PAGE_COUNT = 10;

    /**
     * The pager object.
     */
    private ?\Charcoal\Source\Pagination $pager = null;

    /**
     * The total number of items.
     */
    private ?int $numTotal = null;

    /**
     * @var integer
     */
    private $maxPageCount = self::MAX_PAGE_COUNT;

    /**
     * Retrieve the Paginationb object.
     *
     * @return PaginationInterface
     */
    protected function pager(): \Charcoal\Source\Pagination
    {
        if (!$this->pager instanceof \Charcoal\Source\Pagination) {
            $this->pager = $this->createPagination();
        }

        return $this->pager;
    }

    /**
     * Create the Pagination object.
     *
     * @return PaginationInterface
     */
    protected function createPagination(): \Charcoal\Source\Pagination
    {
        return new Pagination();
    }

    /**
     * Set the page number.
     *
     * @param  integer $page The current page. Pages should start at 1.
     */
    public function setPage($page): static
    {
        $this->pager()->setPage($page);

        return $this;
    }

    /**
     * Retrieve the page number.
     *
     * @return integer
     */
    public function page()
    {
        return $this->pager()->page();
    }

    /**
     * Retrieve the previous page number.
     */
    public function pagePrev(): int
    {
        return max(1, ($this->page() - 1));
    }

    /**
     * Retrieve the next page number.
     */
    public function pageNext(): int
    {
        return min($this->numPages(), ($this->page() + 1));
    }

    /**
     * Set the number of results per page.
     *
     * @param  integer $count The number of results to return, per page.
     *                        Use 0 to request all results.
     */
    public function setNumPerPage($count): static
    {
        $this->pager()->setNumPerPage($count);

        return $this;
    }

    /**
     * Retrieve the number of results per page.
     *
     * @return integer
     */
    public function numPerPage()
    {
        return $this->pager()->numPerPage();
    }

    /**
     * Retrieve the total number of items (for counting pages).
     *
     * @return integer
     */
    public function numTotal(): ?int
    {
        return $this->numTotal;
    }

    /**
     * Set the total number of items (for counting pages).
     *
     * @param  integer $total The total number of items.
     * @throws InvalidArgumentException If the argument is not a number or lower than 0.
     */
    public function setNumTotal($total): static
    {
        if (!is_numeric($total)) {
            throw new InvalidArgumentException(
                'Total Number must be numeric.'
            );
        }

        $total = (int)$total;
        if ($total < 0) {
            throw new InvalidArgumentException(
                'Total Number must be greater than zero.'
            );
        }

        $this->numTotal = $total;

        return $this;
    }

    public function pages(): array
    {
        if ($this->quickJumpEnabled()) {
            return $this->buildQuickJumpForm();
        }

        $out      = [];
        $i        = 1;
        $numPages = $this->numPages();

        while ($i <= $numPages) {
            $active = ($i == $this->page());
            $out[]  = $this->formatPage($i);
            $i++;
        }

        return $out;
    }

    private function buildQuickJumpForm(): array
    {
        $i            = 1;
        $numPages     = $this->numPages();
        $maxPageCount = $this->maxPageCount();

        // Get the range on each side of the pager
        $half  = ($numPages / 2);
        $max   = ($maxPageCount / 2);
        $range = round(min($half, $max));
        $left  = [];
        $right = [];

        // Remember if the active page was hit
        $hasActive = false;

        // Separator
        $separator = [
            'separator' => true
        ];

        // First range loop
        while ($i <= $range) {
            $active = ($i == $this->page());
            if ($active) {
                $hasActive = true;
            }
            $left[] = [
                'separator' => false,
                'active'    => $active,
                'num'       => $i
            ];
            $i++;
        }

        // Second range loop
        $rangeNumPages = ($numPages - $range + 1);
        while ($rangeNumPages <= $numPages) {
            $active = ($rangeNumPages == $this->page());
            if ($active) {
                $hasActive = true;
            }
            $right[] = $this->formatPage($rangeNumPages);
            $rangeNumPages++;
        }

        // In between the 2 ranges
        $middle = $hasActive ? [$separator] : [];
        if (!$hasActive) {
            if (($range + 1) < $this->page()) {
                $middle[] = $separator;
            }

            $middle[] = $this->formatPage($this->page());

            if (($numPages - $range + 1) > $this->page()) {
                $middle[] = $separator;
            }
        }

        return array_merge($left, $middle, $right);
    }

    /**
     * @param integer $page The page index.
     */
    private function formatPage($page): array
    {
        return [
            'separator' => false,
            'active'    => ($page == $this->page()),
            'num'       => $page
        ];
    }

    /**
     * Retrieve the number of pages.
     *
     * @return integer
     */
    public function numPages(): int|float
    {
        if ($this->numPerPage() == 0) {
            return 1;
        }

        return ceil($this->numTotal() / $this->numPerPage());
    }

    /**
     * @return integer
     */
    public function maxPageCount()
    {
        return $this->maxPageCount;
    }

    /**
     * @param integer $maxPageCount The maximum number of page to display in pager at the same time.
     */
    public function setMaxPageCount($maxPageCount): static
    {
        $this->maxPageCount = $maxPageCount;

        return $this;
    }


    /**
     * Determine if pagination can be displayed.
     */
    public function showPagination(): bool
    {
        return ($this->numPages() > 1);
    }

    /**
     * Determine if the "previous page" link can be displayed.
     */
    public function previousEnabled(): bool
    {
        return ($this->page() > 1);
    }

    /**
     * Determine if the "next page" link can be displayed.
     */
    public function nextEnabled(): bool
    {
        return ($this->page() < $this->numPages());
    }

    /**
     * His the quick jump input allowed?
     */
    public function quickJumpEnabled(): bool
    {
        return ($this->numPages() > $this->maxPageCount());
    }
}
