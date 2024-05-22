<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\PostAttachmentResource;
use App\Models\Follower;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function index(Request $request, User $user)
    {
        $isCurrentUserFollower = false;
        if(!auth()->guest()) {
            $isCurrentUserFollower = Follower::where('user_id', $user->id)
                ->where('follower_id', auth()->id())
                ->exists();
        }

        $followerCount = Follower::where('user_id', $user->id)->count();

        $posts = Post::postsForTimeline(Auth::id(), false)
            ->leftJoin('users AS u', 'u.pinned_post_id', 'posts.id')
            ->where('user_id', $user->id)
            ->orderBy('u.pinned_post_id', 'desc')
            ->orderBy('posts.created_at', 'desc')
            ->paginate(10);

        $posts = PostResource::collection($posts);
        if($request->wantsJson()) {
            return $posts;
        }

        $followers = $user->followers;
        $followings = $user->followings;

        $photos = PostAttachment::query()
            ->where('mime', 'like', 'image/%')
            ->where('created_by', $user->id)
            ->latest()
            ->get();

        return Inertia::render('Profile/View', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'success' => session('success'),
            'isCurrentUserFollower' => $isCurrentUserFollower,
            'followerCount' => $followerCount,
            'user' => new UserResource($user),
            'posts' => $posts,
            'followers' => UserResource::collection($followers),
            'followings' => UserResource::collection($followings),
            'photos' => PostAttachmentResource::collection($photos)
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile', $request->user())->with('success', 'Your profile details were updated.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updateImages(Request $request)
    {
        $data = $request->validate([
            'cover' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ]);

        $cover = $data['cover'] ?? null;
        $avatar = $data['avatar'] ?? null;
        $user = $request->user();
        $success = '';

        if ($cover) {

            if ($user->cover_path) {
                Storage::disk('public')->delete($user->cover_path);
            }

            $path = $cover->store('covers/'.$user->id,'public');
            $user->update(['cover_path' => $path]);
            $success = 'Cover image updated';
        }

        if ($avatar) {
            
            if($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $avatar->store('avatars/'.$user->id, 'public');
            $user->update(['avatar_path' => $path]);
            $success = 'Avatar image updated';
        }

        return back()->with('success', $success);

    }

}