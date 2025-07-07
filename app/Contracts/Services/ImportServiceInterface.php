<?php

namespace App\Contracts\Services;

use Illuminate\Http\UploadedFile;

interface ImportServiceInterface
{
    /**
     * Process file import with full validation and security checks
     *
     * @param UploadedFile $file
     * @param int $userId
     * @return \App\DTOs\Import\ImportResultDTO
     * @throws \App\Exceptions\Import\RateLimitExceededException
     * @throws \App\Exceptions\Import\FileValidationException
     * @throws \App\Exceptions\Import\ImportProcessingException
     */
    public function processImport(UploadedFile $file, int $userId): \App\DTOs\Import\ImportResultDTO;

    /**
     * Generate file preview for user confirmation
     *
     * @param UploadedFile $file
     * @return array
     * @throws \App\Exceptions\Import\FileValidationException
     */
    public function generatePreview(UploadedFile $file): array;

    /**
     * Validate file before processing
     *
     * @param UploadedFile $file
     * @return bool
     * @throws \App\Exceptions\Import\FileValidationException
     */
    public function validateFile(UploadedFile $file): bool;

    /**
     * Get import statistics for monitoring
     *
     * @param int $userId
     * @return array
     */
    public function getImportStatistics(int $userId): array;

    /**
     * Cancel ongoing import process
     *
     * @param int $userId
     * @param string $importId
     * @return bool
     */
    public function cancelImport(int $userId, string $importId): bool;

    /**
     * Process file import with history tracking
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $userId
     * @param \App\Models\ImportHistory $importHistory
     * @return \App\DTOs\Import\ImportResultDTO
     * @throws \App\Exceptions\Import\RateLimitExceededException
     * @throws \App\Exceptions\Import\FileValidationException
     */
    public function processImportWithHistory(\Illuminate\Http\UploadedFile $file, int $userId, \App\Models\ImportHistory $importHistory): \App\DTOs\Import\ImportResultDTO;
} 