<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WorkController extends Controller
{
    public function index()
    {
        // ダミー例：作品の一覧を返す
        $works = [
            ['id' => 1, 'title' => '作品1', 'description' => '説明1'],
            ['id' => 2, 'title' => '作品2', 'description' => '説明2'],
            // ...
        ];

        return response()->json($works);
    }
}
