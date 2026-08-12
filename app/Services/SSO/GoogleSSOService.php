<?php

namespace App\Services\SSO;

use App\Services\SSO\Contracts\SSOServiceInterface;

class GoogleSSOService extends AbstractSSOService implements SSOServiceInterface
{
    protected function driver(): string
    {
        return 'google';
    }
}
