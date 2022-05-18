<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Message\Template;

class DataItem
{
    private string $index;
    private string $value;
    private ?string $color;

    public function __construct(string $index, string $value, string $color = null)
    {
        $this->index = $index;
        $this->value = $value;
        $this->color = $color;
    }

    public function getIndex(): string
    {
        return $this->index;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @return array{ value: string, color?: string }
     */
    public function toArray(): array
    {
        return null === $this->color
            ? ['value' => $this->value]
            : ['value' => $this->value, 'color' => $this->color];
    }
}
