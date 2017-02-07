<?php

namespace Charcoal\Property;

use \InvalidArgumentException;
use \OutOfBoundsException;
use \UnexpectedValueException;

// From 'charcoal-image'
use \Charcoal\Image\ImageFactory;
use \Charcoal\Image\ImageInterface;

// From 'charccoal-translation'
use \Charcoal\Translator\Translation;

// Local namespace dependencies
use \Charcoal\Property\FileProperty;

/**
 * Image Property.
 *
 * The image property is a specialized file property that stores image file.
 */
class ImageProperty extends FileProperty
{
    const EFFECTS_EVENT_SAVE    = 'save';
    const EFFECTS_EVENT_NEVER   = 'never';
    const EFFECTS_EVENT_UPLOAD  = 'upload';
    const DEFAULT_APPLY_EFFECTS = self::EFFECTS_EVENT_SAVE;

    /**
     * One or more effects to apply on the image.
     *
     * @var array
     */
    private $effects = [];

    /**
     * Whether to apply any effects on the uploaded image.
     *
     * @var mixed
     */
    private $applyEffects = self::DEFAULT_APPLY_EFFECTS;

    /**
     * Store the image factory instance.
     *
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * The type of image processing engine.
     *
     * @var string
     */
    private $driverType;

    /**
     * @return string
     */
    public function type()
    {
        return 'image';
    }

    /**
     * Set an image factory.
     *
     * @param  ImageFactory $factory The image factory, to manipulate images.
     * @return self
     */
    protected function setImageFactory(ImageFactory $factory)
    {
        $this->imageFactory = $factory;

        return $this;
    }

    /**
     * Retrieve the image factory.
     *
     * @return ImageFactory
     */
    public function imageFactory()
    {
        if ($this->imageFactory === null) {
            $this->imageFactory = $this->createImageFactory();
        }

        return $this->imageFactory;
    }

    /**
     * Create an image factory.
     *
     * @return ImageFactory
     */
    protected function createImageFactory()
    {
        return new ImageFactory();
    }

    /**
     * Create an image.
     *
     * @return ImageInterface
     */
    protected function createImage()
    {
        return $this->imageFactory()->create($this->driverType());
    }

    /**
     * Set the name of the property's image processing driver.
     *
     * @param  string $type The processing engine.
     * @throws InvalidArgumentException If the drive type is not a string.
     * @return ImageProperty Chainable
     */
    private function setDriverType($type)
    {
        if (!is_string($type)) {
            throw new InvalidArgumentException(sprintf(
                'Image driver type must be a string, received %s',
                (is_object($type) ? get_class($type) : gettype($type))
            ));
        }

        $this->driverType = $type;

        return $this;
    }

    /**
     * Get the name of the property's image processing driver.
     *
     * @return string
     */
    protected function driverType()
    {
        if ($this->driverType === null) {
            return 'imagemagick';
        }

        return $this->driverType;
    }

    /**
     * Set whether effects should be applied.
     *
     * @param  mixed $event When to apply affects.
     * @throws OutOfBoundsException If the effects event does not exist.
     * @return ImageProperty Chainable
     */
    public function setApplyEffects($event)
    {
        if ($event === false) {
            $this->applyEffects = self::EFFECTS_EVENT_NEVER;
            return $this;
        }

        if ($event === null || $event === '') {
            $this->applyEffects = self::EFFECTS_EVENT_SAVE;
            return $this;
        }

        if (!in_array($event, $this->acceptedEffectsEvents())) {
            if (!is_string($event)) {
                $event = (is_object($event) ? get_class($event) : gettype($event));
            }
            throw new OutOfBoundsException(sprintf(
                'Unsupported image property event "%s" provided',
                $event
            ));
        }

        $this->applyEffects = $event;

        return $this;
    }

    /**
     * Determine if effects should be applied.
     *
     * @param  string|boolean $event A specific event to check or a global flag to set.
     * @throws OutOfBoundsException If the effects event does not exist.
     * @return mixed If an $event is provided, returns TRUE or FALSE if the property applies
     *     effects for the given event. Otherwise, returns the property's condition on effects.
     */
    public function applyEffects($event = null)
    {
        if ($event !== null) {
            if (!in_array($event, $this->acceptedEffectsEvents())) {
                if (!is_string($event)) {
                    $event = (is_object($event) ? get_class($event) : gettype($event));
                }
                throw new OutOfBoundsException(sprintf(
                    'Unsupported image property event "%s" provided',
                    $event
                ));
            }

            return $this->applyEffects === $event;
        }

        return $this->applyEffects;
    }

    /**
     * Retrieve the supported events where effects can be applied.
     *
     * @return array
     */
    public function acceptedEffectsEvents()
    {
        return [
            self::EFFECTS_EVENT_UPLOAD,
            self::EFFECTS_EVENT_SAVE,
            self::EFFECTS_EVENT_NEVER
        ];
    }

    /**
     * Set (reset, in fact) the image effects.
     *
     * @param array $effects The effects to set to the image.
     * @return ImageProperty Chainable
     */
    public function setEffects(array $effects)
    {
        $this->effects = [];
        foreach ($effects as $effect) {
            $this->addEffect($effect);
        }
        return $this;
    }

    /**
     * @param mixed $effect An image effect.
     * @return ImageProperty Chainable
     */
    public function addEffect($effect)
    {
        $this->effects[] = $effect;
        return $this;
    }

    /**
     * @return array
     */
    public function effects()
    {
        return $this->effects;
    }

    /**
     * @return array
     */
    protected function batchEffects()
    {
        $effects = $this->effects();
        $grouped = [];
        if ($effects) {
            $blueprint = [ 'effects' => [], 'save' => true, 'rename' => null, 'copy' => null ];
            $fxGroup   = $blueprint;
            foreach ($effects as $effect) {
                if (isset($effect['type']) && $effect['type'] === 'save') {
                    if (isset($effect['rename'])) {
                        $fxGroup['rename'] = $effect['rename'];
                    }
                    if (isset($effect['copy'])) {
                        $fxGroup['copy'] = $effect['copy'];
                    }

                    $grouped[] = $fxGroup;

                    $fxGroup = $blueprint;
                } else {
                    $fxGroup['effects'][] = $effect;
                }
            }
        }

        return $grouped;
    }

    /**
     * Process the property's effects on the given image(s).
     *
     * @param  mixed               $value   The target(s) to apply effects on.
     * @param  array               $effects The effects to apply on the target.
     * @param  ImageInterface|null $image   Optional. The image for processing.
     * @return mixed Returns the given images. Depending on the effects applied,
     *     certain images might be renamed.
     */
    public function processEffects($value, array $effects = null, ImageInterface $image = null)
    {
        $value = $this->parseVal($value);

        if ($value instanceof Translation) {
            $value = $value->data();
        }

        if ($effects === null) {
            $effects = $this->batchEffects();
        }

        if ($effects) {
            if ($image === null) {
                $image = $this->createImage();
            }

            if (is_array($value)) {
                foreach ($value as &$val) {
                    $val = $this->processEffectsOne($val, $effects, $image);
                }
            } else {
                $value = $this->processEffectsOne($value, $effects, $image);
            }
        }

        return $value;
    }

    /**
     * Process the property's effects on the given image.
     *
     * @param  string              $value   The target to apply effects on.
     * @param  array               $effects The effects to apply on the target.
     * @param  ImageInterface|null $image   Optional. The image for processing.
     * @throws InvalidArgumentException If the $value is not a string.
     * @return mixed Returns the processed target or NULL.
     */
    private function processEffectsOne($value, array $effects = null, ImageInterface $image = null)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'Target image must be a string, received %s',
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }

        if ($image === null) {
            $image = $this->createImage();
        }

        if ($effects === null) {
            $effects = $this->batchEffects();
        }

        if ($effects) {
            $basePath = $this->basePath();

            // @todo Save original file here
            $image->open($value);

            $target = null;
            if (null !== parse_url($value, PHP_URL_HOST)) {
                $target = $this->uploadPath().pathinfo($value, PATHINFO_BASENAME);
            }

            foreach ($effects as $fxGroup) {
                if ($fxGroup['save']) {
                    $rename = $fxGroup['rename'];
                    $copy   = $fxGroup['copy'];

                    $doRename = false;
                    $doCopy   = false;
                    $doSave   = true;

                    if ($rename || $copy) {
                        if ($copy) {
                            $copy   = $this->renderFileRenamePattern(($target ?: $value), $copy);
                            $exists = $this->fileExists($basePath.$copy);
                            $doCopy = ($copy && ($this->overwrite() || !$exists));
                        }

                        if ($rename) {
                            $value    = $this->renderFileRenamePattern(($target ?: $value), $rename);
                            $exists   = $this->fileExists($basePath.$value);
                            $doRename = ($value && ($this->overwrite() || !$exists));
                        }

                        $doSave = ($doCopy || $doRename);
                    }

                    if ($doSave) {
                        if ($fxGroup['effects']) {
                            $image->setEffects($fxGroup['effects']);
                            $image->process();
                        }

                        if ($rename || $copy) {
                            if ($doCopy) {
                                $image->save($copy);
                            }

                            if ($doRename) {
                                $image->save($value);
                            }
                        } else {
                            $image->save($target);
                        }
                    }
                }
            }
        }

        return $value;
    }

    /**
     * @return array
     */
    public function acceptedMimetypes()
    {
        return [
            'image/gif',
            'image/jpg',
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/svg+xml',
            'image/webp'
        ];
    }

    /**
     * Generate the file extension from the property's value.
     *
     * @param  string $file The file to parse.
     * @return string The extension based on the MIME type.
     */
    public function generateExtension($file = null)
    {
        if (is_string($file)) {
            if (in_array($file, $this->acceptedMimetypes())) {
                $mime = $file;
            } else {
                $mime = $this->mimetypeFor($file);
            }
        } else {
            $mime = $this->mimetype();
        }

        switch ($mime) {
            case 'image/gif':
                return 'gif';

            case 'image/jpg':
            case 'image/jpeg':
            case 'image/pjpeg':
                return 'jpg';

            case 'image/png':
                return 'png';

            case 'image/svg+xml':
                $ext = 'svg';
                break;

            case 'image/webp':
                $ext = 'webp';
                break;
        }
    }

    /**
     * @param mixed $val The value, at time of saving.
     * @return mixed
     */
    public function save($val)
    {
        $val = parent::save($val);

        if ($this->applyEffects('save')) {
            $val = $this->processEffects($val);
        }

        return $val;
    }

    /**
     * Apply effects to the uploaded data URI(s).
     *
     * @see    FileProperty::fileUpload()
     * @param  string $fileData The file data, raw.
     * @return string
     */
    public function dataUpload($fileData)
    {
        $target = parent::dataUpload($fileData);

        if ($this->applyEffects('upload')) {
            $target = $this->processEffects($target);
        }

        return $target;
    }

    /**
     * Apply effects to the uploaded file(s).
     *
     * @see    FileProperty::fileUpload()
     * @param  array $fileData The file data to upload.
     * @return string
     */
    public function fileUpload(array $fileData)
    {
        $target = parent::fileUpload($fileData);

        if ($this->applyEffects('upload')) {
            $target = $this->processEffects($target);
        }

        return $target;
    }
}
