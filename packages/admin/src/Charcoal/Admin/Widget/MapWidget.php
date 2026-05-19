<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;
// From 'charcoal-core'
use Charcoal\Model\ModelInterface;
// From 'charcoal-ui'
use Charcoal\Ui\FormGroup\FormGroupInterface;
use Charcoal\Ui\FormGroup\FormGroupTrait;
// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;

/**
 * Map Widget displays a google map widget, with UI to add polygons, lines and points.
 *
 * Most of this widget functionalities are in javascript.
 */
class MapWidget extends AdminWidget implements FormGroupInterface
{
    use FormGroupTrait;

    /**
     * The related model.
     *
     * @var ModelInterface
     */
    protected $obj;

    /**
     * The styles for the marker.
     *
     * @var array|null
     */
    private $styles;

    /**
     * Latitude
     */
    private ?float $lat = null;

    /**
     * Lontitude
     */
    private ?float $lng = null;

    /**
     * The $obj property key for the latitude.
     */
    private ?string $latProperty = 'lat';

    /**
     * The $obj property key for the longitude.
     */
    private ?string $lngProperty = 'lng';

    public function widgetType(): string
    {
        return 'charcoal/admin/widget/map';
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     */
    #[\Override]
    public function widgetDataForJs(): array
    {
        return [
            'obj_id'   => null,
            'obj_type' => null,
            'coords'   => $this->coords(),
        ];
    }

    /**
     * Set the $obj property key for the latitude.
     *
     * @param  string|null $key The latitude property ident.
     * @throws InvalidArgumentException If the property key is not a string.
     */
    public function setLatProperty($key): static
    {
        if ($key === null) {
            $this->latProperty = $key;
            return $this;
        }

        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'The "lat_property" must be a string'
            );
        }

        $this->latProperty = $key;
        return $this;
    }

    /**
     * Retrieve the $obj property key for the latitude.
     */
    public function latProperty(): ?string
    {
        return $this->latProperty;
    }

    /**
     * Set the $obj property key for the longitude.
     *
     * @param  string|null $key The longitude property key.
     * @throws InvalidArgumentException If the property key is not a string.
     */
    public function setLngProperty($key): static
    {
        if ($key === null) {
            $this->lngProperty = $key;
            return $this;
        }

        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'The "lng_property" must be a string'
            );
        }

        $this->lngProperty = $key;
        return $this;
    }

    /**
     * Set the $obj property key for the longitude.
     *
     * @param  string $key The longitude property key.
     */
    #[\Deprecated(message: 'In favour of {@see self::setLngProperty()}.')]
    public function setLonProperty($key): static
    {
        $this->logger->warning(
            'MapWidget "lon_property" is deprecated. Use "lng_property".',
            [ 'package' => 'charcoal/admin' ]
        );
        $this->setLngProperty($key);
        return $this;
    }

    /**
     * Retrieve the $obj property key for the longitude.
     */
    public function lngProperty(): ?string
    {
        return $this->lngProperty;
    }

    /**
     * Set the latitude for the widget.
     *
     * @param  float $coord The latitude of a location.
     * @throws InvalidArgumentException If the longitude is not a number.
     */
    public function setLat($coord): static
    {
        if ($coord === null) {
            $this->lat = $coord;
            return $this;
        }

        if (!is_numeric($coord)) {
            throw new InvalidArgumentException(
                'The "lat" must be a number'
            );
        }

        $this->lat = (float)$coord;

        return $this;
    }

    /**
     * Retrieve the latitude from the object's latitude property.
     *
     * @return float|null
     */
    public function lat()
    {
        if ($this->lat !== null) {
            return $this->lat;
        }

        if (!$this->hasObj() || !$this->latProperty()) {
            return null;
        }

        return $this->obj()[$this->latProperty()];
    }

    /**
     * Set the longitude of the object's geolocation.
     *
     * @param  float $coord The longitude of a location.
     * @throws InvalidArgumentException If the longitude is not a number.
     */
    public function setLng($coord): static
    {
        if ($coord === null) {
            $this->lng = $coord;
            return $this;
        }

        if (!is_numeric($coord)) {
            throw new InvalidArgumentException(
                'The "lng" must be a number'
            );
        }

        $this->lng = (float)$coord;

        return $this;
    }

    /**
     * Retrieve the longitude from the object's longitude property.
     *
     * @return float|null
     */
    public function lng()
    {
        if ($this->lng !== null) {
            return $this->lng;
        }

        if (!$this->hasObj() || !$this->lngProperty()) {
            return null;
        }

        return $this->obj()[$this->lngProperty()];
    }

    /**
     * Set the $obj property key for the longitude.
     *
     * @return self
     */
    #[\Deprecated(message: 'In favour of {@see self::lng()}.')]
    public function lon()
    {
        $this->logger->warning(
            'MapWidget "lon" is deprecated. Use "lng".',
            [ 'package' => 'charcoal/admin' ]
        );
        return $this->lng();
    }

    /**
     * Retrieve the latitude / longitude as an array.
     *
     * @return float[]|null
     */
    public function latLng(): ?array
    {
        $lat = $this->lat();
        $lng = $this->lng();

        if ($lat && $lng) {
            return [ $lat, $lng ];
        } else {
            return null;
        }
    }

    /**
     * Retrieve the latitude / longitude as an associative array.
     *
     * @return float[]|null
     */
    public function coords(): ?array
    {
        $lat = $this->lat();
        $lng = $this->lng();

        if ($lat && $lng) {
            return [ 'lat' => $lat, 'lng' => $lng ];
        } else {
            return null;
        }
    }

    /**
     * Determine if the widget has a related object.
     *
     * @return boolean
     */
    public function hasObj()
    {
        if ($this->obj === null) {
            try {
                $this->obj();
            } catch (InvalidArgumentException) {
                return false;
            }
        }

        return !empty($this->obj);
    }

    /**
     * Retrieve the widget's related object.
     *
     * @throws InvalidArgumentException If the object type or ID are invalid or missing.
     * @return ModelInterface
     */
    public function obj()
    {
        if ($this->obj === null) {
            $objId   = filter_input(INPUT_GET, 'obj_id', FILTER_SANITIZE_STRING);
            $objType = filter_input(INPUT_GET, 'obj_type', FILTER_SANITIZE_STRING);
            if ($objId && $objType) {
                $obj = $this->modelFactory()->create($objType);
                $obj->load($objId);

                $this->obj = $obj;
            } else {
                throw new InvalidArgumentException('Missing Object Type or ID');
            }
        }

        return $this->obj;
    }
}
