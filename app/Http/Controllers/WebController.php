<?php

namespace App\Http\Controllers;


use App\Services\WebService;

class WebController extends Controller
{
    protected $webService;

    public function __construct(WebService $webService)
    {
        $this->webService = $webService;
    }
}
