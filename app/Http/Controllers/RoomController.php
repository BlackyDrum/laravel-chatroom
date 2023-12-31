<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:32|unique:rooms,name',
            'count' => 'required|integer|min:1|max:10',
            'password' => ['nullable', Rules\Password::default()]
        ]);

        if (!Auth::user()->admin)
        {
            if (Room::query()->where('creator_id', '=', Auth::id())->count() >= 1)
            {
                return back()->withErrors(['max_rooms' => "You can only have 1 room. Please delete your active room before creating another one."]);
            }
        }

        Room::query()->create([
            'name' => $request->input('name'),
            'count' => $request->input('count'),
            'password' => $request->input('password') ? Hash::make($request->input('password')) : null,
            'has_password' => (bool)$request->input('password'),
            'creator_id' => Auth::id(),
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:rooms,id'
        ]);

        $room = Room::query()->find($request->input('id'));
        if (Auth::user()->admin)
            $room->delete();
        else if (Auth::id() == $room->creator_id)
            $room->delete();
        else
            abort(403, "Forbidden");
    }
}
