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
        $query      = $request->input('query');
        $page       = $request->input('page', 1);
        $sort_by    = $request->input('sort_by', 'popularity.desc');
        $media_type = $request->input('media_type', 'movie');

        Log::info('TMDb API マルチサーチリクエスト', [
            'query'      => $query,
            'page'       => $page,
            'sort_by'    => $sort_by,
            'media_type' => $media_type,
        ]);

        try {
            if ($query) {
                // クエリがある場合はマルチサーチ
                $results = $this->tmdbService->searchMulti($query, $page);
            } else {
                // クエリがない → ディスカバー
                $results = $this->tmdbService->discover($media_type, $page, $sort_by);
            }

            if (isset($results['error'])) {
                return response()->json(['error' => 'TMDb API エラー'], 500);
            }

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('TMDb API リクエスト失敗', [
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'サーバーエラー'], 500);
        }
    }

    // 詳細情報取得
    public function show($media_type, $tmdb_id)
    {
        try {
            // tmdb_id を使用してメディア詳細を取得
            $dbMedia = Media::where('tmdb_id', $tmdb_id)->first();

            $media = $this->tmdbService->getMediaDetails($media_type, $tmdb_id);

            if ($media) {
                // DB に存在する場合は DB の id を返す
                if ($dbMedia) {
                    // DB の情報を反映
                    $media['id']         = $dbMedia->id;        // DBのオートインクリメントID
                    $media['tmdb_id']    = $dbMedia->tmdb_id;   // TMDbのID
                    $media['media_type'] = $dbMedia->media_type;
                    \Log::info($media);

                } else {
                    // DB に無い場合は id を null にし、tmdb_id をそのまま返す
                    $media['id']         = null;
                    $media['tmdb_id']    = $tmdb_id;
                    $media['media_type'] = $media_type;
                }

                return response()->json($media);
            } else {
                return response()->json(['message' => 'Media not found'], 404);
            }
        } catch (\Exception $e) {
            Log::error('TMDb API リクエスト失敗', [
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => 'サーバーエラー'], 500);
        }
    }

    // メディア登録
    public function register(Request $request, $media_type, $tmdb_id)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            // TMDbから作品詳細を取得
            $mediaDetails = $this->tmdbService->getMediaDetails($media_type, $tmdb_id);
            if (!$mediaDetails) {
                return response()->json(['message' => 'Media not found'], 404);
            }

            // 既に登録されているか確認
            $isRegistered = $user->media()->where('tmdb_id', $tmdb_id)->exists();
            if ($isRegistered) {
                return response()->json([
                    'message' => 'この作品は既に登録されています。'
                ], 400);
            }

            // メディア情報を保存
            $media = Media::firstOrCreate(
                ['tmdb_id' => $tmdb_id],
                [
                    'title'        => $mediaDetails['title'] ?? $mediaDetails['name'] ?? 'タイトル不明',
                    'release_date' => $mediaDetails['release_date'] ?? $mediaDetails['first_air_date'] ?? null,
                    'poster_path'  => $mediaDetails['poster_path'] ?? '',
                    'overview'     => $mediaDetails['overview'] ?? '',
                    'media_type'   => $media_type,
                ]
            );

            // ユーザーとメディアの関連付け
            $user->media()->attach($media->id, ['status' => 1]);

            DB::commit();
            return response()->json(['message' => '作品を登録しました。'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('作品の登録に失敗しました。', [
                'message' => $e->getMessage()
            ]);
            return response()->json(['error' => '作品の登録に失敗しました。'], 500);
        }
    }

    // 作品の登録状態を確認
    public function isRegistered($media_type, $media_id)
    {
        $user         = Auth::user();
        $isRegistered = $user->media()->where('tmdb_id', $media_id)->exists();

        return response()->json(['isRegistered' => $isRegistered]);
    }

    // レビューを取得（必要に応じて）
    public function getReviews($media_type, $media_id)
    {
        // 現状は空の配列を返す例
        return response()->json([]);
    }
}
