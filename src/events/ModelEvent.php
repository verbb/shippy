<?php
namespace verbb\shippy\events;

use Symfony\Contracts\EventDispatcher\Event;

class ModelEvent extends Event
{
    // Public Methods
    // =========================================================================

    public function __construct($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $name => $value) {
                $this->$name = $value;
            }
        }
    }
}