<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserRelation;
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

    public function read(Request $request) {
        $arr = ['error' => false];

        $page = intval($request->input('page'));
        $perPage = 2;

        $follows = [];
        $userList = UserRelation::where('user_from', $this->loggedUser['id'])->get();

        foreach($userList as $user) {
            $follows[] = $user['user_to'];
        }
        $follows[] = $this->loggedUser['id'];

        $posts = Post::whereIn('id_user', $follows)->orderBy('created_at', 'desc')->offSet($page * $perPage)->limit($perPage)->get();
        $total = Post::whereIn('id_user', $follows)->count();
        $pageCount = ceil($total / $perPage);

        $posts = $this->_postListToObject($posts, $this->loggedUser['id']);

        $arr['posts'] = $posts;
        $arr['pageCount'] = $pageCount;
        $arr['currentPage'] = $page;

        return $arr;
    }

    public function userFeed(Request $request, $id = false) {
        $arr = ['error' => false];

        $id = $id ? $id : $this->loggedUser['id'];

        $page = intval($request->input('page'));
        $perPage = 2;

        $posts = Post::where('id_user', $id)
            ->orderBy('created_at', 'desc')
            ->offSet($page * $perPage)
            ->limit($perPage)
            ->get();
        $total = Post::where('id_user', $id)->count();
        $pageCount = ceil($total / $perPage);

        $posts = $this->_postListToObject($posts, $this->loggedUser['id']);

        $arr['posts'] = $posts;
        $arr['pageCount'] = $pageCount;
        $arr['currentPage'] = $page;

        return $arr;
    }

    private function _postListToObject($posts, $id) {
        foreach($posts as $post) {
            $post->user = User::find($post->id_user);
            $post->user->avatar = url('/media/avatars/'.$post->user->avatar);
            $post->user->cover = url('/media/covers/'.$post->user->cover);

            $post->likes = PostLike::where('id_post', $post->id)->count();
            $post->isLiked = PostLike::where('id_post', $post->id)->where('id_user', $id)->count();

            $post->comments = PostComment::where('id_post', $post->id)->get();
            foreach($post->comments as $comment) {
                $comment->user = User::find($comment->id_user);
                $comment->user->avatar = url('/media/avatars/'.$comment->user->avatar);
                $comment->user->cover = url('/media/covers/'.$comment->user->cover);
            }

            if($post->id_user == $id) {
                $post->mine = true;
            } else {
                $post->mine = false;
            }

            if($post->type == 'photo') {
                $post->photo = url('/media/uploads/'.$post->body);
            }
        }
        return $posts;
    }
}
