<?php

namespace Charcoal\Source;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\PaginationInterface;

/**
 * Pagination Clause
 *
 * For limiting the results of a query.
 */
class Pagination extends AbstractExpression implements
    PaginationInterface
{
    public const DEFAULT_PAGE  = 1;
    public const DEFAULT_COUNT = 0;

    /**
     * The current page.
     *
     * @var integer
     */
    protected $page = self::DEFAULT_PAGE;

    /**
     * The number of results per page.
     *
     * @var integer
     */
    protected $numPerPage = self::DEFAULT_COUNT;

    /**
     * Set the pagination clause data.
     *
     * @param  array<string,mixed> $data The expression data;
     *     as an associative array.
     * @return self
     */
    public function setData(array $data)
    {
        parent::setData($data);

        if (isset($data['page'])) {
            $this->setPage($data['page']);
        }

        if (isset($data['per_page'])) {
            $this->setNumPerPage($data['per_page']);
        }

        if (isset($data['num_per_page'])) {
            $this->setNumPerPage($data['num_per_page']);
        }

        return $this;
    }

    /**
     * Retrieve the default values for pagination.
     *
     * @return array<string,mixed> An associative array.
     */
    public function defaultData()
    {
        return [
            'page'         => self::DEFAULT_PAGE,
            'num_per_page' => self::DEFAULT_COUNT,
            'active'       => true,
            'name'         => null,
        ];
    }

    /**
     * Retrieve the pagination clause structure.
     *
     * @return array<string,mixed> An associative array.
     */
    public function data()
    {
        return [
            'page'         => $this->page(),
            'num_per_page' => $this->numPerPage(),
            'active'       => $this->active(),
            'name'         => $this->name(),
        ];
    }

    /**
     * Set the page number.
     *
     * @param  integer $page The current page.
     *     Pages should start at 1.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setPage($page)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'Page number must be numeric.'
            );
        }

        $page = (int)$page;
        if ($page === 0) {
            $page = 1;
        } elseif ($page < 0) {
            throw new InvalidArgumentException(
                'Page number must be greater than zero.'
            );
        }

        $this->page = $page;
        return $this;
    }

    /**
     * Retrieve the page number.
     *
     * @return integer
     */
    public function page()
    {
        return $this->page;
    }

    /**
     * Set the number of results per page.
     *
     * @param  integer $count The number of results to return, per page.
     *     Use 0 to request all results.
     * @throws InvalidArgumentException If the parameter is not numeric or < 0.
     * @return self
     */
    public function setNumPerPage($count)
    {
        if (!is_numeric($count)) {
            throw new InvalidArgumentException(
                'Number Per Page must be numeric.'
            );
        }

        $count = (int)$count;
        if ($count < 0) {
            throw new InvalidArgumentException(
                'Number Per Page must be greater than zero.'
            );
        }

        $this->numPerPage = $count;
        return $this;
    }

    /**
     * Retrieve the number of results per page.
     *
     * @return integer
     */
    public function numPerPage()
    {
        return $this->numPerPage;
    }

    /**
     * Retrieve the pagination's lowest possible index.
     *
     * @return integer
     */
    public function first()
    {
        $page  = $this->page();
        $limit = $this->numPerPage();

        return max(0, (($page - 1) * $limit));
    }

    /**
     * Retrieve the pagination's highest possible index.
     *
     * Note: Can be greater than the actual number of items in storage.
     *
     * @return integer
     */
    public function last()
    {
        $first = $this->first();
        $limit = $this->numPerPage();

        return ($first + $limit);
    }
}
