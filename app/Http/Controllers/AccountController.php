<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = request()->user()->id;
        $accounts = Account::where('user_id', $userId)
            ->select(['id', 'name', 'login', 'type']) // выбираем только нужные поля
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
         // Приведение всех значений AccountType к строковому типу для использования в валидации
         $accountTypeValues = implode(',', array_column(AccountType::cases(), 'value'));

         // Создание валидационного объекта
         $validator = Validator::make($request->all(), [
             'type' => 'required|string|in:' . $accountTypeValues,
             'name' => 'required|string|max:100',
             'login' => 'required|string|max:100',
             'password' => 'nullable|string|max:255',
             'description' => 'nullable|string',
         ]);
 
         // Условное валидационное правило для url
         $validator->sometimes('url', 'nullable|url|max:100', function ($input) {
             return $input->type !== 'другое';
         });
 
         // Условное валидационное правило для url как строка
         $validator->sometimes('url', 'nullable|string|max:100', function ($input) {
             return $input->type === 'другое';
         });
 
         // Выполнение валидации
         if ($validator->fails()) {
             return response()->json(['errors' => $validator->errors()], 422);
         }
 
         $validatedData = $validator->validated();

        $userId = $request->user()->id;
        $type = $validatedData['type'];
        $url = isset($validatedData['url']) ? strtolower($validatedData['url']) : null;
        $nameLowerCase = strtolower($validatedData['name']);
        $login = $validatedData['login'];

        // Проверка дублирования учетной записи по name и login
        $existingAccountByNameAndLogin = Account::where('user_id', $userId)
        ->get()
        ->first(function($record) use ($nameLowerCase, $login) {
            $decryptedName = $record->name;
            $decryptedLogin = $record->login;
            
            return strtolower($decryptedName) === strtolower($nameLowerCase) && $decryptedLogin === $login;
        });

        if ($existingAccountByNameAndLogin) {
            return response()->json(['error' => 
            'Учетная запись с такими названием и логином уже существует'], 422);
        }

        // Проверка дублирования учетной записи по login и url, 
        // если URL указан, то возращает ошибку
        if ($url) {
            $existingAccountByLoginAndUrl = Account::where('user_id', $userId)
            ->get()
            ->first(function($record) use ($login, $url) {
                $decryptedLogin = $record->login;
                $decryptedUrl = $record->url;
                
                return $decryptedLogin === $login && strtolower($decryptedUrl) === strtolower($url);
            });
            if ($existingAccountByLoginAndUrl) {
                return response()->json(['error' => 'Учетная запись с таким логином и url-адресом уже существует'], 422);
            }
        }
    
        // Проверка корректности URL для интернет-ресурсов
        if ($type === AccountType::INTERNET_RESOURCE->value && ! $url) {
            return response()->json(['error' => 
            'URL обязателен для выбранного типа учетной записи'], 422);
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
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:accounts,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Некорректный запрос'], 422);
        }
        $userId = request()->user()->id;
        $account = Account::select(['id', 'type', 'name', 'url', 'login', 
        'password', 'description'])->where('user_id', $userId)->find($id);
        if (! $account) {
            return response()->json(['error' => 'Учетная запись не найдена'], 404);
        }
        // Отключаем скрытие атрибута "password"
        $account->makeVisible('password');
       
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
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:accounts,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Некорректный запрос'], 422);
        }
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
            return response()->json(['error' => 'Учетная запись не найдена'], 404);
        }

        $type = $validatedData['type'];
        $url = isset($validatedData['url']) ? strtolower($validatedData['url']) : null;
        $nameLowerCase = strtolower($validatedData['name']);
        $login = $validatedData['login'];

        // Проверка дублирования учетной записи по name и login
    $existingAccountByNameAndLogin = Account::where('user_id', $userId)
    ->get()
    ->first(function($record) use ($nameLowerCase, $login, $account) {
        $decryptedName = $record->name;
        $decryptedLogin = $record->login;

        return strtolower($decryptedName) === strtolower($nameLowerCase) && $decryptedLogin === $login && $record->id !== $account->id;
    });


        if ($existingAccountByNameAndLogin) {
            return response()->json(['error' => 'Учетная запись с такими названием и логином уже существует'], 422);
        }
        // Проверка корректности URL для интернет-ресурсов
        if ($type === AccountType::INTERNET_RESOURCE->value && ! $url) {
            return response()->json(['error' => 'URL обязателен для выбранного типа учетной записи'], 422);
        }
        // Проверка дублирования учетной записи по login и url, если URL указан
        if ($url) {
            $existingAccountByLoginAndUrl = Account::where('user_id', $userId)
            ->get()
            ->first(function($record) use ($login, $url, $account) {
                $decryptedLogin = $record->login;
                $decryptedUrl = $record->url;

                return $decryptedLogin === $login && strtolower($decryptedUrl) === strtolower($url) && $record->id !== $account->id;
            });

            if ($existingAccountByLoginAndUrl) {
                return response()->json(['error' => 'Учетная запись с таким логином и url-адресом уже существует'], 422);
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
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:accounts,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Некорректный запрос'], 422);
        }

        $userId = request()->user()->id;
        $account = Account::where('user_id', $userId)->find($id);
        if (! $account) {
            return response()->json(['error' => 'Учетная запись не найдена'], 404);
        }
        $account->delete();

        return response()->json(['message' => 'Данная учетная запись успешно удалена'], 200);
    }
}
