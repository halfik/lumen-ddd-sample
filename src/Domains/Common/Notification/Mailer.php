<?php

namespace Domains\Common\Notification;

use Illuminate\Mail\MailManager;
use Illuminate\Mail\SentMessage;

class Mailer
{
    private MailManager $mailer;

    /**
     * Initialize mailer
     */
    public function __construct()
    {
        $this->mailer = app('mail.manager');
    }

    /**
     * @param Mail $mail
     * @return bool
     */
    public function send(Mail $mail): bool
    {
        /** @var SentMessage|null $result */
        $result = $this->mailer->to($mail->sendTo())->send($mail);
        return !is_null($result);
    }
}
