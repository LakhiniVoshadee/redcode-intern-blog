<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'tags' => 'nullable|string|max:255',
            'read_time' => 'nullable|integer|min:1',
            'views' => 'nullable|integer|min:0',
        ]);
        // attach user_id if authenticated and posts table supports it
        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
        }

        $post = Post::create($validated);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post
        ], 201);
    }

    /**
     * Update the specified post in storage (no validation yet).
     */
    public function update(Request $request, Post $post)
    {
        // Basic ownership check: only owner may edit when user_id exists
        if (!Auth::check() || ($post->user_id && $post->user_id !== Auth::id())) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Update allowed fields directly without validation (per request)
        $data = $request->only(['title', 'content', 'category', 'excerpt', 'tags', 'read_time', 'views']);

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
        if (!Auth::check() || ($post->user_id && $post->user_id !== Auth::id())) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully',
        ], 200);
    }
}