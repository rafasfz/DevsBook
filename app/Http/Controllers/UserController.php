<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct() {
        $this->middleware('auth:api');

        $this->loggedUser = auth()->user();
    }

    public function update(Request $request) {
        $arr = ['error' => false];

        $name = $request->input('name');
        $email = $request->input('email');
        $birthdate = $request->input('birthdate');
        $city = $request->input('city');
        $work = $request->input('work');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');

        $user = User::find($this->loggedUser['id']);

        if($name) {
            $user->name = $name;
        }

        if($email) {
            if($email != $user->email) {
                $emailExists = User::where('email', $email);

                if($emailExists) {
                    $arr['error'] = 'Email already registred';
                    return $arr;
                }

                $user->email = $email;
            }
        }

        if($birthdate) {
            if(strtotime($birthdate) === false) {
                $arr['error'] = 'Invalid birthdate';
                return $arr;
            }

            $user->birthdate = $birthdate;
        }

        if($city) {
            $user->city = $city;
        }

        if($work) {
            $user->work = $work;
        }

        if($password && $password_confirm) {
            if($password != $password_confirm) {
                $arr['error'] = 'Passwords do not match';
                return $arr;
            }

            $user->password = password_hash($password, PASSWORD_DEFAULT);
        }

        $user->save();
        $arr['user'] = $user;


        return $arr;
    }
}
