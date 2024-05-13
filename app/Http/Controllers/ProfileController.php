<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationСode;
use App\Notifications\EmailVerificationCode;
use App\Notifications\NewEmailVerificationCode;
use App\Notifications\OldEmailVerificationCode;
use DragonCode\Support\Facades\Helpers\Str;
use Illuminate\Http\JsonResponse;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
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

    public function sendCodeEmails(Request $request): JsonResponse
    {
        $validData = $request->validate([
            'new_email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class), 'not_in:' . $request->user()->email]
        ]);
        $user = $request->user();
        VerificationСode::where('user_id', $user->id)->update(['status' => 'invalid']);

        $CodeOldEmail =Str::random(7);
        $CodeNewEmail = Str::random(7);

        $email_old = VerificationСode::create([
            'user_id' => $user->id,
            'type_email' => 'old_email',
            'verification_value' => $user->email,
            'code' => Hash::make($CodeOldEmail),
            'expired_at' => now()->addMinutes(5)
        ]);
        $email_new = VerificationСode::create([
            'user_id' => $user->id,
            'type_email' => 'new_email',
            'verification_value' => $validData['new_email'],
            'code' => Hash::make($CodeNewEmail),
            'expired_at' => now()->addMinutes(5)
        ]);

        $user->notify(new OldEmailVerificationCode($CodeOldEmail));
        Notification::route('mail', $validData['new_email'])->notify(new NewEmailVerificationCode($CodeNewEmail));


        return response()->json(['message' => 'Коды подтверждения отправлены.'],200);
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

