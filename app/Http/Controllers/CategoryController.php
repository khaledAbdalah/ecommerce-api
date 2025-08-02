<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreReqeust;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index ()
    {
        try {
            $categories = new CategoryCollection(Category::paginate(10));
            return response()->json([
                'success' => true,
                'data' => [$categories]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store (CategoryStoreReqeust $request)
    {
        try {
            $validated = $request->validated();

            if ( $request->hasFile('thumbnail') ) {
                $validated['thumbnail'] = $request->thumbnail->store('categories/thumbnail');
            }

            $category = Category::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => [
                    'category' => new CategoryResource($category)
                ]
            ], 201);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show (Category $category)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => new CategoryResource($category),
                ]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update (CategoryUpdateRequest $request, Category $category)
    {
        try {
            $validated = $request->validated();

            if ( $request->hasFile('thumbnail') ) {
                if ( !empty($category->thumbnail) ) Storage::delete($category->thumbnail);
                $validated['thumbnail'] = $request->thumbnail->store('categories/thumbnail');
            }

            $category->fill($validated)->save();
            $category = $category->fresh();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => [
                    'category' => new CategoryResource($category),
                ]
            ]);
        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy (Category $category)
    {
        $this->authorize('delete', $category);
        try {
            $category->deleteThumbnail();
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch ( Throwable $e ) {
            return response()->unexpectedError($e);
        }
    }
}