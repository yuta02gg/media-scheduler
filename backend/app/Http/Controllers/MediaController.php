<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TmdbService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Media;

class MediaController extends Controller
{
    protected $tmdbService;

    public function __construct()
    {
        $this->tmdbService = new TmdbService();
    }

    // 検索メソッド
    public function search(Request $request)
    {
        $query = $request->input('query');
        $page = $request->input('page', 1);
        $sort_by = $request->input('sort_by', 'popularity.desc'); // デフォルト値を設定
        $media_type = $request->input('media_type', 'movie'); // メディアタイプ（movie または tv）

        Log::info('TMDb API マルチサーチリクエスト', [
            'query' => $query,
            'page' => $page,
            'sort_by' => $sort_by,
            'media_type' => $media_type,
        ]);

        try {
            if ($query) {
                // クエリがある場合はマルチサーチを使用
                $results = $this->tmdbService->searchMulti($query, $page);
            } else {
                // クエリがない場合はディスカバーを使用してソート
                $results = $this->tmdbService->discover($media_type, $page, $sort_by);
            }

            if (isset($results['error'])) {
                return response()->json(['error' => 'TMDb API エラー'], 500);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('TMDb API リクエスト失敗', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'サーバーエラー'], 500);
        }
    }

    // 詳細情報を取得するメソッド
    public function show($media_type, $id)
    {
        try {
            $media = $this->tmdbService->getMediaDetails($media_type, $id);

            if ($media) {
                return response()->json($media);
            } else {
                return response()->json(['message' => 'Media not found'], 404);
            }
        } catch (\Exception $e) {
            Log::error('TMDb API リクエスト失敗', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'サーバーエラー'], 500);
        }
    }

    // メディア登録メソッド
    public function register(Request $request, $media_type, $id)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // TMDbから作品の詳細を取得
            $mediaDetails = $this->tmdbService->getMediaDetails($media_type, $id);

            if (!$mediaDetails) {
                return response()->json(['message' => 'Media not found'], 404);
            }

            // 既に登録されているか確認
            $isRegistered = $user->media()->where('tmdb_id', $id)->exists();
            if ($isRegistered) {
                return response()->json(['message' => 'この作品は既に登録されています。'], 400);
            }

            // メディア情報を保存
            $media = Media::firstOrCreate(
                ['tmdb_id' => $id],
                [
                    'title' => $mediaDetails['title'] ?? $mediaDetails['name'] ?? 'タイトル不明',
                    'release_date' => $mediaDetails['release_date'] ?? $mediaDetails['first_air_date'] ?? null,
                    'poster_path' => $mediaDetails['poster_path'] ?? '',
                    'overview' => $mediaDetails['overview'] ?? '',
                    'media_type' => $media_type,
                ]
            );

            // ユーザーとメディアの関連付けを作成
            $user->media()->attach($media->id, ['status' => 1]);

            DB::commit();

            return response()->json(['message' => '作品を登録しました。'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('作品の登録に失敗しました。', ['message' => $e->getMessage()]);
            return response()->json(['error' => '作品の登録に失敗しました。'], 500);
        }
    }

    // 作品の登録状態を確認するメソッド
    public function isRegistered($media_type, $id)
    {
        $user = Auth::user();
        $isRegistered = $user->media()->where('tmdb_id', $id)->exists();

        return response()->json(['isRegistered' => $isRegistered]);
    }

    // レビューを取得するメソッド
    public function getReviews($media_type, $id)
    {
        // 必要に応じてレビューを取得するロジックを実装
        // ここでは仮に空の配列を返します
        return response()->json([]);
    }
}
