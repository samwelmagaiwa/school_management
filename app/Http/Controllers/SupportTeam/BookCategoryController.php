<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Only librarians, admins and super admins manage categories
        $this->middleware('libraryManager');
    }

    public function index()
    {
        $categories = BookCategory::orderBy('name')->get();

        return view('pages.library.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:book_categories,name',
        ]);

        BookCategory::create($data);

        return Qs::jsonStoreOk();
    }

    public function update(Request $request, BookCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:book_categories,name,' . $category->id,
        ]);

        $category->update($data);

        return Qs::jsonUpdateOk();
    }

    public function destroy(BookCategory $category)
    {
        // Prevent deletion if any books still use this category
        $inUse = \App\Models\Book::where('category', $category->name)->exists();
        if ($inUse) {
            return back()->with('flash_danger', 'Cannot delete a category that is still used by one or more books.');
        }

        $category->delete();

        return back()->with('flash_success', __('msg.del_ok'));
    }
}