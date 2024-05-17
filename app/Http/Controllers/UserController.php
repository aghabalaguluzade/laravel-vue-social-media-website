<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use App\Models\User;
use App\Notifications\FollowUser;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function follow(Request $request, User $user)
    {
        $data = $request->validate([
            'follow' => ['boolean']
        ]);

        if($data['follow']) {
            $message = 'You followed user "'.$user->name.'"';
            Follower::create([
                'user_id' => $user->id,
                'follower_id' => auth()->id()
            ]);
        }else {
            $message = 'You unfollowed user "'.$user->name.'"';
            Follower::query()
                ->where('user_id', $user->id)
                ->where('follower_id', auth()->id())
                ->delete();
        }
        $user->notify(new FollowUser(auth()->user(), $data['follow']));

        return back()->with('success', $message);
    }
}
