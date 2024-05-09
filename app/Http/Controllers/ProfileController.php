<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use \Symfony\Component\HttpFoundation\Response;


class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateName(Request $request): JsonResponse
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->name = $validatedData['name'];
        $user->save();

        return response()->json(['message' => 'Имя пользователя успешно обновлено', 'user' => $user], 200);
    }
    public function updateEmailUnverified(Request $request): JsonResponse
    {

        $validatedData = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
        ]);
        $user = $request->user();
        if (!$user->hasVerifiedEmail()) {
            $user->email = $validatedData['email'];
            $user->save();
            return response()->json(['message' => 'Email пользователя успешно обновлен', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'Email пользователя уже подтвержден. Изменение почты данным способом невозможно.', 'user' => $user], 200);
        }

    }


    /* public function update(Request $request)
    {

       $validData =  $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($request->user()->id)],
         ]);
         $request->user()->fill($validData);

         if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return response()->json(['message' => 'Даныне профиля успешно обновлены', 'user' => $request -> user()], 200);

    } */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $name = $request->user()->name;

        Auth::guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(["message`" => "$name, your account has been deleted"], 200);
    }
}
