<?php

declare(strict_types=1);

namespace Charcoal\Admin\Property\Input;

use InvalidArgumentException;
use Charcoal\Admin\Property\AbstractPropertyInput;
use UnexpectedValueException;

/**
 * Multi-Line Text Input Property
 */
class TextareaInput extends AbstractPropertyInput
{
    private ?int $cols = null;

    private ?int $rows = null;

    private int $minLength = 0;

    private int $maxLength = 0;

    /**
     * @param integer $cols The number of columns (html cols attribute).
     * @throws InvalidArgumentException  If the argument is not a number.
     * @return Text Chainable
     */
    public function setCols($cols): static
    {
        if (!is_numeric($cols)) {
            throw new InvalidArgumentException(
                'Columns must to be a number'
            );
        }
        $this->cols = (int)$cols;
        return $this;
    }

    /**
     * @return integer
     */
    public function cols(): ?int
    {
        return $this->cols;
    }

    /**
     * @param integer $rows The number of rows (html rows attribute).
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setRows($rows): static
    {
        if (!is_numeric($rows)) {
            throw new InvalidArgumentException(
                'Rows must to be a number'
            );
        }
        $this->rows = (int)$rows;
        return $this;
    }

    /**
     * @return integer
     */
    public function rows(): ?int
    {
        return $this->rows;
    }

    /**
     * @param integer $minLength The min length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMinLength($minLength): static
    {
        if (!is_numeric($minLength)) {
            throw new InvalidArgumentException(
                'Minimum length needs to be an integer'
            );
        }
        $this->minLength = (int)$minLength;
        return $this;
    }

    public function minLength(): int
    {
        return $this->minLength;
    }

    /**
     * @param integer $maxLength The max length.
     * @throws InvalidArgumentException If the argument is not a number.
     * @return Text Chainable
     */
    public function setMaxLength($maxLength): static
    {
        if (!is_numeric($maxLength)) {
            throw new InvalidArgumentException(
                'Maximum length needs to be an integer'
            );
        }
        $this->maxLength = (int)$maxLength;
        return $this;
    }

    #[\Override]
    public function getInputValOptions(): array
    {
        return [
            'pretty' => true,
        ];
    }

    public function maxLength(): int
    {
        return $this->maxLength;
    }
}
