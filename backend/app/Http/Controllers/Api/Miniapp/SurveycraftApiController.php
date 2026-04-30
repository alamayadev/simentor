<?php

namespace App\Http\Controllers\Api\Miniapp;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Surveycraft;
use App\Services\SurveycraftService;
use App\Http\Requests\Miniapp\Surveycraft\StoreSurveycraftRequest;
use App\Http\Requests\Miniapp\Surveycraft\UpdateSurveycraftRequest;
use App\Http\Requests\Miniapp\Surveycraft\StoreSurveyResponseRequest;
use App\Http\Requests\Miniapp\Surveycraft\GenerateSurveyAIRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @group Mini App - Surveycraft
 *
 * Controller for managing Surveycraft mini-app operations.
 * Handles survey creation, retrieval, AI generation, and responses.
 */
class SurveycraftApiController extends BaseApiController
{
    protected SurveycraftService $surveyService;

    /**
     * Create a new controller instance.
     *
     * @param SurveycraftService $surveyService
     */
    public function __construct(SurveycraftService $surveyService)
    {
        $this->surveyService = $surveyService;
    }

    /**
     * Display a listing of surveys.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: GET /api/miniapp/surveycraft?per_page=10
     * @example Response: {"status":"success", "data": [...], "pagination_info": {...}}
     */
    public function index(Request $request)
    {
        $result = $this->surveyService->listSurveys($request->all());
        return $this->success($result['data'], 'Data retrieved successfully');
    }

    /**
     * Generate survey content using AI based on a prompt.
     *
     * @param GenerateSurveyAIRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: POST /api/miniapp/surveycraft/generate-ai with {"prompt": "Basic customer satisfaction", "lang": "en"}
     * @example Response: {"status":"success", "data": {"id": "survey-123", "title": "Customer Satisfaction", "questions": [...]}}
     */
    public function generateWithAI(GenerateSurveyAIRequest $request)
    {
        try {
            $surveyData = $this->surveyService->generateWithAI($request->prompt, $request->lang);
            return $this->success($surveyData);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 500);
        }
    }

    /**
     * Get unique survey ID and title options for selection.
     *
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: GET /api/miniapp/surveycraft/options
     * @example Response: {"status":"success", "data": [{"survey_id": "S1", "title": "Sales Survey"}, ...]}
     */
    public function surveyOptions()
    {
        $options = Surveycraft::select('survey_id', 'title')
            ->groupBy('survey_id', 'title')
            ->orderBy('title')
            ->get();

        return $this->success($options);
    }

    /**
     * Store a newly created survey in storage.
     *
     * @param StoreSurveycraftRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: POST /api/miniapp/surveycraft (Multipart) with json_file, survey_id, title, etc.
     * @example Response: {"status":"success", "data": {"id": 1, "survey_id": "S1", ...}}
     */
    public function store(StoreSurveycraftRequest $request)
    {
        $surveycraft = $this->surveyService->createSurvey(
            $request->validated(),
            $request->file('json_file')
        );

        return $this->success($surveycraft->load('owner'), null, 201);
    }

    /**
     * Store a survey response.
     *
     * @param StoreSurveyResponseRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: POST /api/miniapp/surveycraft/respond (Multipart) with respond_id, json_file
     * @example Response: {"status":"success", "data": {"respond_id": "R1", "file_url": "..."}}
     */
    public function respond_store(StoreSurveyResponseRequest $request)
    {
        $result = $this->surveyService->storeResponse(
            $request->respond_id,
            $request->file('json_file')
        );

        return $this->success($result, null, 201);
    }

    /**
     * List all responses for a given survey ID.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: GET /api/miniapp/surveycraft/responds?survey_id=S1
     * @example Response: {"status":"success", "data": {"survey_id": "S1", "responses": [...]}}
     */
    public function responds(Request $request)
    {
        $request->validate(['survey_id' => 'required|string']);
        $result = $this->surveyService->listResponses($request->survey_id);
        return $this->success($result);
    }

    /**
     * Display the specified survey.
     *
     * @param mixed $surveycraft
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: GET /api/miniapp/surveycraft/1
     * @example Response: {"status":"success", "data": {"id": 1, "title": "...", ...}}
     */
    public function show($surveycraft)
    {
        $record = Surveycraft::with('owner')->find($surveycraft);
        if (!$record) {
            return $this->error('Surveycraft not found', null, 404);
        }

        return $this->success($record);
    }

    /**
     * Get survey details for public sharing.
     *
     * @param Request $request
     * @param mixed $surveycraft
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: GET /api/miniapp/surveycraft/share/1?survey_id=S1
     * @example Response: {"status":"success", "data": {"id": 1, ...}}
     */
    public function share(Request $request, $surveycraft)
    {
        $request->validate(['survey_id' => 'required|string']);
        $record = Surveycraft::with('owner')
            ->where('id', $surveycraft)
            ->where('survey_id', $request->survey_id)
            ->first();

        if (!$record) {
            return $this->error('Surveycraft not found', null, 404);
        }

        return $this->success($record);
    }

    /**
     * Update the specified survey in storage.
     *
     * @param UpdateSurveycraftRequest $request
     * @param mixed $surveycraft
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: PUT /api/miniapp/surveycraft/1 or POST /api/miniapp/surveycraft/1 with updated fields.
     * @example Response: {"status":"success", "data": {"id": 1, ...}}
     */
    public function update(UpdateSurveycraftRequest $request, $surveycraft)
    {
        $record = Surveycraft::find($surveycraft);
        if (!$record) {
            return $this->error('Surveycraft not found', null, 404);
        }

        $updated = $this->surveyService->updateSurvey(
            $record,
            $request->validated(),
            $request->file('json_file')
        );

        return $this->success($updated->load('owner'));
    }

    /**
     * Remove the specified survey from storage.
     *
     * @param mixed $surveycraft
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: DELETE /api/miniapp/surveycraft/1
     * @example Response: HTTP 204 No Content.
     */
    public function destroy($surveycraft)
    {
        $record = Surveycraft::find($surveycraft);
        if (!$record) {
            return $this->error('Surveycraft not found', null, 404);
        }

        $this->surveyService->deleteSurvey($record);

        return $this->success(null, null, 204);
    }

    /**
     * Retrieve a specific survey response by ID.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: GET /api/miniapp/surveycraft/respond?respond_id=R1
     * @example Response: {"status":"success", "data": {"respond_id": "R1", "submitted_at": "...", "file_url": "..."}}
     */
    public function respond(Request $request)
    {
        $request->validate(['respond_id' => ['required', 'string', 'regex:/^[A-Za-z0-9._-]+$/']]);
        try {
            $result = $this->surveyService->getResponse($request->respond_id);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), null, 404);
        }
    }

    /**
     * Serve survey-related files with appropriate headers.
     *
     * @param string $path
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * 
     * @example Request: GET /api/miniapp/surveycraft/file/my-survey.json
     * @example Response: Raw file content (JSON).
     */
    public function file(string $path)
    {
        if (str_contains($path, '..')) {
            abort(400, ltrim($path, '/'));
        }

        $relativePath = 'surveycraft/' . ltrim($path, '/');
        if (!Storage::disk('direct')->exists($relativePath)) {
            return $this->error('File not found', null, 404);
        }

        $mime = Storage::disk('direct')->mimeType($relativePath) ?? 'application/json';
        $content = Storage::disk('direct')->get($relativePath);

        return response($content, 200, [
            'Content-Type' => $mime,
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
        ]);
    }
}
