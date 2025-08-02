<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json([
                'success' => true,
                'data' => [
                    'categories' => $categories,
                ]
            ]);
        } catch ( Throwable $e){
            return response()->unexpectedError($e);
        }
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
        } catch (Throwable $e) {
            return response()->unexpectedError($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $category,
                ]
            ]);
        } catch ( Throwable $e){
            return response()->unexpectedError($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg,webp'
            ]);

            if ($request->hasFile('thumbnail')) {
               if(!empty($category->thumbnail)) Storage::delete($category->thumbnail);
                $validated['thumbnail'] = $request->thumbnail->store('categories/thumbnail');
            }

            $category->fill($validated)->save();
            $category = $category->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => [
                    'category' => $category,
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            return response()->unexpectedError($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {

            $category->deleteThumbnail();
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch ( Throwable $e){
            return response()->unexpectedError($e);
        }
    }
}