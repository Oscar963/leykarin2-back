<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComplaintRequest;
use App\Http\Resources\ComplaintResource;
use App\Mail\ComplaintRegistered;
use App\Mail\DependenceComplaintNotification;
use App\Services\WebService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class WebController extends Controller
{
    protected $webService;

    public function __construct(WebService $webService)
    {
        $this->webService = $webService;
    }
}
