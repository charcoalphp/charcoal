<?php

namespace Charcoal\Cms\Route;

use Exception;
// From Pimple
use Pimple\Container;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;
// From 'charcoal-app'
use Charcoal\App\Route\TemplateRoute;
// From 'charcoal-object'
use Charcoal\Object\RoutableInterface;
// From 'charcoal-cms'
use Charcoal\Cms\NewsInterface;

/**
 * News Route Handler
 */
class NewsRoute extends TemplateRoute
{
    use TranslatorAwareTrait;

    /**
     * URI path.
     */
    private string $path;

    /**
     * The news entry matching the URI path.
     *
     * @var NewsInterface|RoutableInterface
     */
    private $news;

    /**
     * The news entry model.
     */
    private string $objType = 'charcoal/cms/news';

    /**
     * @param array $data Class depdendencies.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->path = ltrim((string) $data['path'], '/');
    }

    /**
     * Determine if the URI path resolves to an object.
     *
     * @param  Container $container A DI (Pimple) container.
     */
    public function pathResolvable(Container $container): bool
    {
        $news = $this->loadNewsFromPath($container);
        return ($news instanceof NewsInterface) && $news->id();
    }

    /**
     * @param  Container         $container A DI (Pimple) container.
     * @param  RequestInterface  $request   A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response  A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    #[\Override]
    public function __invoke(
        Container $container,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $config = $this->config();

        $news = $this->loadNewsFromPath($container);
        if ($news === null) {
            return $response->withStatus(404);
        }

        $templateIdent      = (string)$news['templateIdent'];
        $templateController = (string)$news['templateIdent'];

        if ($templateController === '' || $templateController === '0') {
            $container['logger']->warning(sprintf(
                '[%s] Missing template controller on model [%s] for ID [%s]',
                static::class,
                $news::class,
                $news['id']
            ));
            return $response->withStatus(500);
        }

        $templateFactory = $container['template/factory'];

        $template = $templateFactory->create($templateController);
        $template->init($request);

        // Set custom data from config.
        $template->setData($config['template_data']);
        $template->setNews($news);

        $templateContent = $container['view']->render($templateIdent, $template);
        if ($templateContent === $templateIdent || $templateContent === '') {
            $container['logger']->warning(sprintf(
                '[%s] Missing or bad template identifier on model [%s] for ID [%s]',
                static::class,
                $news::class,
                $templateIdent
            ));
            return $response->withStatus(500);
        }

        $response->write($templateContent);

        return $response;
    }

    /**
     * @todo   Add support for `@see setlocale()`; {@see GenericRoute::setLocale()}
     * @param  Container $container Pimple DI container.
     * @return NewsInterface|null
     */
    protected function loadNewsFromPath(Container $container)
    {
        if ($this->news === null) {
            $config  = $this->config();
            $objType = ($config['obj_type'] ?? $this->objType);

            try {
                $model = $container['model/factory']->create($objType);
                $langs = $container['translator']->availableLocales();
                $lang  = $model->loadFromL10n('slug', $this->path, $langs);

                if ($lang) {
                    $container['translator']->setLocale($lang);
                }

                if ($model->id()) {
                    $this->news = $model;
                    return $model;
                }
            } catch (Exception) {
                $container['logger']->debug(sprintf(
                    '[%s] Unable to load model [%s] for path [%s]',
                    static::class,
                    $model::class,
                    $this->path
                ));
            }

            $this->news = false;
        }

        return $this->news ?: null;
    }
}
