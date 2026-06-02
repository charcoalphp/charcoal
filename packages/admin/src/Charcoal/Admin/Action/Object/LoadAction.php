<?php

namespace Charcoal\Admin\Action\Object;

use Exception;
use UnexpectedValueException;
use InvalidArgumentException;
use RuntimeException;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From Pimple
use Pimple\Container;
// From 'charcoal-core'
use Charcoal\Model\Collection;
use Charcoal\Loader\CollectionLoader;
// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;

/**
 * Action: Load one or more objects from storage.
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 *
 * ## Optional Parameters
 *
 * - `obj_id` (_mixed_) — The object ID to load.
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object(s) was/were loaded, FALSE in case of any error.
 * - `collection` (_Charcoal\Model\Collection_) — One or more of the requested objects, if any.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Object(s) loaded, if any
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Object(s) could not be loaded, if any
 */
class LoadAction extends AdminAction
{
    /**
     * Store the collection loader for the current class.
     */
    private ?\Charcoal\Loader\CollectionLoader $collectionLoader = null;

    /**
     * @var string
     */
    protected $objType;

    /**
     * @var Collection
     */
    protected $objCollection;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    #[\Override]
    protected function validDataFromRequest(): array
    {
        return array_merge([
            'obj_type', 'obj_id'
        ], parent::validDataFromRequest());
    }

    /**
     * @param  RequestInterface  $request  The request options.
     * @param  ResponseInterface $response The response to return.
     * @return ResponseInterface
     * @throws UnexpectedValueException If "obj_id" is passed as $request option.
     * @todo   Implement obj_id support for load object action
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $failMessage = $this->translator()->translation('Failed to load object(s)');
        $errorThrown = strtr($this->translator()->translation('{{ errorMessage }}: {{ errorThrown }}'), [
            '{{ errorMessage }}' => $failMessage
        ]);
        $reqMessage  = $this->translator()->translation(
            '{{ parameter }} required, must be a {{ expectedType }}, received {{ actualType }}'
        );

        $objType = $request->getParam('obj_type');
        $objId   = $request->getParam('obj_id');

        if ($objId) {
            $this->addFeedback('error', strtr('{{ parameter }} not supported', [
                '{{ parameter }}' => '"obj_id"'
            ]));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        if (!$objType) {
            $actualType = get_debug_type($objType);
            $this->addFeedback('error', strtr($reqMessage, [
                '{{ parameter }}'    => '"obj_type"',
                '{{ expectedType }}' => 'string',
                '{{ actualType }}'   => $actualType,
            ]));
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        try {
            $this->logger->debug('[Admin] Loading object: ' . $objType);

            $this->setObjType($objType);
            $this->loadObjectCollection($objType);

            $count = count($this->objCollection);
            $doneMessage = match ($count) {
                0 => $this->translator()->translation('No objects found.'),
                1 => $this->translator()->translation('One object found.'),
                default => strtr($this->translator()->translation('{{ count }} objects found.'), [
                    '{{ count }}' => $count
                ]),
            };
            $this->addFeedback('success', $doneMessage);
            $this->setSuccess(true);

            return $response;
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }

    /**
     * @return array The object collection parsed as array
     */
    public function objCollection(): array
    {
        if (!$this->objCollection) {
            return [];
        }

        return $this->objCollection->objects();
    }

    /**
     * Retrieve the model collection loader.
     *
     * @throws RuntimeException If the collection loader was not previously set.
     */
    public function collectionLoader(): \Charcoal\Loader\CollectionLoader
    {
        if (!$this->collectionLoader instanceof \Charcoal\Loader\CollectionLoader) {
            throw new RuntimeException(sprintf(
                'Collection Loader is not defined for "%s"',
                static::class
            ));
        }

        return $this->collectionLoader;
    }

    /**
     * @return string The object type as string.
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * @param string $objType The object type as string.
     * @throws InvalidArgumentException If the object type is not a string.
     */
    public function setObjType($objType): static
    {
        if (!is_string($objType)) {
            throw new InvalidArgumentException(sprintf(
                'Object type must be a string, received %s',
                get_debug_type($objType)
            ));
        }

        $this->objType = $objType;

        return $this;
    }

    #[\Override]
    public function results(): array
    {
        return [
            'success'    => $this->success(),
            'feedbacks'  => $this->feedbacks(),
            'collection' => $this->objCollection()
        ];
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setCollectionLoader($container['model/collection/loader']);
    }

    /**
     * Load Object Collection
     *
     * @param string $objType The object type as string.
     * @return \Charcoal\Model\Collection
     */
    protected function loadObjectCollection($objType): \ArrayAccess|array
    {
        $proto  = $this->modelFactory()->get($objType);
        $loader = $this->collectionLoader();
        $loader->setModel($proto);
        $loader->addFilter('active', true);

        $this->objCollection = $loader->load();

        return $this->objCollection;
    }

    /**
     * Set a model collection loader.
     *
     * @param CollectionLoader $loader The collection loader.
     */
    protected function setCollectionLoader(CollectionLoader $loader): static
    {
        $this->collectionLoader = $loader;

        return $this;
    }
}
