<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PostLike;
use App\Models\Post;

class PostController extends Controller
{
    private $loggedUser;

    public function __construct() {
        $this->middleware('auth:api');

        $this->loggedUser = auth()->user();
    }

    public function like($id) {
        $arr = ['error' => false];

        $post = Post::find($id);

        if(!$post) {
            $arr['error'] = 'Post not found';
            return $arr;
        }

        $isLiked = !!PostLike::where('id_user', $this->loggedUser['id'])->where('id_post', $id)->count();

        if($isLiked) {
            $postLike = PostLike::where('id_user', $this->loggedUser['id'])->where('id_post', $id);
            $postLike->delete();
        } else {
            $postLike = new PostLike;
            $postLike->id_user = $this->loggedUser['id'];
            $postLike->id_post = $id;
            $postLike->created_at = date('Y-m-d H:i:s');
            $postLike->save();
        }

        $likes = PostLike::where('id_post', $id)->count();
        $arr['likes'] = $likes;
        $arr['like'] = !$isLiked;

        return $arr;
    }
}
