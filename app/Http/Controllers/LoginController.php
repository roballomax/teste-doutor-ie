<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function index(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response($validator->errors(), 422);
        }

        if (Auth::attempt($request->all())) {
            $request->session()->regenerate();
 
            $token = $request->user()->createToken('API TOKEN');
            
            return response([
                'message' => 'Login efetuado com sucesso!',
                'token' => $token->plainTextToken,
            ]);
        }

        return response([
            'message' => 'Erro ao efetuar Login!',
        ]);
    }
}
