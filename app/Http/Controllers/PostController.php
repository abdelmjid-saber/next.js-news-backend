<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            $posts = Post::where('user_id', auth()->user()->id)->orderBy('updated_at', 'desc')->get();
            return response()->json($posts);
        }

        return response()->json(['message' => 'Not Authenticated'], 401);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => "required|min:6|max:255",
            'content' => "required",
            'excerpt' => "nullable|max:158",
            'category' => 'required',
            "status" => "required|in:Pending Review,Draft",
        ]);

        $post = Post::create([
            'user_id' => auth()->user()->id,
            "title" => $request->title,
            "content" => $request->content,
            "excerpt" => $request->excerpt,
            "slug" => $this->generateUniqueSlug($request->title),
            "category_id" => $request->category,
            "status" => $request->status,
        ]);


        $tags = json_decode($request->tags);
        $post->tags()->attach($tags);

        // Featured Image
        if ($request->file('featured_image')) {
            $featured_image_path = $request->file('featured_image')->store('public/images');
            $image_path = str_replace('public/', '', $featured_image_path);
            $post->update(['featured_image' => Storage::url($image_path)]);
        }

        return response()->json(['message' => 'Post created']);
    }

    public function edit($slug)
    {
        $post = Post::where('slug', $slug)->with('tags')->first();

        if (auth()->user()->id === $post->user_id) {
            return response()->json($post);
        } else {
            return response()->json(['message' => 'No found post'], 404);
        }
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'title' => "required|min:6|max:255",
            'content' => "required",
            'excerpt' => "nullable|max:158",
            'category' => 'required',
            "status" => "required|in:Pending Review,Draft",
        ]);

        $post = Post::where('slug', $slug)->first();

        if (auth()->user()->id !== $post->user_id) {
            return response()->json(['message' => 'No found post'], 404);
        }

        $post->update([
            "title" => $request->title,
            "content" => $request->content,
            "excerpt" => $request->excerpt,
            "category_id" => $request->category,
            "status" => $request->status,
        ]);

        $tags = json_decode($request->tags);
        $post->tags()->sync($tags);

        // Featured Image
        if ($request->file('featured_image')) {
            // Delete Current Image
            if (File::exists(public_path($post->featured_image))) {
                File::delete(public_path($post->featured_image));
            }

            $featured_image_path = $request->file('featured_image')->store('public/images');
            $image_path = str_replace('public/', '', $featured_image_path);
            $post->update(['featured_image' => Storage::url($image_path)]);
        }

        return response()->json(['message' => 'Post updated']);
    }

    public function destroy($slug)
    {
        $post = Post::where('slug', $slug)->first();

        if (auth()->user()->id !== $post->user_id) {
            return response()->json(['message' => 'No found post'], 404);
        }

        // Delete Image
        if (File::exists(public_path($post->featured_image))) {
            File::delete(public_path($post->featured_image));
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted']);
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);

        $existingSlugs = Post::where('slug', 'like', $slug . '%')->pluck('slug')->toArray();

        $counter = 1;
        $uniqueSlug = $slug;

        while (in_array($uniqueSlug, $existingSlugs)) {
            $uniqueSlug = $slug . '-' . $counter;
            $counter++;
        }

        return $uniqueSlug;
    }

    public function categories()
    {
        $categories = Category::where('status', 'Published')->get();

        return response()->json($categories);
    }

    public function tags()
    {
        $tags = Tag::get();

        return response()->json($tags);
    }
}
