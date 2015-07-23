<?php

namespace Charcoal\Source;

use \InvalidArgumentException as InvalidArgumentException;

/**
*
*/
class Pagination implements PaginationInterface
{
    const DEFAULT_PAGE = 0;
    const DEFAULT_NUM_PER_PAGE = 0;

    /**
    * @var integer $_page
    */
    protected $_page = self::DEFAULT_PAGE;
    /**
    * @var integer $_num_per_page
    */
    protected $_num_per_page = self::DEFAULT_NUM_PER_PAGE;

    /**
    * @param array $data
    * @return Pagination Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['page'])) {
            $this->set_page($data['page']);
        }
        if (isset($data['num_per_page'])) {
            $this->set_num_per_page($data['num_per_page']);
        }
        return $this;
    }

    /**
    * @param integer $page
    * @throws InvalidArgumentException if the parameter is not numeric or < 0
    * @return Pagination (Chainable)
    */
    public function set_page($page)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException('Page number needs to be numeric.');
        }
        $page = (int)$page;
        if ($page < 0) {
            throw new InvalidArgumentException('Page number needs to be >= 0.');
        }
        $this->_page = $page;
        return $this;
    }

    /**
    * @return integer
    */
    public function page()
    {
        return $this->_page;
    }

    /**
    * @param integer $num
    * @throws InvalidArgumentException if the parameter is not numeric or < 0
    * @return Pagination (Chainable)
    */
    public function set_num_per_page($num)
    {
        if (!is_numeric($num)) {
            throw new InvalidArgumentException('Num-per-page needs to be numeric.');
        }
        $num = (int)$num;
        if ($num < 0) {
            throw new InvalidArgumentException('Num-per-page needs to be >= 0.');
        }

        $this->_num_per_page = $num;
        return $this;
    }

    /**
    * @return integer
    */
    public function num_per_page()
    {
        return $this->_num_per_page;
    }

    /**
    * @return integer
    */
    public function first()
    {
        $page = $this->page();
        $num_per_page = $this->num_per_page();
        return max(0, (($page-1)*$num_per_page));
    }

    /**
    * Can be greater than the actual number of items to retrieve
    * @return integer
    */
    public function last()
    {
        $first = $this->first();
        $num_per_page = $this->num_per_page();
        return ($first + $num_per_page);
    }
}
