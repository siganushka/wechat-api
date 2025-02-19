<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Message;

class Template
{
    private string $id;
    private array $data = [];

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function addData(string $key, string $value): self
    {
        $this->data[$key] = compact('value');

        return $this;
    }

    public function hasData(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    public function removeData(string $key): void
    {
        unset($this->data[$key]);
    }
}
