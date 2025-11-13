<?php

namespace App\Http\Controllers;

use App\Models\QuestionOption;
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
    // Get options by question
    public function index($question_id)
    {
        $options = QuestionOption::where('question_id', $question_id)->get();
        return response()->json($options);
    }

    // Add an option to a question
    public function store(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'required|string',
            'is_correct' => 'required|boolean',
        ]);

        $option = QuestionOption::create($request->all());

        return response()->json($option, 201);
    }

    // Delete an option
    public function destroy($id)
    {
        $option = QuestionOption::findOrFail($id);
        $option->delete();
        return response()->json(['message' => 'Option deleted successfully']);
    }
}