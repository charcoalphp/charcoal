<?php

namespace Charcoal\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\PaginationInterface;

/**
 * Implementation, as concrete class, of the PaginationInterface.
 */
class Pagination implements PaginationInterface
{
    const DEFAULT_PAGE = 0;
    const DEFAULT_NUM_PER_PAGE = 0;

    /**
     * @var integer $page
     */
    protected $page = self::DEFAULT_PAGE;
    /**
     * @var integer $numPerPage
     */
    protected $numPerPage = self::DEFAULT_NUM_PER_PAGE;

    /**
     * @param array $data The pagination data (page, num_per_page).
     * @return Pagination Chainable
     */
    public function setData(array $data)
    {
        if (isset($data['page'])) {
            $this->setPage($data['page']);
        }
        if (isset($data['num_per_page'])) {
            $this->setNumPerPage($data['num_per_page']);
        }
        return $this;
    }

    /**
     * @param integer $page The current page. Start at 0.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return Pagination (Chainable)
     */
    public function setPage($page)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'Page number needs to be numeric.'
            );
        }
        $page = (int)$page;
        if ($page < 0) {
            throw new InvalidArgumentException(
                'Page number needs to be >= 0.'
            );
        }
        $this->page = $page;
        return $this;
    }

    /**
     * @return integer
     */
    public function page()
    {
        return $this->page;
    }

    /**
     * @param integer $num The number of results to retrieve, per page.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return Pagination (Chainable)
     */
    public function setNumPerPage($num)
    {
        if (!is_numeric($num)) {
            throw new InvalidArgumentException(
                'Num-per-page needs to be numeric.'
            );
        }
        $num = (int)$num;
        if ($num < 0) {
            throw new InvalidArgumentException(
                'Num-per-page needs to be >= 0.'
            );
        }

        $this->numPerPage = $num;
        return $this;
    }

    /**
     * @return integer
     */
    public function numPerPage()
    {
        return $this->numPerPage;
    }

    /**
     * @return integer
     */
    public function first()
    {
        $page = $this->page();
        $numPerPage = $this->numPerPage();
        return max(0, (($page-1)*$numPerPage));
    }

    /**
     * Can be greater than the actual number of items in Storage
     * @return integer
     */
    public function last()
    {
        $first = $this->first();
        $numPerPage = $this->numPerPage();
        return ($first + $numPerPage);
    }
}
