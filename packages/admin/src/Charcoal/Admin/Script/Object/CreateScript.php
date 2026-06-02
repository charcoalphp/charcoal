<?php

namespace Charcoal\Admin\Script\Object;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;

/**
 *
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
            ],
            'obj-id' => [
                'longPrefiex'  => 'obj-id',
                'description'  => 'Object ID',
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
            'Create a new object'
        );

        $objType = $this->argOrInput('obj-type');

        $obj = $this->modelFactory()->create($objType);

        $properties = $obj->properties();

        $vals = [];
        foreach ($properties as $prop) {
            $input = $this->propertyToInput($prop);
            $vals[$prop->ident()] = $input->prompt();
        }

        $obj->setFlatData($vals);
        $ret = $obj->save();

        $climate->green()->out(
            "\n" . sprintf('Success! Object "%s" created.', $ret)
        );

        return $response;
    }
}
