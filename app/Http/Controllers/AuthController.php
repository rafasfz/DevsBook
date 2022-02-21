<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['lohin', 'create', 'unauthorized']]);
    }

    public function unauthorized() {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    public function create(Request $request) {
        $array = ['error' => false];

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $birthdate = $request->input('birthdate');

        if(!($name && $email && $password && $birthdate)) {
            $array['error'] = 'Missing input fields';

            return $array;
        }

        if(strtotime($birthdate) === false) {
            $array['error'] = 'Invalid birthdate';

            return $array;
        }

        $emailExists = User::where('email', $email)->count();

        if($emailExists) {
            $array['error'] = 'Email already registred';

            return $array;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = $hash;
        $user->birthdate = $birthdate;
        $user->save();

        $token = auth()->attempt([
            'email' => $email,
            'password' => $password
        ]);
        if(!$token) {
            $array['error'] = 'Error not expected, please report this';

            return $array;
        }
        $array['token'] = $token;

        return $array;
    }
}
