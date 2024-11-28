<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
        $this->baseUrl = 'https://api.themoviedb.org/3';
    }

    // マルチサーチメソッド
    public function searchMulti($query, $page = 1)
    {
        try {
            $response = Http::get('https://api.themoviedb.org/3/search/multi', [
                'api_key' => $this->apiKey,
                'query' => $query,
                'page' => $page,
                'language' => 'ja-JP',
                'include_adult' => false,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                // エラーハンドリング
                throw new \Exception('TMDb API エラー: ' . $response->body());
            }
        } catch (\Exception $e) {
            // 例外のエラーハンドリング
            throw new \Exception('サーバーエラー: ' . $e->getMessage());
        }
    }

    // メディア詳細取得メソッド
    public function getMediaDetails($mediaType, $id)
    {
        $endpoint = ($mediaType === 'tv') ? 'tv' : 'movie';

        try {
            $response = Http::get("https://api.themoviedb.org/3/{$endpoint}/{$id}", [
                'api_key' => $this->apiKey,
                'language' => 'ja-JP',
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                // エラーハンドリング
                throw new \Exception('TMDb API エラー: ' . $response->body());
            }
        } catch (\Exception $e) {
            // 例外のエラーハンドリング
            throw new \Exception('サーバーエラー: ' . $e->getMessage());
        }
    }

    // ディスカバー機能を使用して作品を取得
    public function discover($media_type, $page = 1, $sort_by = 'popularity.desc')
    {
        $endpoint = $media_type === 'tv' ? 'discover/tv' : 'discover/movie';

        $response = Http::get("{$this->baseUrl}/{$endpoint}", [
            'api_key' => $this->apiKey,
            'language' => 'ja-JP',
            'sort_by' => $sort_by,
            'page' => $page,
            'include_adult' => false,
        ]);

        return $response->json();
    }
}
