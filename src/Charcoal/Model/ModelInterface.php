<?php

namespace Charcoal\Model;

interface ModelInterface
{
    public function set_data($data);
    public function set_flat_data($data);
    public function data();
    public function properties();
    public function property($property_ident);
    public function p($property_ident=null);
}
