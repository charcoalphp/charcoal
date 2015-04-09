<?php

namespace \Charcoal\Model;

interface RoutableInterface
{
    public function url();
    public function url_options();
    public function short_url();
    public function external_url();
}
