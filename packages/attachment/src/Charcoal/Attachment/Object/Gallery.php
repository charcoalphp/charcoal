<?php

namespace Charcoal\Attachment\Object;

// From 'charcoal-attachment'
use Charcoal\Attachment\Object\Container;

/**
 * Gallery Attachment Type
 *
 * This type allows for nesting of additional attachment types.
 */
class Gallery extends Container
{
    /**
     * The quantity of columns per row. Should be a multiple of 12.
     *
     * @var integer
     */
    protected $numColumns = 4;

    /**
     * Retrieve the container's attachments as rows containing columns.
     *
     * @return array
     */
    public function attachmentsAsRows()
    {
        $rows = [];

        if ($this->hasAttachments()) {
            $rows = array_chunk($this->attachments()->values(), $this->numColumns);

            /** Map row content with useful front-end properties. */
            array_walk($rows, function (&$attachment, $index) {
                $attachment = [
                    'columns' => $attachment,
                    'isFirst' => ($index === 0),
                ];
            });
        }

        return $rows;
    }

    /**
     * Retrieve the Bootstrap column width to be used in front-end templating.
     *
     * @return string
     */
    public function columnWidth()
    {
        return (string)ceil(12 / $this->numColumns);
    }
}
