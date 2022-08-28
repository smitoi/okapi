<?php

namespace App\Http\Controllers\Okapi;

use App\Http\Controllers\Controller;
use App\Services\DocumentationService;
use Illuminate\Support\Facades\File;
use JsonException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentationController extends Controller
{
    protected const JSON_FILE_PATH = 'openapi.json';

    protected DocumentationService $documentationService;

    public function __construct(DocumentationService $documentationService)
    {
        $this->documentationService = $documentationService;
    }

    /**
     * @throws JsonException
     */
    public function __invoke(): BinaryFileResponse
    {
        $path = self:: JSON_FILE_PATH;
        File::put(public_path($path), json_encode($this->documentationService->generateDocumentation(), JSON_THROW_ON_ERROR));
        return response()->download(public_path($path));
    }
}
