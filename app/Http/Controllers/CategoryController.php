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
            return $this->success($categories);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
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

            return $this->success(['category' => new CategoryResource($category)],
                'Category created successfully', 201);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show (Category $category)
    {
        try {
            return $this->success(['category' => new CategoryResource($category)]);
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
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

            return $this->success(['category' => new CategoryResource($category)], 'Category updated successfully');
        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
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

            return $this->success(message: 'Category deleted successfully');

        } catch ( Throwable $e ) {
            return $this->unexpectedError($e);
        }
    }
}