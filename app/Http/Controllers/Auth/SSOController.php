<?php

namespace App\Http\Controllers\Auth;

use App\Services\SSO\Contracts\SSOServiceInterface;
use App\Http\Controllers\Controller;

class SSOController extends Controller
{
    public function __construct(protected SSOServiceInterface $service) {}

    public function redirect()
    {
        return $this->service->redirect();
    }

    public function callback()
    {
        return $this->service->callback();
    }
}
