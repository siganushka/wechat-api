<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

class ConfigurationManager
{
    private string $defaultName;

    /**
     * @var array<string, Configuration>
     */
    private array $configurations = [];

    public function __construct(string $defaultName = 'default')
    {
        $this->defaultName = $defaultName;
    }

    public function getDefaultName(): string
    {
        return $this->defaultName;
    }

    /**
     * @return array<string, Configuration>
     */
    public function all(): array
    {
        return $this->configurations;
    }

    public function has(string $name): bool
    {
        return \array_key_exists($name, $this->configurations);
    }

    public function get(string $name): Configuration
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('Configuration "%s" for "%s" does not exist. Defined are: "%s".', $name, static::class, implode('", "', array_keys($this->configurations))));
        }

        return $this->configurations[$name];
    }

    public function set(string $name, Configuration $configuration): self
    {
        if ($this->has($name)) {
            throw new \InvalidArgumentException(sprintf('Configuration "%s" for "%s" already exists.', $name, static::class));
        }

        $this->configurations[$name] = $configuration;

        return $this;
    }
}
