<?php

namespace App\Http\Requests;

use App\Services\VirusScanService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Upload File Request
 *
 * Custom form request for validating file uploads with content verification.
 * Ensures uploaded files match their declared MIME type to prevent file type spoofing.
 * Includes virus scanning using ClamAV or basic validation as fallback.
 */
class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,json',  // Only allow PDF and JSON files
                'max:2048',  // 2MB in kilobytes
                function ($attribute, $value, $fail) {
                    // SECURITY FIX: Verify file content matches declared MIME type
                    // This prevents file type spoofing attacks

                    if (!$value instanceof \Illuminate\Http\UploadedFile) {
                        $fail('The file must be an uploaded file.');
                    }

                    // Get the real MIME type using fileinfo
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $realMimeType = $finfo->file($value->getPathname());

                    // Get the declared MIME type from the file extension
                    $declaredMime = $value->getMimeType();

                    // Check if the declared MIME type matches the actual file content
                    if ($realMimeType !== $declaredMime) {
                        $fail('File content does not match the declared file type.');
                    }

                    // Additional check: For PDF files, verify it's actually a PDF
                    if ($declaredMime === 'application/pdf') {
                        // Read first few bytes to check PDF magic number
                        $handle = fopen($value->getPathname(), 'rb');
                        $magic = fread($handle, 4);
                        fclose($handle);

                        // PDF magic number should be: %PDF-1.4 (25 50 46 4C)
                        if (substr($magic, 0, 4) !== '%PDF-1.4') {
                            $fail('The file is not a valid PDF file.');
                        }
                    }

                    // For JSON files, verify it's valid JSON
                    if ($declaredMime === 'application/json') {
                        $jsonContent = file_get_contents($value->getPathname());
                        json_decode($jsonContent);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $fail('The file is not a valid JSON file.');
                        }
                    }

                    // SECURITY FIX: Scan file for viruses/malware
                    $virusScanService = new VirusScanService();
                    $scanResult = $virusScanService->scan($value);

                    if (!$scanResult['clean']) {
                        $fail('File security check failed: ' . $scanResult['message']);
                    }

                    return true;
                },
            ],
        ];
    }

    /**
     * Get custom error messages for validator failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'The file is required.',
            'file.file' => 'The file must be a file.',
            'file.mimes' => 'The file must be a PDF or JSON file.',
            'file.max' => 'The file may not be greater than 2048 kilobytes (2MB).',
            'file.uploaded_file' => 'The file must be an uploaded file.',
        ];
    }
}
