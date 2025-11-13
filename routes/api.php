<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionOptionController;
use App\Http\Controllers\QuizSubmissionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::get('/ping', function () {
    return response()->json(['message' => 'API is working!']);
});

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/quizzes', [QuizController::class, 'index']);
    Route::get('/quizzes/{id}', [QuizController::class, 'show']);
    Route::post('/quizzes', [QuizController::class, 'store'])->middleware('teacher');
    Route::delete('/quizzes/{id}', [QuizController::class, 'destroy'])->middleware('teacher');

    Route::get('/quizzes/{quiz_id}/questions', [QuestionController::class, 'index']);
    Route::post('/questions', [QuestionController::class, 'store'])->middleware('teacher');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->middleware('teacher');

    Route::get('/questions/{question_id}/options', [QuestionOptionController::class, 'index']);
    Route::post('/question-options', [QuestionOptionController::class, 'store'])->middleware('teacher');
    Route::delete('/question-options/{id}', [QuestionOptionController::class, 'destroy'])->middleware('teacher');

    Route::get('/quizzes/{quiz_id}/user-answers/{user_id}', [QuizSubmissionController::class, 'getUserAnswers']);
    Route::get('/quizzes/{quiz_id}/result/{user_id}', [QuizSubmissionController::class, 'getQuizResult']);
    Route::post('/user-answers', [QuizSubmissionController::class, 'saveAnswer']);
});
