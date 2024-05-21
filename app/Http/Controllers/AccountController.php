<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = request()->user()->id;
        $accounts = Account::where('user_id', $userId)
            ->select(['id', 'name', 'login', 'url']) // выбираем только нужные поля
            ->get(); // метод, который выполняет запрос к базе данных

        return response()->json(['data' => $accounts], 200);
    }

    // просмотр категорий учетных записей
    public function indexAccountTypes(): JsonResponse
    {
        return response()->json(AccountType::cases(), 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse //+
    {
        $validatedData = $request->validate([
            'type' => 'required|string|in:'.implode(',', array_column(AccountType::cases(), 'value')), //подключение списка
            'url' => 'nullable|url|max:100',
            'name' => 'required|string|max:100',
            'login' => 'required|string|max:100',
            'password' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $type = $validatedData['type'];
        $url = isset($validatedData['url']) ? strtolower($validatedData['url']) : null;
        $nameLowerCase = strtolower($validatedData['name']);
        $login = $validatedData['login'];

        // Проверка дублирования учетной записи по name и login
        $existingAccountByNameAndLogin = Account::where('user_id', $userId)
            ->whereRaw('LOWER(name) = ?', [$nameLowerCase])
            ->where('login', $login)  // Проверка логина без приведения к нижнему регистру. буду использовать такой принцип для логина
            ->first();

        if ($existingAccountByNameAndLogin) {
            return response()->json(['message' => 'Учетная запись с такими названием и логином уже существует'], 422);
        }

        // Проверка дублирования учетной записи по login и url, если URL указан
        if ($url) {
            $existingAccountByLoginAndUrl = Account::where('user_id', $userId)
                ->where('login', $login)  // Проверка логина без приведения к нижнему регистру
                ->whereRaw('LOWER(url) = ?', [$url])
                ->first();

            if ($existingAccountByLoginAndUrl) {
                return response()->json(['message' => 'Учетная запись с таким логином и url-адресом уже существует'], 422);
            }
        }

        // Проверка корректности URL для интернет-ресурсов
        if ($type === AccountType::INTERNET_RESOURCE->value && ! $url) {
            return response()->json(['message' => 'URL обязателен для выбранного типа учетной записи'], 422);
        }

        $account = Account::create([
            'user_id' => $userId,
            'type' => $type,
            'url' => $validatedData['url'],  // Используем исходные данные из запроса
            'name' => $validatedData['name'],  // Используем исходные данные из запроса
            'login' => $validatedData['login'],  // Используем исходные данные из запроса
            'password' => $validatedData['password'] ?? null,
            'description' => $validatedData['description'],
        ]);

        return response()->json(['message' => 'Учетная запись успешно создана', 'data' => $account], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) //+
    {
        $userId = request()->user()->id;
        $account = Account::select(['id', 'type', 'name', 'url', 'login', 'password', 'description'])->where('user_id', $userId)->find($id);
        if (! $account) {
            return response()->json(['message' => 'Учетная запись не найдена'], 404);
        }
        // Отключаем скрытие атрибута "password"
        $account->makeVisible('password');
        // ($account->password == null) ? $account->password = 'false' : $account->password = 'true'; вот так можно реализовать для фронта

        return response()->json(['data' => $account], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) //+
    {
        $validatedData = $request->validate([
            'type' => 'required|string|in:'.implode(',', array_column(AccountType::cases(), 'value')),
            'url' => 'nullable|url|max:100',
            'name' => 'required|string|max:100',
            'login' => 'required|string|max:100',
            'password' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $account = Account::where('user_id', $userId)->find($id);
        if (! $account) {
            return response()->json(['message' => 'Учетная запись не найдена'], 404);
        }

        $type = $validatedData['type'];
        $url = isset($validatedData['url']) ? strtolower($validatedData['url']) : null;
        $nameLowerCase = strtolower($validatedData['name']);
        $login = $validatedData['login'];

        // Проверка дублирования учетной записи по name и login
        $existingAccountByNameAndLogin = Account::where('user_id', $userId)
            ->whereRaw('LOWER(name) = ?', [$nameLowerCase])
            ->where('login', $login)
            ->where('id', '!=', $account->id)
            ->first();

        if ($existingAccountByNameAndLogin) {
            return response()->json(['message' => 'Учетная запись с такими названием и логином уже существует'], 422);
        }
        // Проверка корректности URL для интернет-ресурсов
        if ($type === AccountType::INTERNET_RESOURCE->value && ! $url) {
            return response()->json(['message' => 'URL обязателен для выбранного типа учетной записи'], 422);
        }
        // Проверка дублирования учетной записи по login и url, если URL указан
        if ($url) {
            $existingAccountByLoginAndUrl = Account::where('user_id', $userId)
                ->where('login', $login)
                ->whereRaw('LOWER(url) = ?', [$url])
                ->where('id', '!=', $account->id)
                ->first();

            if ($existingAccountByLoginAndUrl) {
                return response()->json(['message' => 'Учетная запись с таким логином и url-адресом уже существует'], 422);
            }
        }

        $account->update([
            'type' => $type,
            'url' => $validatedData['url'],
            'name' => $validatedData['name'],
            'login' => $validatedData['login'],
            'password' => $validatedData['password'] ?? $account->password,
            'description' => $validatedData['description'],
        ]);

        return response()->json(['message' => 'Учетная запись успешно обновлена', 'data' => $account], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse //+
    {

        $userId = request()->user()->id;
        $account = Account::where('user_id', $userId)->find($id);
        if (! $account) {
            return response()->json(['message' => 'Учетная запись не найдена'], 404);
        }
        $account->delete();

        return response()->json(['message' => 'Данная учетная запись успешно удалена'], 200);
    }
}
