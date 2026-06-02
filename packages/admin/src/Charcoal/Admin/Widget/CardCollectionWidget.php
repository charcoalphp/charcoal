<?php

namespace Charcoal\Admin\Widget;

use Charcoal\Model\ModelInterface;
use Charcoal\Translator\Translation;

/**
 * Class CardCollectionWidget
 */
class CardCollectionWidget extends TableWidget
{
    /**
     * @var integer $numColumns
     */
    protected $numColumns;

    /**
     * @var string $cardTemplate
     */
    protected $cardTemplate;

    /**
     * @var boolean $showFooterChip
     */
    protected $showFooterChip = true;

    /**
     * @var Translation|string $chipTitle
     */
    protected $chipTitle;

    /**
     * @return integer
     */
    public function numColumns()
    {
        return $this->numColumns;
    }

    /**
     * @param integer $numColumns NumColumns for CardCollectionWidget.
     */
    public function setNumColumns($numColumns): static
    {
        $this->numColumns = $numColumns;

        return $this;
    }

    public function bsColRatio(): float|int
    {
        return abs(12 / ($this->numColumns() ?: 12));
    }

    /**
     * @return string
     */
    public function cardTemplate()
    {
        return $this->cardTemplate;
    }

    /**
     * @param string $cardTemplate CardTemplate for CardCollectionWidget.
     */
    public function setCardTemplate($cardTemplate): static
    {
        $this->cardTemplate = $cardTemplate;

        return $this;
    }

    /**
     * @return \Generator
     */
    public function objectCardRow()
    {
        foreach ($this->objectRows() as $obj) {
            $this->setDynamicTemplate('widget_template', $this->cardTemplate());
            yield $obj;
        }
    }

    /**
     * Filter the object before its assigned to the row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  ModelInterface $object           The current row's object.
     * @param  array          $objectProperties The $object's display properties.
     */
    #[\Override]
    protected function parseObjectRow(ModelInterface $object, array $objectProperties): array
    {
        $row = $this->parseCollectionObjectRow($object, $objectProperties);
        $objProps = $row['objectProperties'];
        array_walk($objProps, function (array $value) use (&$row): void {
            $row['objectProperties'][$value['ident']] = $value['val'];

            if (!method_exists($row['object'], 'isChipSuccess')) {
                $row['isChipSuccess'] = $this->isChipSuccess($row['object']);
            }
        });

        return $row;
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     */
    #[\Override]
    public function widgetDataForJs(): array
    {
        return array_merge_recursive(
            parent::widgetDataForJs(),
            [
                'card_template' => $this->cardTemplate(),
                'num_columns' => $this->numColumns()
            ]
        );
    }

    /**
     * @param ModelInterface $object The model to determine success for.
     * @return boolean
     */
    public function isChipSuccess(ModelInterface $object)
    {
        $method = [ $object, 'isViewable' ];
        if (is_callable($method)) {
            return call_user_func($method);
        }

        if (isset($object['isViewable'])) {
            return (bool)$object['isViewable'];
        }

        if (isset($object['active'])) {
            return (bool)$object['active'];
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function showFooterChip()
    {
        return $this->showFooterChip;
    }

    /**
     * @param boolean $showFooterChip ShowFooterChip for CardCollectionWidget.
     */
    public function setShowFooterChip($showFooterChip): static
    {
        $this->showFooterChip = $showFooterChip;

        return $this;
    }

    /**
     * @return Translation|string
     */
    public function chipTitle()
    {
        if (empty($this->chipTitle)) {
            return $this->defaultChipTitle();
        }

        return $this->chipTitle;
    }

    private function defaultChipTitle(): string
    {
        return $this->translator()->translate('Active');
    }

    /**
     * @param Translation|string $chipTitle ChipTitle for CardCollectionWidget.
     */
    public function setChipTitle($chipTitle): static
    {
        $this->chipTitle = $this->translator()->translation($chipTitle);

        return $this;
    }
}
