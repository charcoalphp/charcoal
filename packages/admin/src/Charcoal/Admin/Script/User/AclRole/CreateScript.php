<?php

namespace Charcoal\Admin\Script\User\AclRole;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-core'
use Charcoal\Validator\ValidatableInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;

/**
 * Create user script.
 */
class CreateScript extends AdminScript
{
    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     */
    public function run(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        unset($request);

        $climate = $this->climate();

        $climate->underline()->out(
            'Create a new Acl Role'
        );

        $role = $this->modelFactory()->create('charcoal/admin/user/acl-role');
        $properties = $role->properties();

        $shownProps = [
            'ident',
            'parent',
            'allowed',
            'denied',
            'superuser'
        ];

        $vals = [];
        foreach ($properties as $prop) {
            $ident = $prop->ident();

            if (!in_array($ident, $shownProps)) {
                continue;
            }

            $input = $this->propertyToInput($prop);
            $value = $input->prompt();

            if ($prop instanceof ValidatableInterface) {
                $valid = $prop->setVal($value)->validate();
                $prop->clearVal();
            }

            $vals[$ident] = $value;
        }

        $role->setFlatData($vals);

        $ret = $role->save();
        if ($ret) {
            $climate->green()->out("\n" . sprintf('Success! Role "%s" created.', $ret));
        } else {
            $climate->red()->out("\nError. Object could not be created.");
        }

        return $response;
    }

    public function authRequired(): bool
    {
        return false;
    }
}
