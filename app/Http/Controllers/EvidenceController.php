<?php

namespace App\Http\Controllers;

use App\Models\Evidence;
use App\Services\EvidenceService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EvidenceController extends Controller
{
    protected $evidenceService;

    public function __construct(EvidenceService $evidenceService)
    {
        $this->evidenceService = $evidenceService;
    }

    public function download(int $id): BinaryFileResponse
    {
        return $this->evidenceService->downloadFile($id);
    }
}
