<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MasterPassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'master_password'=>['required','confirmed','min:6','different:'.$request->user()->password],

        ],
        ['masterPassword.different' => 'Мастер пароль должен отличаться от пароля для входа в аккаунт',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
