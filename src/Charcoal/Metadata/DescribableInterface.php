<?php

namespace Charcoal\Metadata;

use \Charcoal\Metadata\MetadataInterface as MetadataInterface;

interface DescribableInterface
{
    public function set_metadata(MetadataInterface $metadata);
    public function metadata();
    public function load_metadata($metadata_ident='');
    public function metadata_ident();
}
