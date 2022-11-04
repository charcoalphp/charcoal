<?php

namespace Charcoal\Admin\Action\Object;

use Charcoal\Object\RevisionsManager;
use Exception;
use InvalidArgumentException;
// From PSR-7
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-object'
use Charcoal\Object\ObjectRevisionInterface;
// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Action: Restore Object Revision
 *
 * ## Required Parameters
 *
 * - `obj_type` (_string_) — The object type, as an identifier for a {@see \Charcoal\Model\ModelInterface}.
 * - `obj_id` (_mixed_) — The object ID to revert
 * - `rev_num` (_integer_) — The object's revision to restore the object to
 *
 * ## Response
 *
 * - `success` (_boolean_) — TRUE if the object was properly restored, FALSE in case of any error.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Revision has been restored
 * - `400` — Client error; Invalid request data
 * - `500` — Server error; Revision could not be restored
 */
class RevertRevisionAction extends AdminAction implements ObjectContainerInterface
{
    use ObjectContainerTrait;

    /**
     * The revision number to restore.
     *
     * @var integer|null
     */
    protected $revNum;

    private RevisionsManager $revisionService;

    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->revisionService = $container->get('revisions/manager');
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type',
            'obj_id',
            'rev_num',
        ], parent::validDataFromRequest());
    }

    /**
     * Set the revision number to restore.
     *
     * @param integer $revNum The revision number to load.
     * @return ObjectContainerInterface Chainable
     * @throws InvalidArgumentException If the given revision is invalid.
     */
    protected function setRevNum($revNum)
    {
        if (!is_numeric($revNum)) {
            throw new InvalidArgumentException(sprintf(
                'Revision must be an integer, received %s.',
                (is_object($revNum) ? get_class($revNum) : gettype($revNum))
            ));
        }

        $this->revNum = (int)$revNum;

        return $this;
    }

    /**
     * Retrieve the revision number to restore.
     *
     * @return integer|null
     */
    public function revNum()
    {
        return $this->revNum;
    }

    /**
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        try {
            $translator = $this->translator();

            $failMessage = $translator->translate('Failed to restore revision');
            $errorThrown = strtr($translator->translate('{{ errorMessage }}: {{ errorThrown }}'), [
                '{{ errorMessage }}' => $failMessage
            ]);

            $obj      = $this->obj();
            $revNum   = $this->revNum();
            $this->revisionService->setModel($obj);

            $revision = $this->revisionService->getRevisionFromNumber($revNum);
            if (!$revision['id']) {
                $this->setSuccess(false);

                if ($revision->source()->tableExists()) {
                    $this->addFeedback('error', strtr('Revision #{{ revNum }} does not exist for {{ model }}', [
                        '{{ model }}'  => $this->getSingularLabelFromObj($obj),
                        '{{ revNum }}' => $revNum,
                    ]));

                    return $response->withStatus(404);
                }

                $this->addFeedback('error', strtr('No revisions available for {{ model }}', [
                    '{{ model }}' => $this->getSingularLabelFromObj($obj),
                ]));

                return $response->withStatus(404);
            }

            $result = $this->revisionService->revertToRevision($revNum);

            if ($result) {
                $doneMessage = $translator->translate(
                    'Object has been successfully restored to revision from {{ revisionDate }}'
                );

                $this->addFeedback('success', strtr($doneMessage, [
                    '{{ revisionDate }}' => $revision['revTs']->format('Y-m-d @ H:i:s'),
                ]));
                $this->addFeedback('success', strtr($translator->translate('Restored Revision: {{ revisionNum }}'), [
                    '{{ revisionNum }}' => $revNum,
                ]));
                $this->addFeedback('success', strtr($translator->translate('Reverted Object: {{ objId }}'), [
                    '{{ objId }}' => $obj->id(),
                ]));
                $this->setSuccess(true);

                return $response;
            } else {
                $this->addFeedback('error', $failMessage);
                $this->setSuccess(false);

                return $response->withStatus(500);
            }
        } catch (Exception $e) {
            $this->addFeedback('error', strtr($errorThrown, [
                '{{ errorThrown }}' => $e->getMessage()
            ]));
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }
}
