<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    // Get questions by quiz
    public function index($quiz_id)
    {
        $questions = Question::where('quiz_id', $quiz_id)->with('options')->get();
        return response()->json($questions);
    }

    // Add a question and its options
    public function store(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string',
        ]);

        $question = Question::create([
            'quiz_id' => $request->quiz_id,
            'question_text' => $request->question_text,
        ]);

        return response()->json(['message' => 'Question added successfully', 'question' => $question->load('options')]);
    }

    // Delete question
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        return response()->json(['message' => 'Question deleted successfully']);
    }
}
