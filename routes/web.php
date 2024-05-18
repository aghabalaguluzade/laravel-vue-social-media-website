<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->middleware(['auth', 'verified']);

// Home
Route::get('/', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

// User
Route::get('/u/{user:username}', [ProfileController::class, 'index'])->name('profile');

// Group
Route::get('/g/{group:slug}', [GroupController::class, 'profile'])->name('group.profile');
Route::get('/group/approve-invitation/{token}', [GroupController::class, 'approveInvitation'])->name('group.approveInvitation');

Route::middleware('auth')->group(function () {
    // Profile
    Route::post('/profile/update-images', [ProfileController::class, 'updateImages'])->name('profile.updateImages');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/user/follow/{user}', [UserController::class, 'follow'])->name('user.follow');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('post.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('post.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('post.destroy');
    Route::get('/post/download/{attachment}', [PostController::class, 'downloadAttachment'])->name('post.download');
    Route::post('/post/{post}/reaction', [PostController::class, 'postReaction'])->name('post.reaction');
    Route::get('/post/{post}', [PostController::class, 'view'])->name('post.view');
    Route::post('/post/ai-post', [PostController::class, 'aiPostContent'])->name('post.aiContent');

    // Comments
    Route::post('/post/{post}/comment', [PostController::class, 'createComment'])->name('post.comment.create');
    Route::delete('/comment/{comment}', [PostController::class, 'deleteComment'])->name('post.comment.delete');
    Route::put('/comment/{comment}', [PostController::class, 'updateComment'])->name('post.comment.update');
    Route::post('/comment/{comment}/reaction', [PostController::class, 'commentReaction'])->name('post.comment.reaction');

    // Groups
    Route::post('/group', [GroupController::class, 'store'])->name('group.create');
    Route::put('/group/{group:slug}', [GroupController::class, 'update'])->name('group.update');
    Route::post('/group/update-images/{group:slug}', [GroupController::class, 'updateImages'])->name('group.updateImages');
    Route::post('/group/invite/{group:slug}', [GroupController::class, 'inviteUsers'])->name('group.inviteUsers');
    Route::post('/group/join/{group:slug}', [GroupController::class, 'join'])->name('group.join');
    Route::post('/group/approve-request/{group:slug}', [GroupController::class, 'approveRequest'])->name('group.approveRequest');
    Route::post('/group/change-role/{group:slug}', [GroupController::class, 'changeRole'])->name('group.changeRole');
    Route::delete('group/remove-user/{group:slug}', [GroupController::class, 'removeUser'])->name('group.removeUser');
});

require __DIR__.'/auth.php';
