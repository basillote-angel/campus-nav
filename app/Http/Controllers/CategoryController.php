<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * Returns JSON for API/modal usage, or redirects if accessed directly.
     */
    public function index(Request $request)
    {
        $categories = Category::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->latest()
            ->get();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($categories, 200);
        }

        // If accessed directly via browser, redirect to dashboard
        return redirect()->route('dashboard');
    }

    public function create()
    {
        // Categories are managed via modal, redirect to dashboard
        return redirect()->route('dashboard');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:categories']);
        $category = Category::create($request->only('name'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Category added successfully',
                'category' => $category
            ], 200);
        }

        return redirect()->route('dashboard')->with('success', 'Category added.');
    }

    public function edit(Category $category)
    {
        // Categories are managed via modal, redirect to dashboard
        return redirect()->route('dashboard');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|unique:categories,name,' . $category->id]);
        $category->update($request->only('name'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category
            ], 200);
        }

        return redirect()->route('dashboard')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ], 200);
        }

        return redirect()->route('dashboard')->with('success', 'Category deleted.');
    }
}
