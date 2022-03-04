<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SearchController extends Controller
{
    private $loggedUser;

    public function __construct() {
        $this->middleware('auth:api');

        $this->loggedUser = auth()->user();
    }

    public function search(Request $request) {
        $arr = ['error' => false];

        $txt = $request->input('txt');

        if(!$txt) {
            $arr['error'] = 'Missing input fields';

            return $arr;
        }

        $users = User::where('name', 'like', '%' . $txt . '%')
            ->orWhere('email', 'like', '%' . $txt . '%')
            ->select('id', 'name', 'avatar')
            ->get();

        $arr['users'] = $users;

        return $arr;
    }
}
