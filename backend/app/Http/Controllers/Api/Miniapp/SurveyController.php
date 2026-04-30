<?php

namespace App\Http\Controllers\Api\Miniapp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Survey;

/**
 * @group Miniapp - Survey
 * 
 * APIs for managing Survey schema and structures.
 * 
 * @unauthenticated
 */
class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $surveys = Survey::all();
        return response()->json([
            'success' => true,
            'message' => 'Surveys retrieved successfully',
            'data' => $surveys
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'survey_name' => 'required|string|max:255',
            'survey_description' => 'nullable|string',
            'json_file' => 'nullable|array',
            'source_json' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $survey = Survey::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Survey created successfully',
            'data' => $survey
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Survey retrieved successfully',
            'data' => $survey
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        $validated = $request->validate([
            'survey_name' => 'sometimes|required|string|max:255',
            'survey_description' => 'nullable|string',
            'json_file' => 'nullable|array',
            'source_json' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $survey->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Survey updated successfully',
            'data' => $survey
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $survey = Survey::find($id);

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        $survey->delete();

        return response()->json([
            'success' => true,
            'message' => 'Survey deleted successfully'
        ]);
    }
}
