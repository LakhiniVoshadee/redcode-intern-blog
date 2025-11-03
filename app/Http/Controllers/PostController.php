<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;


class PostController extends Controller
{
   
    /**
     * Display a listing of the posts using an Inertia page.
     */
    public function index()
    {
        $posts = Post::latest()->get();
        return Inertia::render('PostsIndex', [
            'posts' => $posts,
        ]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(StorePostRequest $request)
    {
        // The FormRequest will automatically validate and
        // return a 422 JSON response for API callers when invalid.
        $validated = $request->validated();

        // No user ownership attached (public CRUD)
        $post = Post::create($validated);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }

    /**
     * Update the specified post in storage (no validation yet).
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        // The UpdatePostRequest uses "sometimes" rules so partial updates are allowed.
        $data = $request->validated();

        $post->fill($data);
        $post->save();

        return response()->json([
            'message' => 'Post updated successfully',
            'post' => $post,
        ], 200);
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        // Public delete (no ownership/auth checks)
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ], 200);
    }
}