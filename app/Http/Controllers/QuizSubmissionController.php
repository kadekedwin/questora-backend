<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\UserAnswer;
use App\Models\QuizResult;
use App\Models\QuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizSubmissionController extends Controller
{
    public function submit(Request $request, $quizId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_id' => 'required|exists:question_options,id',
        ]);

        $userId = $request->user_id;
        $answers = $request->answers;

        DB::transaction(function () use ($quizId, $userId, $answers) {
            $score = 0;
            foreach ($answers as $ans) {
                $option = QuestionOption::find($ans['option_id']);
                $isCorrect = $option->is_correct;

                UserAnswer::create([
                    'user_id' => $userId,
                    'quiz_id' => $quizId,
                    'question_id' => $ans['question_id'],
                    'option_id' => $ans['option_id'],
                    'is_correct' => $isCorrect,
                ]);

                if ($isCorrect) $score++;
            }

            QuizResult::create([
                'user_id' => $userId,
                'quiz_id' => $quizId,
                'score' => $score,
                'submitted_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Quiz submitted successfully']);
    }
}
