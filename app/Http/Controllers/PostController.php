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
}