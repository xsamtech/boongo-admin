<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function account(Request $request)
    {
        return view('account', ['user' => auth()->user()]);
    }

    public function accountEntity($entity)
    {
        return view('account', ['entity' => $entity, 'user' => auth()->user()]);
    }

    public function accountEntityDatas($entity, $id)
    {
        return view('account', ['entity' => $entity, 'selectedId' => $id, 'user' => auth()->user()]);
    }

    public function updateAccount(Request $request)
    {
        $user = auth()->user();

        $v = Validator::make($request->all(), [
            'firstname' => 'nullable|string|max:150',
            'lastname' => 'nullable|string|max:150',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'city' => 'nullable|string|max:150',
            'password' => 'nullable|string|min:6|confirmed',
            'avatar_crop' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return Redirect::back()->withErrors($v->errors())->withInput();
        }

        $data = $v->validated();
        $user->firstname = $data['firstname'] ?? $user->firstname;
        $user->lastname = $data['lastname'] ?? $user->lastname;
        $user->email = $data['email'];
        $user->city = $data['city'] ?? $user->city;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if (!empty($data['avatar_crop']) && str_starts_with($data['avatar_crop'], 'data:image/')) {
            [$meta, $payload] = explode(',', $data['avatar_crop'], 2);
            $ext = str_contains($meta, 'jpeg') ? 'jpg' : (str_contains($meta, 'webp') ? 'webp' : 'png');
            $path = 'images/users/' . $user->id . '/avatar_' . time() . '.' . $ext;
            Storage::disk('public')->put($path, base64_decode($payload));
            $user->avatar_url = Storage::url($path);
        }

        $user->save();

        return Redirect::back()->with('success_message', 'Compte mis a jour.');
    }

    public function updateAccountEntity(Request $request, $entity)
    {
        return $this->updateAccount($request);
    }

    public function updateAccountEntityDatas(Request $request, $entity, $id)
    {
        return $this->updateAccount($request);
    }
}
