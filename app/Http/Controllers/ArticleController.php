<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show', 'tags','comments']]);
    }

    //
    public function index(Request $request)
    {
        $tag = $request->query('tag');
        $author = $request->query('author');
        $favorited = $request->query('favorited');
        $limit = $request->query('limit') ?? 20;
        $offset = $request->query('offset') ?? 0;

        $query = Article::query();

        if ($tag) {
            $query->where('tagList', 'like', '%' . $tag . '%');
        }

        if ($author) {
            $query->whereHas('author', function ($q) use ($author) {
                $q->where('username', $author);
            });
        }

        if ($favorited) {
            $query->whereHas('favorites', function ($q) use ($favorited) {
                $q->where('user_id', $favorited);
            });
        }

        $query->limit($limit);
        $query->offset($offset);
        $articles = $query->get();
        // ->map(function ($article) {
        //     $article->tagList = explode(",", $article->tagList);
        //     return $article;
        // });

        return response()->json([
            'articles' => [
                $articles,
            ],
            'articlesCount' => $articles->count()
        ]);
    }

    public function feed(Request $request)
    {
        $user = auth()->user();
        $limit = $request->query('limit') ?? 20;
        $offset = $request->query('offset') ?? 0;

        $articles = Article::query()->whereHas('author', function ($q) use ($user) {
            $q->whereHas('followers', function ($q) use ($user) {
                $q->where('follower_id', $user->id);
            });
        })->limit($limit)->offset($offset)->get();

        return response()->json([
            'articles' => [
                $articles,
            ],
            'articlesCount' => $articles->count()
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $article = Article::query()->where('slug', $slug)->first();

        if ($article) {
            return response()->json([
                'article' => $article
            ], 201);
        }

        return response()->json([
            'error' => 'not found'
        ], 404);
    }

    public function create(Request $request)
    {
        $request->validate([
            'article.title' => 'required|string',
            'article.description' => 'required|string',
            'article.body' => 'required|string',
            'article.tagList' => 'array',
            // validates each member of the array
            'article.tagList.*' => 'string'
        ]);

        $titleArray = explode(" ", $request->article['title']);
        // NOTE use " " instead of ' ' for explode and implode
        $slug = implode("-", $titleArray);
        $originalSlug = $slug;
        $counter = 1;

        while (Article::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $article = Article::create([
            'title' => $request->article['title'],
            'description' => $request->article['description'],
            'body' => $request->article['body'],
            'slug' => $slug,
            'tagList' => implode(",", $request->article['tagList']),
            'user_id' => auth()->user()->id,
        ]);

        return response()->json([
            'article' => $article
        ], 201);
    }

    public function update(Request $request, string $slug)
    {
        $request->validate([
            'article.title' => 'string',
            'article.description' => 'string',
            'article.body' => 'string',

        ]);

        $article = Article::query()->where('slug', $slug)->first();
        if (!$article) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        if ($request->article['title']) {
            $titleArray = explode(" ", $request->article['title']);
            // NOTE use " " instead of ' ' for explode and implode
            $slug = implode("-", $titleArray);
            $originalSlug = $slug;
            $counter = 1;

            while (Article::where('slug', $slug)->whereNot('id',$article->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $article->update([
            'title' => $request->article['title'] ?? $article->title,
            'slug' => $request->article['title'] == null ? $article->slug : $slug,
            'description' => $request->article['description'] ?? $article->description,
            'body' => $request->article['body'] ?? $article->body,
        ]);

        return response()->json([
            'article' => $article
        ], 201);
    }

    public function delete(Request $request, string $slug)
    {
        $article = Article::query()->where('slug', $slug)->first();
        if (!$article) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        $article->delete();
        return response()->json([
            'message' => 'article deleted'
        ], 201);
    }

    public function favorite(Request $request, string $slug)
    {
        $article = Article::query()->where('slug', $slug)->first();
        if (!$article) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        $article->addFavorite(auth()->user());
        return response()->json([
            'article' => $article
        ], 201);
    }

    public function unfavorite(Request $request, string $slug)
    {
        $article = Article::query()->where('slug', $slug)->first();
        if (!$article) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        $article->removeFavorite(auth()->user());
        return response()->json([
            'article' => $article
        ], 201);
    }

    public function comments(Request $request, string $slug)
    {
        $article = Article::query()->where('slug', $slug)->first();
        if (!$article) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        $comments = $article->comments()->get();
        return response()->json([
            'comments' => $comments
        ], 200);
    }

    public function addComment(Request $request, string $slug)
    {
        $request->validate([
            'comment.body' => 'required|string',
        ]);

        $article = Article::query()->where('slug', $slug)->first();
        if (!$article) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        $comment = $article->comments()->create([
            'body' => $request->comment['body'],
            'user_id' => auth()->user()->id,
        ]);
        return response()->json([
            'comment' => $comment
        ], 201);
    }

    public function deleteComment(Request $request, string $slug, int $id)
    {
        $article = Article::query()->where('slug', $slug)->first();
        if (!$article) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        $comment = $article->comments()->where('id', $id)->first();
        if (!$comment) {
            return response()->json([
                'error' => 'not found'
            ], 404);
        }
        $comment->delete();
        return response()->json([
            'message' => 'comment deleted'
        ], 201);
    }

    public function tags(Request $request)
    {
        $tags = Article::query()->select('tagList')->latest()->get();
        $tagsArray = [];
        foreach ($tags as $tag) {
            $tagsArray = array_merge($tagsArray,$tag->tagList);
        }
        $tagsArray = array_unique($tagsArray);
        return response()->json([
            'tags' => $tagsArray
        ], 200);
    }
}
