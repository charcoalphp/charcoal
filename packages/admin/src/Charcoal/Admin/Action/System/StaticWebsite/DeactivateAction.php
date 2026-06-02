<?php

declare(strict_types=1);

namespace Charcoal\Admin\Action\System\StaticWebsite;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From Pimple
use Pimple\Container;
use Charcoal\Admin\AdminAction;

/**
 * Class DeactivateAction
 */
class DeactivateAction extends AdminAction
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $staticLink = $this->basePath . DIRECTORY_SEPARATOR . 'www/static';
        if (!file_exists($staticLink)) {
            $this->setSuccess(false);
            return $response->withStatus(409);
        }

        $ret = unlink($staticLink);
        if ($ret === false) {
            $this->setSuccess(false);
            return $response->withStatus(500);
        } else {
            $this->setSuccess(true);
            return $response;
        }
    }

    #[\Override]
    public function results(): array
    {
        return [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    #[\Override]
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['base_path'];
    }
}
