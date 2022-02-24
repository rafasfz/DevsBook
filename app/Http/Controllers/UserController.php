<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use Image;

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

    public function updateAvatar(Request $request) {
        $arr = ['error' => false];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg'];

        $user = User::find($this->loggedUser['id']);
        $destPath = public_path('/media/avatars/');

        if($user->avatar) {
            try {
                unlink($destPath . $user->avatar);
            } catch (\Exception $e) {
                //
            }
        }

        $image = $request->file('avatar');

        if(!$image) {
            $arr['error'] = 'No avatar provided';
            return $arr;
        }

        if(in_array($image->getMimeType(), $allowedTypes) === false) {
            $arr['error'] = 'Invalid image type. Just jpg, jpeg, png, gif and svg are allowed';
            return $arr;
        }

        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

        Image::make($image->path())->save($destPath.'/'.$filename);

        $user->avatar = $filename;
        $user->save();
        $arr['url'] = url('/media/avatars/'.$filename);
        $arr['user'] = $user;

        return $arr;

    }
}
