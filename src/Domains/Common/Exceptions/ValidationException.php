<?php

namespace Domains\Common\Exceptions;

use Domains\Common\Validation\Message;
use Domains\Common\Validation\MessageBag;

class ValidationException extends \Exception
{
    private MessageBag $messageBag;

    public function __construct()
    {
        parent::__construct('Validation exception');
        $this->messageBag = new MessageBag();
    }

    /**
     * @param string $key
     * @param string $errorMsg
     * @param array $arguments
     * @return $this
     */
    public function addError(string $key, string $errorMsg, array $arguments=[]): self
    {
        $this->messageBag->add($key, new Message($errorMsg, $arguments));
        return $this;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !$this->messageBag()->isEmpty();
    }

    /**
     * @return MessageBag
     */
    public function messageBag(): MessageBag
    {
        return $this->messageBag;
    }

    /**
     * Get errors
     * @return array
     */
    public function errors(): array
    {
        return $this->messageBag()->toArray();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $readable = '';
        $errorsCount = count($this->messageBag()->toArray());
        $i=1;

        foreach($this->messageBag()->toArray() as $key => $val) {
            $readable .= $key . ' ';
            $readable .=  implode("\r\n",
                array_map(function ($entry) {
                    if($entry['arguments']) {
                        return $entry['message'] . ": " . implode(',', $entry['arguments']);
                    }
                    return $entry['message'];
                }, $val)
            );

            if ($i<$errorsCount) {
                $readable .= "\r\n";
            }
            $i++;
        }

        return $readable;
    }
}
