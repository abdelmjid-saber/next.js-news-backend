<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\Splade\Facades\Toast;
use ProtoneMedia\Splade\SpladeTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create posts', ['only' => ['create', 'store']]);
        $this->middleware('can:read posts',  ['only' => ['show', 'index']]);
        $this->middleware('can:update posts', ['only' => ['edit', 'update']]);
        $this->middleware('can:delete posts', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                Collection::wrap($value)->each(function ($value) use ($query) {
                    $query
                        ->orWhere('title', 'LIKE', "%{$value}%");
                });
            });
        });

        $posts = QueryBuilder::for(Post::class)
            ->defaultSort('-created_at')
            ->allowedSorts(['title', 'status', 'created_at'])
            ->allowedFilters(['title', 'status', 'created_at', $globalSearch])
            ->paginate()
            ->withQueryString();

        return view('dashboard.posts.index', [
            'posts' => SpladeTable::for($posts)
                ->withGlobalSearch(columns: ['id'])
                ->column('title', __('main.title'), sortable: true)
                ->column('status', __('main.status'), sortable: true)
                ->column('user.name', __('main.author'))
                ->column('category.name', __('Category'))
                ->column(
                    'created_at',
                    __('main.created_at'),
                    as: fn ($created_at) => Carbon::parse($created_at)->format(getSetting('date_format')),
                    sortable: true
                )
                ->column('action', __('main.action')),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $categories = Category::where('status', 'Published')->pluck('name', 'id')->toArray();
        $tags = Tag::pluck('name', 'id')->toArray();

        return view('dashboard.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => "required|max:255",
            'content' => "required",
            'excerpt' => "nullable|max:158",
            'slug' => "required|max:255|unique:posts,slug",
            'category_id' => 'required',
            "status" => "required|in:Published,Pending Review,Draft,Rejected",
            "tags" => "array",
        ]);

        $post = Post::create([
            'user_id' => auth()->user()->id,
            "title" => $request->title,
            "content" => $request->content,
            "excerpt" => $request->excerpt,
            "slug" => $this->generateUniqueSlug($request->slug),
            "category_id" => $request->category_id,
            "status" => $request->status,
        ]);

        // Featured Image
        if ($request->file('featured_image')) {
            $featured_image_path = $request->file('featured_image')->store('public/images');
            $image_path = str_replace('public/', '', $featured_image_path);
            $post->update(['featured_image' => Storage::url($image_path)]);
        }

        // Attach tags to the post
        $post->tags()->attach($request->tags);

        Toast::title(__('main.post_created'))->autoDismiss(3);
        return redirect()->route('dashboard.posts.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return view('dashboard.posts.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        $categories = Category::where('status', 'Published')->pluck('name', 'id')->toArray();
        $tags = Tag::pluck('name', 'id')->toArray();

        return view('dashboard.posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => "required|max:255",
            'content' => "required",
            'excerpt' => "nullable|max:158",
            'category_id' => 'required',
            "status" => "required|in:Published,Pending Review,Draft,Rejected",
            "tags" => "array",
        ]);

        $post->update([
            "title" => $request->title,
            "content" => $request->content,
            "excerpt" => $request->excerpt,
            "category_id" => $request->category_id,
            "status" => $request->status,
        ]);

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

        // Sync tags for the post (detach old tags, attach new ones)
        $post->tags()->sync($request->tags);

        Toast::title(__('main.post_updated'))->autoDismiss(3);
        return redirect()->route('dashboard.posts.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Delete Image
        if (File::exists(public_path($post->featured_image))) {
            File::delete(public_path($post->featured_image));
        }

        $post->delete();

        Toast::title(__('main.post_deleted'))->autoDismiss(3);
        return redirect()->route('dashboard.posts.index');
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
}
