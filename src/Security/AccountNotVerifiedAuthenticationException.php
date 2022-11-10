<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AccountNotVerifiedAuthenticationException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Please verify your account before logging in.';
    }
}
{

}