<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Message\Template;

class Data
{
    /**
     * @var DataItem[]
     */
    private array $items = [];

    /**
     * @param DataItem[] $items
     */
    public function __construct(iterable $items = [])
    {
        foreach ($items as $item) {
            if ($item instanceof DataItem) {
                $this->addItem($item);
            }
        }
    }

    public function addItem(DataItem $item): self
    {
        $this->items[$item->getIndex()] = $item;

        return $this;
    }

    public function hasItem(string $index): bool
    {
        return \array_key_exists($index, $this->items);
    }

    public function removeItem(string $index): void
    {
        unset($this->items[$index]);
    }

    /**
     * @return array<string, array{ value: string, color?: string }>
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->items as $index => $item) {
            $data[$index] = $item->toArray();
        }

        return $data;
    }
}
