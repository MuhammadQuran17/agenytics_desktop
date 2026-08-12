<?php

namespace App\Services\SSO\Contracts;

interface SSOServiceInterface
{
    public function redirect();

    public function callback();
}
