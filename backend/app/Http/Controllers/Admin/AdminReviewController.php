<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\User;

class AdminReviewController extends Controller
{
    /**
     * 指定されたユーザーのレビュー一覧を取得します。
     *
     * @param Request $request
     * @param int $id ユーザーID
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $id)
    {
        $query = Review::where('user_id', $id)->with('media', 'user');

        // フィルタリング
        if ($request->filled('username')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->username . '%');
            });
        }

        if ($request->filled('media_title')) {
            $query->whereHas('media', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->media_title . '%');
            });
        }

        if ($request->filled('rating_min')) {
            $query->where('rating', '>=', $request->rating_min);
        }

        if ($request->filled('rating_max')) {
            $query->where('rating', '<=', $request->rating_max);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $reviews = $query->orderBy('created_at', 'desc')->get()->map(function($review) {
            return [
                'id' => $review->id,
                'media_title' => $review->media->title,
                'username' => $review->user->username,
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
            ];
        });

        return response()->json($reviews);
    }

    /**
     * 指定されたレビューを削除します。
     *
     * @param int $id レビューID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['message' => 'レビューが削除されました。'], 200);
    }
}