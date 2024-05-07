<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $posts = Post::query()
            ->withCount('reactions')
            ->with([
                'reactions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'comments' => function ($query) use ($userId) {
                    $query->withCount('reactions');
                }
            ])
            ->latest()
            ->paginate(10);
        
        $posts = PostResource::collection($posts);
        if ($request->wantsJson()) {
            return $posts;
        }

        return Inertia::render('Home', [
            'posts' => $posts
        ]);
    }
}