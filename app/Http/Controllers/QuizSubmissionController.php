<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\UserAnswer;
// use App\Models\QuizResult;
use App\Models\QuestionOption;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizSubmissionController extends Controller
{
    public function getUserAnswers($quizId, $userId)
    {
        $answers = UserAnswer::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->with(['question', 'selectedOption'])
            ->get();

        return response()->json($answers);
    }

    public function getQuizResult($quizId, $userId)
    {
        $answers = UserAnswer::where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->get();

        $totalQuestions = Question::where('quiz_id', $quizId)->count();
        $correctAnswers = $answers->where('is_correct', true)->count();
        $score = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;

        return response()->json([
            'quiz_id' => $quizId,
            'user_id' => $userId,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score' => round($score, 2),
            'answers' => $answers,
        ]);
    }

    public function saveAnswer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'quiz_id' => 'required|exists:quizzes,id',
            'question_id' => 'required|exists:questions,id',
            'selected_option_id' => 'required|exists:question_options,id',
        ]);

        $quiz = Quiz::find($request->quiz_id);
        $startedAt = UserAnswer::where('user_id', $request->user_id)
            ->where('quiz_id', $request->quiz_id)
            ->whereNotNull('started_at')
            ->value('started_at');

        if (!$startedAt) {
            $startedAt = now();
        } else {
            // Check if duration exceeded
            $durationSeconds = $quiz->duration * 60;
            if (now()->diffInSeconds($startedAt) > $durationSeconds) {
                return response()->json(['message' => 'Quiz time has expired'], 403);
            }
        }

        $option = QuestionOption::find($request->selected_option_id);
        $isCorrect = $option->is_correct;

        $answer = UserAnswer::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'question_id' => $request->question_id,
            ],
            [
                'quiz_id' => $request->quiz_id,
                'selected_option_id' => $request->selected_option_id,
                'is_correct' => $isCorrect,
                'started_at' => $startedAt,
            ]
        );

        return response()->json(['message' => 'Answer saved', 'answer' => $answer]);
    }

    public function submit(Request $request, $quizId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.selected_option_id' => 'required|exists:question_options,id',
        ]);

        $userId = $request->user_id;
        $answers = $request->answers;

        DB::transaction(function () use ($quizId, $userId, $answers) {
            foreach ($answers as $ans) {
                $option = QuestionOption::find($ans['selected_option_id']);
                $isCorrect = $option->is_correct;

                UserAnswer::create([
                    'user_id' => $userId,
                    'quiz_id' => $quizId,
                    'question_id' => $ans['question_id'],
                    'selected_option_id' => $ans['selected_option_id'],
                    'is_correct' => $isCorrect,
                ]);
            }
        });

        return response()->json(['message' => 'Quiz submitted successfully']);
    }
}
