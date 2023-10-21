<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use ProtoneMedia\Splade\SpladeTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Str;
use ProtoneMedia\Splade\Facades\Toast;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:create categories', ['only' => ['create', 'store']]);
        $this->middleware('can:read categories',  ['only' => ['show', 'index']]);
        $this->middleware('can:update categories', ['only' => ['edit', 'update']]);
        $this->middleware('can:delete categories', ['only' => ['destroy']]);
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

        $categories = QueryBuilder::for(Category::class)
            ->defaultSort('-created_at')
            ->allowedSorts(['name', 'status', 'created_at'])
            ->allowedFilters(['name', 'status', 'created_at', $globalSearch])
            ->paginate()
            ->withQueryString();

        return view('dashboard.categories.index', [
            'categories' => SpladeTable::for($categories)
                ->withGlobalSearch(columns: ['id'])
                ->column('name', __('main.name'), sortable: true)
                ->column('status', __('main.status'), sortable: true)
                ->column('user.name', __('main.author'))
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
            'description' => "required|max:158",
            'slug' => "required|max:255|unique:categories,slug",
            "status" => "required|in:Published,Draft",
        ]);

        Category::create([
            'user_id' => auth()->user()->id,
            "name" => $request->name,
            "description" => $request->description,
            "slug" => $this->generateUniqueSlug($request->slug),
            "status" => $request->status,
        ]);

        Toast::title(__('main.category_created'))->autoDismiss(3);
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
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => "required|max:255",
            'description' => "required|max:158",
            "status" => "required|in:Published,Draft",
        ]);

        $category->update([
            "name" => $request->name,
            "description" => $request->description,
            "status" => $request->status,
        ]);

        Toast::title(__('main.category_updated'))->autoDismiss(3);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    function destroy(Category $category)
    {
        $category->delete();

        Toast::title(__('main.category_deleted'))->autoDismiss(3);
        return redirect()->back();
    }

    private function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);

        $existingSlugs = Category::where('slug', 'like', $slug . '%')->pluck('slug')->toArray();

        $counter = 1;
        $uniqueSlug = $slug;

        while (in_array($uniqueSlug, $existingSlugs)) {
            $uniqueSlug = $slug . '-' . $counter;
            $counter++;
        }

        return $uniqueSlug;
    }
}
