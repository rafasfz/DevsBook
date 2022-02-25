<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Models\User;
use Image;

class FeedController extends Controller
{
    private $loggedUser;

    public function __construct() {
        $this->middleware('auth:api');

        $this->loggedUser = auth()->user();
    }

    public function create(Request $request) {
        $arr = ['error' => false];
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/svg'];
        $destPath = public_path('/media/uploads/');

        $type = $request->input('type');
        $body = $request->input('body');
        $photo = $request->file('photo');

        if(!$type) {
            $arr['error'] = 'Missing post type';

            return $arr;
        }

        switch($type) {
            case 'photo':

                if(!$photo) {
                    $arr['error'] = 'Missing post photo';

                    return $arr;
                }


                if(!in_array($photo->getClientMimeType(), $allowedTypes)) {
                    $arr['error'] = 'Invalid photo type. Just jpg, jpeg, png, gif and svg are allowed';

                    return $arr;
                }

                $filename = Str::uuid() . '.' . $photo->getClientOriginalExtension();
                $img = Image::make($photo->path())->save($destPath.'/'.$filename);
                $body = $filename;

                break;

            case 'text':
                if(!$body) {
                    $arr['error'] = 'Missing post body';

                    return $arr;
                }

                break;

            default:
                $arr['error'] = 'Invalid post type';

                return $arr;
                break;
        }

        if(!$body) {
            $arr['error'] = 'Missing post body';

            return $arr;
        }

        $post = new Post();
        $post->id_user = $this->loggedUser['id'];
        $post->type = $type;
        $post->body = $body;
        $post->created_at = date('Y-m-d H:i:s');
        $post->save();
        $arr['post'] = $post;

        return $arr;
    }
}
