<?php
namespace verbb\shippy\models;

use Symfony\Component\EventDispatcher\EventDispatcher;
use JsonSerializable;

class Model implements JsonSerializable
{
    // Properties
    // =========================================================================

    private ?EventDispatcher $events = null;


    // Public Methods
    // =========================================================================

    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                $this->__set($name, $value);
            }
        }

        $this->init();
    }

    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);

        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
    }

    public function __set($name, $value)
    {
        $setter = 'set' . ucfirst($name);

        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
    }

    public function __isset($name)
    {
        $getter = 'get' . ucfirst($name);

        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    public function __unset($name)
    {
        $setter = 'set' . ucfirst($name);

        if (method_exists($this, $setter)) {
            $this->$setter(null);
        }
    }

    public function __debugInfo()
    {
        return $this->toArray();
    }

    public function __sleep(): array
    {
        // Ensure that any `serialize()` calls respect `toArray()`.
        return array_keys($this->toArray());
    }

    public function init(): void
    {
        $this->events = new EventDispatcher();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function displayName(): string
    {
        $classNameParts = explode('\\', static::class);
        return array_pop($classNameParts);
    }

    public function on(string $eventName, callable|array $listener, int $priority = 0): void
    {
        $this->events->addListener($eventName, $listener, $priority);
    }

    public function trigger(string $eventName, object $event): void
    {
        $this->events->dispatch($event, $eventName);
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['events']);

        return $vars;
    }
}