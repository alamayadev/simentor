<?php

namespace App\Http\Controllers\Api\Miniapp\Surveys\Result;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SurveyResult;
use App\Models\Survey;

/**
 * @group Miniapp - Survey Result
 * 
 * APIs for managing Survey Result data submissions.
 * 
 * @unauthenticated
 */
class SurveyResultController extends Controller
{
    private function checkSurveyExpired($survey)
    {
        if ($survey && $survey->end_at && $survey->end_at < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Survey has expired'
            ], 403);
        }
        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $results = SurveyResult::whereHas('survey', function ($query) {
            $query->whereNull('end_at')->orWhere('end_at', '>=', now());
        })->with('survey:id,survey_name,survey_description')->get();

        return response()->json([
            'success' => true,
            'message' => 'Survey results retrieved successfully',
            'data' => $results
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
            'survey_result' => 'required|array',
        ]);

        $survey = Survey::find($request->survey_id);
        if ($expiredResponse = $this->checkSurveyExpired($survey)) {
            return $expiredResponse;
        }

        $result = SurveyResult::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Survey result created successfully',
            'data' => $result
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $result = SurveyResult::find($id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Survey result not found'
            ], 404);
        }

        if ($expiredResponse = $this->checkSurveyExpired($result->survey)) {
            return $expiredResponse;
        }

        return response()->json([
            'success' => true,
            'message' => 'Survey result retrieved successfully',
            'data' => $result
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $result = SurveyResult::find($id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Survey result not found'
            ], 404);
        }

        if ($expiredResponse = $this->checkSurveyExpired($result->survey)) {
            return $expiredResponse;
        }

        $validated = $request->validate([
            'survey_id' => 'sometimes|required|exists:surveys,id',
            'survey_result' => 'sometimes|required|array',
        ]);

        // If survey_id is updated, check if the completely NEW survey is expired
        if (isset($validated['survey_id']) && $validated['survey_id'] != $result->survey_id) {
            $newSurvey = Survey::find($validated['survey_id']);
            if ($expiredResponse = $this->checkSurveyExpired($newSurvey)) {
                return $expiredResponse;
            }
        }

        $result->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Survey result updated successfully',
            'data' => $result
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = SurveyResult::find($id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Survey result not found'
            ], 404);
        }

        if ($expiredResponse = $this->checkSurveyExpired($result->survey)) {
            return $expiredResponse;
        }

        $result->delete();

        return response()->json([
            'success' => true,
            'message' => 'Survey result deleted successfully'
        ]);
    }
}
