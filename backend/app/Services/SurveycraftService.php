<?php

namespace App\Services;

use App\Models\Surveycraft;
use App\Models\User;
use App\Jobs\ScanUploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SurveycraftService
{
    public function listSurveys(array $params): array
    {
        $perPage = (int) ($params['per_page'] ?? 50);
        $perPage = ($perPage <= 0) ? 50 : min($perPage, 200);

        $baseQuery = Surveycraft::query();
        if (isset($params['filter']['survey_id'])) {
            $baseQuery->where('survey_id', $params['filter']['survey_id']);
        }
        if (isset($params['filter']['owner_id'])) {
            $baseQuery->where('owner_id', $params['filter']['owner_id']);
        }
        $totalRecords = $baseQuery->count();

        $results = \Spatie\QueryBuilder\QueryBuilder::for(Surveycraft::class)
            ->with('owner')
            ->allowedFilters([
                'survey_id',
                \Spatie\QueryBuilder\AllowedFilter::exact('owner_id'),
            ])
            ->defaultSort('-timestamp')
            ->allowedSorts(['timestamp', 'title', 'survey_id'])
            ->fastPaginate($perPage);

        return [
            'data' => $results,
            'pagination_info' => [
                'total_page' => $results->lastPage(),
                'total_records' => $totalRecords,
            ]
        ];
    }

    public function createSurvey(array $data, $file): Surveycraft
    {
        $jsonContent = file_get_contents($file->getPathname());
        $surveyData = json_decode($jsonContent, true);
        $actualSurveyId = $surveyData['id'] ?? $data['survey_id'];

        $path = $file->storeAs(
            'surveycraft',
            $this->surveyFilename($actualSurveyId, $file->getClientOriginalExtension()),
            'direct'
        );

        $data['json_file'] = url('/api/surveycraft/file/' . ltrim(Str::after($path, 'surveycraft/'), '/'));
        
        $surveycraft = Surveycraft::create($data);

        ScanUploadedFile::dispatch($path, $file->getClientOriginalName(), Auth::id());

        return $surveycraft;
    }

    public function updateSurvey(Surveycraft $surveycraft, array $data, $file = null): Surveycraft
    {
        if ($file) {
            $this->deleteFile($surveycraft->json_file);

            $jsonContent = file_get_contents($file->getPathname());
            $surveyData = json_decode($jsonContent, true);
            $actualSurveyId = $surveyData['id'] ?? ($data['survey_id'] ?? $surveycraft->survey_id);

            $path = $file->storeAs(
                'surveycraft',
                $this->surveyFilename($actualSurveyId, $file->getClientOriginalExtension()),
                'direct'
            );

            $data['json_file'] = url('/api/surveycraft/file/' . ltrim(Str::after($path, 'surveycraft/'), '/'));
            ScanUploadedFile::dispatch($path, $file->getClientOriginalName(), Auth::id());
        }

        $surveycraft->update($data);
        return $surveycraft;
    }

    public function deleteSurvey(Surveycraft $surveycraft): void
    {
        $this->deleteFile($surveycraft->json_file);

        $prefix = 'surveycraft/respond/' . $surveycraft->survey_id;
        $respondFiles = Storage::disk('public')->files('surveycraft/respond');
        foreach ($respondFiles as $file) {
            if (Str::startsWith($file, $prefix)) {
                Storage::disk('public')->delete($file);
            }
        }

        $surveycraft->delete();
    }

    public function storeResponse(string $respondId, $file): array
    {
        $path = $file->storeAs('surveycraft/respond', $respondId, 'direct');
        ScanUploadedFile::dispatch($path, $file->getClientOriginalName(), Auth::id());

        return [
            'respond_id' => $respondId,
            'file_url' => url('/api/surveycraft/file/respond/' . $respondId),
        ];
    }

    public function listResponses(string $surveyId): array
    {
        $files = collect(Storage::disk('direct')->files('surveycraft/respond'))
            ->filter(fn($path) => Str::startsWith(basename($path), $surveyId))
            ->values()
            ->map(function ($path) use ($surveyId) {
                $basename = basename($path);
                $modified = Storage::disk('direct')->lastModified($path);
                $fileUrl = url('/api/surveycraft/file/' . ltrim(Str::after($path, 'surveycraft/'), '/'));

                $answers = null;
                try {
                    $raw = Storage::disk('direct')->get($path);
                    $answers = json_decode($raw, true);
                } catch (\Throwable $e) {
                    $answers = null;
                }

                return [
                    'id' => $basename,
                    'survey_id' => $surveyId,
                    'submitted_at' => Carbon::createFromTimestamp($modified)->utc()->toIso8601ZuluString(),
                    'answers' => $answers,
                    'file_url' => $fileUrl,
                ];
            });

        return [
            'survey_id' => $surveyId,
            'responses' => $files,
        ];
    }

    public function getResponse(string $respondId): array
    {
        $path = 'surveycraft/respond/' . $respondId;
        if (!Storage::disk('direct')->exists($path)) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Response not found');
        }

        $modified = Storage::disk('direct')->lastModified($path);
        return [
            'respond_id' => $respondId,
            'submitted_at' => Carbon::createFromTimestamp($modified)->timezone(config('app.timezone'))->toIso8601String(),
            'file_url' => url('/api/surveycraft/file/respond/' . $respondId),
        ];
    }

    public function generateWithAI(string $prompt, string $lang): array
    {
        $apiKey = config('services.groq.api_key');
        if (!$apiKey) {
            throw new \Exception('Groq API key not configured');
        }

        $systemInstruction = $lang === 'id' 
            ? "Anda adalah ahli metodologi survei. Buat survei dalam bahasa Indonesia dengan format JSON."
            : "You are an expert survey methodologist. Generate a JSON survey structure.";

        $primaryModel = 'llama-3.3-70b-versatile';
        $fallbackModel = 'llama-3.1-8b-instant';

        $sendRequest = function (string $model) use ($apiKey, $systemInstruction, $prompt, $lang) {
            return Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemInstruction],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $lang === 'id' ? 0.0 : 0.7,
                'max_tokens' => 4000,
            ]);
        };

        $response = $sendRequest($primaryModel);
        if (!$response->successful()) {
            $response = $sendRequest($fallbackModel);
        }

        if (!$response->successful()) {
            throw new \Exception('AI service unavailable');
        }

        $content = $response->json()['choices'][0]['message']['content'] ?? '';
        $cleanedContent = preg_replace('/^```json\s*|\s*```$/i', '', trim($content));
        $surveyData = json_decode($cleanedContent, true);

        if (!$surveyData || !is_array($surveyData)) {
            throw new \Exception('Invalid AI response format');
        }

        return $surveyData;
    }

    protected function deleteFile(?string $fileUrl): void
    {
        if (empty($fileUrl)) return;
        $relative = Str::after($fileUrl, '/api/surveycraft/file/');
        if ($relative !== $fileUrl) {
            Storage::disk('direct')->delete('surveycraft/' . $relative);
        }
    }

    protected function surveyFilename(string $surveyId, ?string $extension = 'json'): string
    {
        $safeId = preg_replace('/[^A-Za-z0-9._-]/', '_', $surveyId);
        $ext = $extension ?: 'json';
        return $safeId . '.' . ltrim($ext, '.');
    }

    protected function removeProblematicAcronyms(string $prompt): string
    {
        $patterns = ['(WFA)', '[WFA]', '{WFA}', '(WFH)', '[WFH]', '{WFH}', '\bWFA\b', '\bWFH\b', 'Work From Anywhere', 'Work From Home'];
        foreach ($patterns as $pattern) {
            $prompt = preg_replace('/' . $pattern . '/i', '', $prompt);
        }
        return trim(preg_replace('/\s+/', ' ', $prompt));
    }
}
