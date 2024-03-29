<?php

namespace CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Events;

/**
 * Class EventEmitter
 *
 * @package CleverReach\CleverReachIntegration\IntegrationCore\Infrastructure\Utility\Events
 */
abstract class EventEmitter
{
    /**
     * List of callbacks to invoke when event occurs.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Registers event handler for a given event
     *
     * @param string $eventClass Fully qualified class name of desired event
     * @param callable $handler Callback to invoke when event occurs. Observable will pass observed event
     * instance as a handler parameter.
     */
    public function when($eventClass, callable $handler)
    {
        $this->handlers[$eventClass][] = $handler;
    }

    /**
     * Fires requested event by calling all its registered handlers
     *
     * @param Event $event Event to fire
     */
    protected function fire(Event $event)
    {
        $eventClass = get_class($event);
        if (empty($this->handlers[$eventClass])) {
            return;
        }

        foreach ($this->handlers[$eventClass] as $handler) {
            call_user_func($handler, $event);
        }
    }
}
