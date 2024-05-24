<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\NewEmailVerificationCode;
use App\Notifications\OldEmailVerificationCode;
use DragonCode\Support\Facades\Helpers\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

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
        if (! $user->hasVerifiedEmail()) {
            $user->email = $validatedData['email'];
            $user->save();

            return response()->json(['message' => 'Email пользователя успешно обновлен', 'user' => $user], 200);
        } else {
            return response()->json(['message' => 'Email пользователя уже подтвержден. Изменение почты данным способом невозможно.', 'user' => $user], 200);
        }
    }

    /*public function sendCodeEmails(Request $request): JsonResponse
    {
        $validData = $request->validate([
            'new_email' => ['required', 'string', 'lowercase', 'email', 'max:255',  Rule::unique(User::class, 'email')]
            //, 'not_in:' . $request->user()->email]
        ]);
        $user = $request->user();
      //  VerificationCode::where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'invalid']);

        $CodeOldEmail = Str::random(7);
        $CodeNewEmail = Str::random(7);

        $email_old = VerificationCode::create([
            'user_id' => $user->id,
            'type_email' => 'old_email',
            'verification_value' => $user->email,
            'code' => Hash::make($CodeOldEmail),
            'expired_at' => now()->addMinutes(5)
        ]);
        $email_new = VerificationCode::create([
            'user_id' => $user->id,
            'type_email' => 'new_email',
            'verification_value' => $validData['new_email'],
            'code' => Hash::make($CodeNewEmail),
            'expired_at' => now()->addMinutes(5)
        ]);

        $user->notify(new OldEmailVerificationCode($CodeOldEmail));
        Notification::route('mail', $validData['new_email'])->notify(new NewEmailVerificationCode($CodeNewEmail));


        return response()->json(['message' => 'Коды подтверждения отправлены.'], 200);
    }
*/
    public function sendCodeEmails(Request $request): JsonResponse
    {
        $validData = $request->validate([
            'new_email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
        ]);

        $user = $request->user();

        // Отметить все предыдущие активные коды как недействительные это излишне для updateorcreate
        //  VerificationCode::where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'invalid']);

        // Генерация новых кодов
        $CodeOldEmail = Str::random(7);
        $CodeNewEmail = Str::random(7);

        // Проверка наличия существующих кодов и создание новых только при их отсутствии
        $email_old = VerificationCode::updateOrCreate(
            ['user_id' => $user->id, 'type_email' => 'old_email', 'status' => 'pending'],
            [
                'verification_value' => $user->email,
                'code' => $CodeOldEmail,
                'expired_at' => now()->addMinutes(10),
                'status' => 'pending',
            ]
        );

        $email_new = VerificationCode::updateOrCreate(
            ['user_id' => $user->id, 'type_email' => 'new_email', 'status' => 'pending'],
            [
                'verification_value' => $validData['new_email'],
                'code' => $CodeNewEmail,
                'expired_at' => now()->addMinutes(10),
                'status' => 'pending',
            ]
        );

        // Отправка уведомлений с кодами подтверждения
        $user->notify(new OldEmailVerificationCode($CodeOldEmail));
        Notification::route('mail', $validData['new_email'])->notify(new NewEmailVerificationCode($CodeNewEmail));

        return response()->json(['message' => 'Коды подтверждения отправлены.'], 200);
    }

    public function updateEmailVerified(Request $request): JsonResponse
    {
        $validData = $request->validate([
            'code_oldemail' => ['required', 'string','max:64'],
            'code_newemail' => ['required', 'string','max:64'],
        ]);

        $user = $request->user();
        // Отметить все предыдущие активные коды как недействительные это излишне для updateorcreate , но возможно здесь пригодиться если вручную
        //  VerificationCode::where('user_id', $user->id)->where('status', 'pending')->update(['status' => 'invalid']);
        // Поиск активных кодов подтверждения
        $oldEmailVerification = VerificationCode::where('user_id', $user->id)
            ->where('type_email', 'old_email')
            ->where('expired_at', '>', now()) // если на сервере  будет запущен механизм автоматической смены статуса,
            // то можно убрать эту проверку или в целом пересмотреть проверку кодов с детальным выводом
            ->where('status', 'pending')
            ->first();

        $newEmailVerification = VerificationCode::where('user_id', $user->id)
            ->where('type_email', 'new_email')
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->first();

        // Проверка на существование активных кодов
        if (! $oldEmailVerification || ! $newEmailVerification) {
            return response()->json(['error' => 'Сессия подтверждения истекла или ещё не инициирована'], 422);
        }

        // Проверка введенных кодов
        if (
            Hash::check($validData['code_oldemail'], $oldEmailVerification->code) &&
            Hash::check($validData['code_newemail'], $newEmailVerification->code)
        ) {
            // Обновление email пользователя
            $user->email = $newEmailVerification->verification_value;
            $user->save();

            // Пометить коды как использованные
            $oldEmailVerification->update(['status' => 'activated', 'verified_at' => now()]);
            $newEmailVerification->update(['status' => 'activated', 'verified_at' => now()]);

            // Пометить email пользователя как подтвержденный
            if ($request->user()->markEmailAsVerified()) {
                return response()->json(['message' => 'Почта успешно обновлена.', $oldEmailVerification], 200);
            }
        }

        return response()->json(['error' => 'Проверьте правильность введенных кодов'], 422);
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

        return response()->json(['message`' => "$name, your account has been deleted"], 200);
    }
}
