<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TmdbService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.tmdb.api_key');
        $this->baseUrl = 'https://api.themoviedb.org/3';
    }

    /**
     * マルチサーチ
     */
    public function searchMulti($query, $page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/search/multi", [
                'api_key'       => $this->apiKey,
                'query'         => $query,
                'page'          => $page,
                'language'      => 'ja-JP',
                'include_adult' => false,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('TMDb API エラー: ' . $response->body());
            }
        } catch (\Exception $e) {
            throw new \Exception('サーバーエラー: ' . $e->getMessage());
        }
    }

    /**
     * メディア詳細取得
     */
    public function getMediaDetails($mediaType, $id)
    {
        $endpoint = ($mediaType === 'tv') ? 'tv' : 'movie';

        try {
            $response = Http::get("{$this->baseUrl}/{$endpoint}/{$id}", [
                'api_key'  => $this->apiKey,
                'language' => 'ja-JP',
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('TMDb API エラー: ' . $response->body());
            }
        } catch (\Exception $e) {
            throw new \Exception('サーバーエラー: ' . $e->getMessage());
        }
    }

    /**
     * ディスカバー機能
     */
    public function discover($media_type, $page = 1, $sort_by = 'popularity.desc')
    {
        $endpoint = ($media_type === 'tv') ? 'discover/tv' : 'discover/movie';

        $response = Http::get("{$this->baseUrl}/{$endpoint}", [
            'api_key'       => $this->apiKey,
            'language'      => 'ja-JP',
            'sort_by'       => $sort_by,
            'page'          => $page,
            'include_adult' => false,
        ]);

        return $response->json();
    }
}
