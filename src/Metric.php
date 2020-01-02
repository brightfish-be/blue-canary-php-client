<?php

namespace Brightfish\BlueCanary;

use Brightfish\BlueCanary\Exceptions\MetricException;

/**
 * Metric class.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Metric
{
    /** @var string */
    protected $key = '';

    /** @var int */
    protected $value = 0;

    /** @var string|null */
    protected $unit;

    /** @var string */
    protected $type = 'float';

    /**
     * Metric constructor.
     * @param string $key
     * @param float $value
     * @param string|null $unit
     * @param string $cast
     * @throws MetricException
     */
    public function __construct(string $key, float $value, ?string $unit = null, string $cast = 'float')
    {
        $this->setKey($key);
        $this->setType($cast);
        $this->setValue($value);
        $this->setUnit($unit);
    }

    /**
     * @param string $key
     * @throws MetricException
     */
    protected function setKey(string $key): void
    {
        if (!$key || strlen($key) > 255) {
            throw new MetricException('The length of a metric key must be between 1 and 255 characters.');
        }

        $this->key = $key;
    }

    /**
     * @param string $type
     * @throws MetricException
     */
    protected function setType(string $type = 'float'): void
    {
        if ($type !== 'float' && $type !== 'int' && $type !== 'integer') {
            throw new MetricException('A metric can only be a float or an int.');
        }

        $this->type = $type !== 'integer' ? $type : 'int';
    }

    /**
     * @param float $value
     */
    protected function setValue(float $value): void
    {
        $this->value = $value;
    }

    /**
     * @param string|null $unit
     * @throws MetricException
     */
    protected function setUnit(?string $unit = null): void
    {
        if (strlen($unit) > 10) {
            throw new MetricException('The unit can only be 10 characters long.');
        }

        $this->unit = $unit ?: null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'value' => $this->type === 'int' ? (int)$this->value : $this->value,
            'unit' => $this->unit,
        ];
    }
}
