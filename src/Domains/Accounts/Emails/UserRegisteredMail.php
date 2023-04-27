<?php

namespace Domains\Accounts\Emails;

use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\Auth\AuthToken;
use Domains\Common\Notification\Mail;

class UserRegisteredMail extends Mail
{
    /**
     * @param UserContract $user
     */
    public function __construct(UserContract $user)
    {
        parent::__construct();

        $expireAt = time() + 7*24*60*60; //+7 days
        $authToken = AuthToken::encodeFromUser($user, $expireAt, ['activate' => true]);

        $signInUrl = config('frontend.base_url') . config('frontend.sign_in');
        $signInUrl = str_replace(':token', $authToken->token(), $signInUrl);

        $this->line("You've been invited to join a team.");
        $this->action('Verify Email and Sign In', $signInUrl);
        $this->line('Thank you for using our application!');

        $this->setSendTo($user->email());
    }
}
