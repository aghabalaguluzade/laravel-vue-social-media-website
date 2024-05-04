<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;

class HomeController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $posts = Post::query()
            ->withCount('reactions')
            ->with(['reactions' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->latest()
            ->paginate(20);
        
        return Inertia::render('Home', [
            'posts' => PostResource::collection($posts)
        ]);
    }
}