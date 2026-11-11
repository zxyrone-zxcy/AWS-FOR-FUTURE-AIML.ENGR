<?php

use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// Public, read-only site: no login, no database.
Route::get('/', [QuizController::class, 'home'])->name('home');
Route::get('/quiz', [QuizController::class, 'quiz'])->name('quiz');
Route::view('/guide', 'guide')->name('guide');
Route::get('/about', [QuizController::class, 'about'])->name('about');

// Question bank consumed by public/js/quiz.js
Route::get('/question-bank', [QuizController::class, 'questions'])->name('questions');
