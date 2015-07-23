<?php

namespace Charcoal\Source;

interface PaginationInterface
{
    /**
    * @param array $data
    * @return Pagination Chainable
    */
    public function set_data(array $data);

    /**
    * @param integer $page
    * @throws InvalidArgumentException if the parameter is not numeric or < 0
    * @return Pagination (Chainable)
    */
    public function set_page($page);

    /**
    * @return integer
    */
    public function page();

    /**
    * @param integer $num
    * @throws InvalidArgumentException if the parameter is not numeric or < 0
    * @return Pagination (Chainable)
    */
    public function set_num_per_page($num);
    /**
    * @return integer
    */
    public function num_per_page();

    /**
    * @return integer
    */
    public function first();

    /**
    * Can be greater than the actual number of items to retrieve
    * @return integer
    */
    public function last();
}
