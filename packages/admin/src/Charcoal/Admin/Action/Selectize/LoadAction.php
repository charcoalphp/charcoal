<?php

namespace Charcoal\Admin\Action\Selectize;

use Exception;
// From Pimple
use Pimple\Container;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-admin'
use Charcoal\Admin\Action\Object\LoadAction as BaseLoadAction;
use Charcoal\Admin\Action\Selectize\SelectizeRendererAwareTrait;

/**
 * Selectize Load Action
 */
class LoadAction extends BaseLoadAction
{
    use SelectizeRendererAwareTrait;

    /**
     * The collection to return.
     *
     * @var array|mixed
     */
    private $selectizeCollection;

    /**
     * @var string $query
     */
    private $query;

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    #[\Override]
    protected function validDataFromRequest(): array
    {
        return array_merge([
            'selectize_prop_ident', 'selectize_property'
        ], parent::validDataFromRequest());
    }

    /**
     * @param  RequestInterface  $request  The request options.
     * @param  ResponseInterface $response The response to return.
     * @return ResponseInterface
     * @throws UnexpectedValueException If "obj_id" is passed as $request option.
     * @todo   Implement obj_id support for load object action
     */
    #[\Override]
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $failMessage = $this->translator()->trans('Failed to load object(s)');
        $errorThrown = strtr($this->translator()->trans('{{ errorMessage }}: {{ errorThrown }}'), [
            '{{ errorMessage }}' => $failMessage
        ]);

        try {
            /** @var Charcoal\Admin\Property\Input\SelectizeInput */
            $input = $this->selectizeInput();

            /** @var Charcoal\Property\ObjectProperty */
            $property = $input->property();

            if ($this->query()) {
                /** @var array<string, mixed> */
                $options   = $input->selectizeOptions();
                $choiceMap = $input->choiceObjMap();

                if (!empty($options['searchProperties'])) {
                    $searchProperties = (array)$options['searchProperties'];
                } elseif (
                    !empty($choiceMap['label']) &&
                    !str_contains((string)$choiceMap['label'], '{{')
                ) {
                    $searchProperties = [ $choiceMap['label'] ];
                } else {
                    $searchProperties = [];
                }

                if ($searchProperties !== []) {
                    $search = [
                        'conjunction' => 'OR',
                        'conditions'  => [],
                    ];
                    foreach ($searchProperties as $searchProperties) {
                        $search['conditions'][] = [
                            'property' => $searchProperties,
                            'operator' => 'LIKE',
                            'value'    => '%' . $this->query() . '%',
                        ];
                    }

                    $filters = $property->filters();
                    if (is_array($filters)) {
                        $filters[] = $search;
                    } else {
                        $filters = [ $search ];
                    }

                    $property->setFilters($filters);
                }
            }

            $choices = $property->choices();

            $this->setSelectizeCollection($this->selectizeVal($choices));

            $count = count($choices);
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
     * @return string
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * @param string $query Query for LoadAction.
     */
    public function setQuery($query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return array|mixed
     */
    public function selectizeCollection()
    {
        return $this->selectizeCollection;
    }

    /**
     * @param array|mixed $selectizeCollection The collection to return.
     */
    public function setSelectizeCollection($selectizeCollection): static
    {
        $this->selectizeCollection = $selectizeCollection;

        return $this;
    }

    #[\Override]
    public function results(): array
    {
        return [
            'success'    => $this->success(),
            'feedbacks'  => $this->feedbacks(),
            'selectize'  => $this->selectizeCollection()
        ];
    }

    /**
     * Dependencies
     * @param Container $container DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->setSelectizeRenderer($container['selectize/renderer']);
        $this->setPropertyInputFactory($container['property/input/factory']);
        $this->setPropertyFactory($container['property/factory']);
    }
}
