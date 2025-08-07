<?php

namespace Charcoal\Admin\Action\System\StaticWebsite;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use DI\Container;
use Charcoal\Admin\AdminAction;
use Psr\Container\ContainerInterface;

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

    /**
     * @return array
     */
    public function results()
    {
        $ret = [
            'success'   => $this->success(),
            'feedbacks' => $this->feedbacks()
        ];

        return $ret;
    }

    /**
     * @param Container $container DI Container.
     * @return void
     */
    protected function setDependencies(ContainerInterface $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container->get('config')['base_path'];
    }
}
