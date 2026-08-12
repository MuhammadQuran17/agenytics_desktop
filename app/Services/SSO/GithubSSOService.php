<?php

namespace App\Services\SSO;

use App\Services\SSO\Contracts\SSOServiceInterface;

class GithubSSOService extends AbstractSSOService implements SSOServiceInterface
{
    protected function driver(): string
    {
        return 'github';
    }
}
