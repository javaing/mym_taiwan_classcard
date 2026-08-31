<?php

namespace App\Http\Controllers;

use App\Helpers\DBHelper;
use App\Helpers\DBHelperTaichung;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaichungActivityController extends Controller
{
    public function index(Request $request)
    {
        $userId = $this->currentUserId($request);
        if (!$userId) {
            return redirect()->route('taichung.login');
        }

        $user = DBHelper::getUser($userId);
        if (!$user) {
            return redirect()->route('taichung.login');
        }

        $today = Carbon::now('Asia/Taipei');

        return view('taichung.activities', [
            'greetingName' => DBHelper::getUserName($userId),
            'displayDate' => $today->year . '年' . $today->month . '月' . $today->day . '日',
            'activities' => config('taichung.activities'),
            'checkedActivityTypes' => DBHelperTaichung::getTodayActivityTypes(
                $userId,
                $today->format('Y-m-d')
            ),
        ]);
    }

    public function store(Request $request, $activityType)
    {
        $userId = $this->currentUserId($request);
        if (!$userId || !DBHelper::getUser($userId)) {
            return redirect()->route('taichung.login');
        }

        $activities = config('taichung.activities');
        if (!array_key_exists($activityType, $activities)) {
            abort(404);
        }

        $businessDate = Carbon::now('Asia/Taipei')->format('Y-m-d');
        $activity = $activities[$activityType];

        if (DBHelperTaichung::hasCheckedIn($userId, $activityType, $businessDate)) {
            return $this->duplicateResponse($userId, $activityType, $activity, $businessDate);
        }

        $inserted = DBHelperTaichung::insertCashCheckin(
            $userId,
            $activityType,
            $activity,
            $businessDate
        );

        if (!$inserted) {
            return $this->duplicateResponse($userId, $activityType, $activity, $businessDate);
        }

        Log::info('Taichung activity check-in created', [
            'userId' => $userId,
            'activityType' => $activityType,
            'amount' => $activity['amount'],
            'businessDate' => $businessDate,
        ]);

        return redirect()
            ->route('taichung.activities')
            ->with('success', '已完成「' . $activity['label'] . '」報到，現金 '
                . number_format($activity['amount']) . ' 元。');
    }

    private function duplicateResponse($userId, $activityType, $activity, $businessDate)
    {
        Log::warning('Taichung activity check-in blocked: duplicate', [
            'userId' => $userId,
            'activityType' => $activityType,
            'businessDate' => $businessDate,
        ]);

        return redirect()
            ->route('taichung.activities')
            ->with('warning', '今天已完成「' . $activity['label'] . '」報到，不可重複點選。');
    }

    private function currentUserId(Request $request)
    {
        if (!$request->session()->get('taichung_authenticated')) {
            return null;
        }

        return $request->session()->get('line_user_id');
    }
}
