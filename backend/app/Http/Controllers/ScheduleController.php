<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;
use App\Models\Media;

class ScheduleController extends Controller
{
    /**
     * スケジュールの取得
     */
    public function index()
    {
        $schedules = Schedule::where('user_id', Auth::id())->with('media')->get();

        // カレンダー用のイベント形式に変換
        $events = $schedules->map(function($schedule) {
            return [
                'title' => $schedule->title,
                'start' => $schedule->date,
                'allDay' => true,
                'extendedProps' => [
                    'id' => $schedule->id,
                    'work_id' => $schedule->media_id,
                    'poster_path' => $schedule->media->poster_path ?? null,
                    'overview' => $schedule->media->overview ?? null,
                ],
            ];
        });

        return response()->json($events);
    }


    /**
     * スケジュールの追加
     */
    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'date' => 'required|date',
        'work_id' => 'nullable|exists:media,id',
    ]);

    $schedule = Schedule::create([
        'user_id' => Auth::id(),
        'media_id' => $request->work_id,
        'title' => $request->title,
        'date' => $request->date,
    ]);

    // メディア情報を取得
    $media = null;
    if ($request->work_id) {
        $media = Media::find($request->work_id);
    }

    // 作成したイベントデータを返す
    return response()->json([
        'message' => 'スケジュールが追加されました。',
        'schedule' => [
            'title' => $schedule->title,
            'start' => $schedule->date,
            'allDay' => true,
            'extendedProps' => [
                'id' => $schedule->id,
                'work_id' => $schedule->media_id,
                'poster_path' => $media->poster_path ?? null,
                'overview' => $media->overview ?? null,
            ],
        ],
    ], 201);
}

    public function destroy($id)
    {
        $schedule = Schedule::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$schedule) {
            return response()->json(['message' => 'スケジュールが見つかりません。'], 404);
        }

        $schedule->delete();

        return response()->json(['message' => 'スケジュールが削除されました。'], 200);
    }

}
