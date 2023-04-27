<?php

namespace Domains\Common\Notification;

use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\Messages\SimpleMessage;

abstract class Mail extends Mailable implements Renderable
{
    private SimpleMessage $message;
    private ?string $sendTo;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->message = new SimpleMessage();
        $this->message->success();
        $this->view = 'email';
    }

    /**
     * @param string $email
     * @return $this
     */
    protected function setSendTo(string $email): self
    {
        $this->sendTo = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function sendTo(): ?string
    {
        return $this->sendTo;
    }

    /**
     * @return SimpleMessage
     */
    public function message(): SimpleMessage
    {
        return $this->message;
    }

    /**
     * @param string $text
     * @param string $url
     * @return $this
     */
    public function action(string $text, string $url): self
    {
        $this->message()->action($text, $url);
        return $this;
    }

    /**
     * @param string $line
     * @return $this
     */
    public function line(string $line): self
    {
        $this->message()->line($line);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->message()->toArray();
    }

    /**
     * Render the mail notification message into an HTML string.
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function render(): string
    {
        $markdown = Container::getInstance()->make(Markdown::class);

        return $markdown->theme($markdown->getTheme())
            ->render($this->view, $this->toArray());
    }

    /**
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function build(): string
    {
        $this->viewData = $this->toArray();
        return $this->render();
    }
}
