<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizSubmissionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/ping', function () {
    return response()->json(['message' => 'API is working!']);
});

Route::get('/quizzes', [QuizController::class, 'index']);
Route::get('/quizzes/{id}', [QuizController::class, 'show']);
Route::post('/quizzes', [QuizController::class, 'store']);
Route::delete('/quizzes/{id}', [QuizController::class, 'destroy']);

Route::post('/questions', [QuestionController::class, 'store']);
Route::delete('/questions/{id}', [QuestionController::class, 'destroy']);

Route::post('/quizzes/{id}/submit', [QuizSubmissionController::class, 'submit']);
