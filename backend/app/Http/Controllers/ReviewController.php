<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    // 全体のレビューを取得（ランキング用）
    public function getReviewRanking()
    {
        $ranking = DB::table('reviews')
            ->select(
                'media.id as media_id',
                'media.tmdb_id',
                'media.media_type',
                'media.title',
                'media.poster_path',
                DB::raw('COUNT(reviews.id) as review_count'),
                DB::raw('AVG(reviews.rating) as average_rating')
            )
            ->join('media', 'reviews.media_id', '=', 'media.id')
            ->groupBy(
                'media.id',
                'media.tmdb_id',
                'media.media_type',
                'media.title',
                'media.poster_path'
            )
            ->orderByDesc('review_count')
            ->get();
    
        // average_rating を数値型にキャスト
        $ranking = $ranking->map(function ($item) {
            $item->average_rating = (float) $item->average_rating;
            return $item;
        });
    
        return response()->json($ranking);
    }
    

    // 特定のメディアのレビューを取得
    public function index($mediaType, $mediaId)
    {
        // メディアを取得、存在しない場合はTMDb APIから取得して作成
        $media = Media::where('tmdb_id', $mediaId)->first();

        if (!$media) {
            $apiKey = config('services.tmdb.api_key');
            $response = Http::get("https://api.themoviedb.org/3/{$mediaType}/{$mediaId}", [
                'api_key' => $apiKey,
                'language' => 'ja-JP',
            ]);

            if ($response->failed()) {
                return response()->json(['message' => 'メディア情報の取得に失敗しました。'], $response->status());
            }

            $tmdbData = $response->json();

            // メディアを作成
            $media = Media::create([
                'title' => $tmdbData['title'] ?? $tmdbData['name'] ?? 'タイトル不明',
                'media_type' => $mediaType,
                'release_date' => $tmdbData['release_date'] ?? $tmdbData['first_air_date'] ?? null,
                'overview' => $tmdbData['overview'] ?? null,
                'poster_path' => $tmdbData['poster_path'] ?? null,
                'tmdb_id' => $mediaId,
            ]);
        }

        // レビューを取得（ユーザー情報を含める）
        $reviews = Review::where('media_id', $media->id)
            ->with('user') // ユーザー情報を含める
            ->get();

        return response()->json([
            'media' => $media,
            'reviews' => $reviews,
        ]);
    }

    // 全体のレビューを取得
    public function getAllReviews()
    {
        $reviews = Review::with('user', 'media')->get(); // ユーザー情報とメディア情報を含めて取得
        return response()->json($reviews);
    }

    // レビューの投稿
    public function store(Request $request, $mediaType, $mediaId)
    {
        // バリデーション
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        // メディアが存在しない場合は取得または作成
        $media = Media::where('tmdb_id', $mediaId)->first();

        if (!$media) {
            $apiKey = config('services.tmdb.api_key');
            $response = Http::get("https://api.themoviedb.org/3/{$mediaType}/{$mediaId}", [
                'api_key' => $apiKey,
                'language' => 'ja-JP',
            ]);

            if ($response->failed()) {
                return response()->json(['message' => 'メディア情報の取得に失敗しました。'], $response->status());
            }

            $tmdbData = $response->json();

            // メディアを作成
            $media = Media::create([
                'title' => $tmdbData['title'] ?? $tmdbData['name'] ?? 'タイトル不明',
                'media_type' => $mediaType,
                'release_date' => $tmdbData['release_date'] ?? $tmdbData['first_air_date'] ?? null,
                'overview' => $tmdbData['overview'] ?? null,
                'poster_path' => $tmdbData['poster_path'] ?? null,
                'tmdb_id' => $mediaId,
            ]);
        }

        // レビューを作成
        $review = Review::create([
            'user_id' => Auth::id(),
            'media_id' => $media->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json($review, 201);
    }
}
