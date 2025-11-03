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
        $request = request();

        $query = Post::query()->latest();

        // Search by title or content
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        // Filter by tag (simple contains match on comma-separated tags)
        if ($tag = $request->query('tag')) {
            $query->where('tags', 'like', "%{$tag}%");
        }

        $perPage = 6;
        $paginator = $query->paginate($perPage)->withQueryString();

        // list of available categories for the filter UI
        $categories = Post::query()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        // If request expects JSON (AJAX), return JSON paginator shape
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'posts' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        }

        return Inertia::render('PostsIndex', [
            'posts' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'tag']),
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