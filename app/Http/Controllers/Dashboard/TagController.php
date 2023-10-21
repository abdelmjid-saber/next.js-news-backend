<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ProtoneMedia\Splade\SpladeTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;
use ProtoneMedia\Splade\Facades\Toast;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create tags', ['only' => ['create', 'store']]);
        $this->middleware('can:read tags',  ['only' => ['show', 'index']]);
        $this->middleware('can:update tags', ['only' => ['edit', 'update']]);
        $this->middleware('can:delete tags', ['only' => ['destroy']]);
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
                        ->orWhere('name', 'LIKE', "%{$value}%");
                });
            });
        });

        $tags = QueryBuilder::for(tag::class)
            ->defaultSort('-created_at')
            ->allowedSorts(['name', 'status', 'created_at'])
            ->allowedFilters(['name', 'status', 'created_at', $globalSearch])
            ->paginate()
            ->withQueryString();

        return view('dashboard.tags.index', [
            'tags' => SpladeTable::for($tags)
                ->withGlobalSearch(columns: ['id'])
                ->column('name', __('main.name'), sortable: true)
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => "required|max:255",
            'description' => "nullable|max:158",
            'slug' => "required|max:255|unique:categories,slug",
        ]);

        Tag::create([
            "name" => strip_tags($request->name),
            "description" => strip_tags($request->description),
            "slug" => $this->generateUniqueSlug($request->slug),
        ]);

        Toast::title(__('main.tag_created'))->autoDismiss(3);
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => "required|max:255",
            'description' => "nullable|max:158",
        ]);

        $tag->update([
            "name" => strip_tags($request->name),
            "description" => strip_tags($request->description),
        ]);

        Toast::title(__('main.tag_updated'))->autoDismiss(3);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        Toast::title(__('main.tag_deleted'))->autoDismiss(3);
        return redirect()->back();
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);

        $existingSlugs = Tag::where('slug', 'like', $slug . '%')->pluck('slug')->toArray();

        $counter = 1;
        $uniqueSlug = $slug;

        while (in_array($uniqueSlug, $existingSlugs)) {
            $uniqueSlug = $slug . '-' . $counter;
            $counter++;
        }

        return $uniqueSlug;
    }
}
