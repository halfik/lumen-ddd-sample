<?php

namespace Domains\Common\Validation;

use Countable;

class MessageBag implements Countable
{
    /**
     * All the registered messages.
     */
    protected array $messages = [];

    /**
     * Add a message to the message bag.
     *
     * @param  string  $key
     * @param  Message  $message
     * @return self
     */
    public function add(string $key, Message $message): self
    {
        $this->messages[$key][] = $message;
        return $this;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->messages());
    }

    /**
     * Determine if the message bag has any messages.
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * Get the instance as an array.
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        /**
         * @var string $key
         * @var array|Message[] $messages
         */
        foreach ($this->messages() as $key => $messages) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = [];
            }

            foreach ($messages As $msg) {
                $result[$key][] = $msg->toArray();
            }
        }

        return $result;
    }
}
