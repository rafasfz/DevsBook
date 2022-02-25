<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PostLike;
use App\Models\PostComment;
use App\Models\Post;
use App\Models\User;

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

    public function comment(Request $request, $id) {
        $arr = ['error' => false];

        $post = Post::find($id);
        $body = $request->input('body');

        if(!$post) {
            $arr['error'] = 'Post not found';
            return $arr;
        }

        if(!$body) {
            $arr['error'] = 'Missing comment body';
            return $arr;
        }

        $comment = new PostComment;
        $comment->id_user = $this->loggedUser['id'];
        $comment->id_post = $id;
        $comment->body = $body;
        $comment->created_at = date('Y-m-d H:i:s');

        $comment->save();
        $user = User::find($this->loggedUser['id']);
        $user['avatar'] = url('/media/avatars/'.$user->avatar);
        $comment['user'] = $user;

        $arr['comment'] = $comment;

        return $arr;
    }
}
