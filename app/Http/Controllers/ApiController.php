<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function post($slug)
    {
        $posts = Post::where('slug', $slug)->where('status', 'Published')
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'username', 'bio', 'profile_photo_path')
                    ->where('status', 'Active');
            }, 'category' => function ($query) {
                $query->select('id', 'name', 'slug')
                    ->where('status', 'Published');
            }, 'tags'])->orderByDesc('created_at')->first();

        return response()->json($posts);
    }

    public function posts(Request $request)
    {
        $limit = $request->input('limit', 8);
        $order = $request->input('order', 'desc');
        $views = $request->input('views');

        $postsQuery = Post::where('status', 'Published');

        if (in_array($order, ['asc', 'desc'])) {
            $postsQuery->orderBy('created_at', $order);
        }

        if (in_array($views, ['asc', 'desc'])) {
            $postsQuery->orderBy('views', $views);
        }

        $posts = $postsQuery->paginate($limit);

        return response()->json($posts);
    }

    public function categoryPosts(Request $request, $category)
    {
        $limit = $request->input('limit', 8);

        $category = Category::where('slug', $category)
            ->where('status', 'Published')
            ->first();

        if ($category) {
            $posts = $category->posts()->where('status', 'Published')->orderBy('created_at', 'desc')->paginate($limit);

            return response()->json(['category' => $category, 'posts' => $posts]);
        } else {
            return response('No found category', 404);
        }
    }

    public function tagPosts(Request $request, $tag)
    {
        $limit = $request->input('limit', 8);

        $tag = Tag::where('slug', $tag)->first();

        if ($tag) {
            $posts = $tag->posts()->where('status', 'Published')->orderBy('created_at', 'desc')->paginate($limit);

            return response()->json(['tag' => $tag, 'posts' => $posts]);
        } else {
            return response('No found tag', 404);
        }
    }
}
