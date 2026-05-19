<?php

namespace Charcoal\Cms\Service\Loader;

use DateTime;
// From 'charcoal-core'
use Charcoal\Loader\CollectionLoader;
// From 'charcoal-cms'
use Charcoal\Cms\NewsInterface;

/**
 * News Loader
 */
class NewsLoader extends AbstractLoader
{
    /**
     * @var string $median The median between upcoming and archive entries.
     */
    protected $median;

    /**
     * @var object $objType The object to load.
     */
    #[\Override]
    protected $objType;

    /**
     * @return CollectionLoader
     */
    public function all()
    {
        $proto = $this->newsProto();
        $loader = $this->collectionLoader()->setModel($proto);
        $loader->addFilter('active', true);

        return $loader;
    }

    /**
     * @return NewsInterface
     */
    public function newsProto()
    {
        return $this->modelFactory()->get($this->objType());
    }

    /**
     * @return CollectionLoader
     */
    public function published()
    {
        $now = new DateTime();
        $loader = $this->all();
        $loader->addFilter('publishDate', $now->format('Y-m-d H:i:s'), [ 'operator' => '<=' ])
            ->addFilter('expiryDate', $now->format('Y-m-d H:i:s'), [ 'operator' => '>=' ]);

        return $loader;
    }

    /**
     * @return CollectionLoader
     */
    public function expired()
    {
        $now = new DateTime();
        $loader = $this->all();
        $loader->addFilter('publishDate', $now->format('Y-m-d H:i:s'), [ 'operator' => '<=' ])
            ->addFilter('expiryDate', $now->format('Y-m-d H:i:s'), [ 'operator' => '<=' ]);

        return $loader;
    }

    /**
     * Fetch upcoming entries based on the median or now.
     * @return CollectionLoader
     */
    public function upcoming()
    {
        return $this->published();
    }

    /**
     * Fetch upcoming entries based on the median or now.
     * @return CollectionLoader
     */
    public function archive()
    {
        return $this->expired();
    }

    /**
     * @return mixed
     */
    public function median()
    {
        return $this->median;
    }

    /**
     * @return object
     */
    public function objType()
    {
        return $this->objType;
    }

    /**
     * @param string $median The median between upcoming and archive.
     */
    public function setMedian($median): static
    {
        $this->median = $median;

        return $this;
    }

    /**
     * @param object $objType The object type.
     */
    public function setObjType($objType): static
    {
        $this->objType = $objType;

        return $this;
    }
}
