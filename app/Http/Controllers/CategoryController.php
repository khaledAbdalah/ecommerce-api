<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => Category::all()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validated =  $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg,webp'
            ]);

            if ($request->hasFile('thumbnail')) {
                $validated['thumbnail'] = $request->thumbnail->store('categories/thumbnail');
            }

            $category = Category::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => [
                    'category' => $category
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {

            if (!$category = Category::find($id)) throw new ModelNotFoundException('Category not found');

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $category,
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {

            if (!$category = Category::find($id)) throw new ModelNotFoundException('Category not found');

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg,webp'
            ]);

            if ($request->hasFile('thumbnail')) {
               if(!empty($category->thumbnail)) Storage::delete($category->thumbnail);
                $validated['thumbnail'] = $request->thumbnail->store('categories/thumbnail');
            }

            $category->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => [
                    'category' => $category,
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            if (!$category =  Category::find($id)) throw new ModelNotFoundException('Category not found');

            $category->deleteThumbnail();
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
