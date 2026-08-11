<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use Illuminate\Http\Request;

/**
 * FR-08 - Basis Pengetahuan (Knowledge Base) / layanan swalayan.
 */
class KnowledgeArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = KnowledgeArticle::with(['category', 'author'])->where('is_published', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->whereFullText(['title', 'content'], $request->search);
        }

        return response()->json($query->orderByDesc('view_count')->paginate($request->get('per_page', 10)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,category_id'],
            'title'        => ['required', 'string', 'max:200'],
            'content'      => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $article = KnowledgeArticle::create([
            ...$data,
            'created_by' => $request->user()->user_id,
        ]);

        return response()->json($article->load(['category', 'author']), 201);
    }

    /**
     * Menampilkan artikel dan menambah hitungan dilihat (view_count).
     */
    public function show(KnowledgeArticle $knowledgeArticle)
    {
        $knowledgeArticle->increment('view_count');

        return response()->json($knowledgeArticle->load(['category', 'author']));
    }

    public function update(Request $request, KnowledgeArticle $knowledgeArticle)
    {
        $data = $request->validate([
            'category_id'  => ['sometimes', 'exists:categories,category_id'],
            'title'        => ['sometimes', 'string', 'max:200'],
            'content'      => ['sometimes', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $knowledgeArticle->update($data);

        return response()->json($knowledgeArticle->fresh(['category', 'author']));
    }

    public function destroy(KnowledgeArticle $knowledgeArticle)
    {
        $knowledgeArticle->delete();

        return response()->json(['message' => 'Artikel berhasil dihapus']);
    }
}
