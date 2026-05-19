<?php

namespace Charcoal\Admin\Script\Object\Table;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;

/**
 * Create an object's table (sql source) according to its metadata's properties.
 */
class CreateScript extends AdminScript
{
    #[\Override]
    public function defaultArguments(): array
    {
        $arguments = [
            'obj-type' => [
                'longPrefix'   => 'obj-type',
                'description'  => 'Object type',
                'defaultValue' => ''
            ]
        ];
        return array_merge(parent::defaultArguments(), $arguments);
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     */
    public function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        unset($request);

        $climate = $this->climate();

        $climate->underline()->out(
            'Create object table from metadata'
        );

        $objType = $this->argOrInput('obj-type');

        $obj = $this->modelFactory()->create($objType);

        $source = $obj->source();

        $table = $source->table();

        if ($this->verbose()) {
            $climate->bold()->out(
                sprintf('The table "%s" will be created...', $table)
            );
            $metadata = $obj->metadata();
            $properties = $metadata->properties();
            $prop_names = array_keys($properties);
            $climate->out(
                sprintf(
                    'The %d following properties will be used: "%s"',
                    count($prop_names),
                    implode(', ', $prop_names)
                )
            );
        }

        $input = $climate->confirm(
            'Continue?'
        );
        if (!$input->confirmed()) {
            return $response;
        }

        if ($source->tableExists()) {
            $climate->error(
                sprintf('The table "%s" already exists. This script can only create new tables.', $table)
            );
            $climate->darkGray()->out(
                'If you want to alter the table, run the `admin/object/table/alter` script.'
            );
            return $response;
        }

        $source->createTable();

        $climate->green()->out(
            "\n" . 'Success!'
        );

        return $response;
    }
}
