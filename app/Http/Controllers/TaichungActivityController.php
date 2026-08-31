<?php

namespace App\Http\Controllers;

use App\Helpers\DBHelper;
use App\Helpers\DBHelperTaichung;
use App\Helpers\Tools;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaichungActivityController extends Controller
{
    public function purchase(Request $request)
    {
        $userId = $this->validUserId($request);
        if (!$userId) {
            return redirect()->route('taichung.login');
        }

        return view('taichung.purchase', array_merge($this->pageData($userId), [
            'activities' => config('taichung.activities'),
        ]));
    }

    public function authorizeActivity(Request $request)
    {
        $userId = $this->validUserId($request);
        if (!$userId) {
            return redirect()->route('taichung.login');
        }

        $activityType = $request->input('activityType');
        $activities = config('taichung.activities');
        if (!is_string($activityType) || !array_key_exists($activityType, $activities)) {
            return redirect()
                ->route('taichung.activities')
                ->with('warning', '請選擇要購買的活動。');
        }

        $password = strtolower(trim((string) $request->input('buyActivityPass')));
        $dailyPassword = strtolower((string) Tools::getBuyCardPassword());
        if ($password === '' || !hash_equals($dailyPassword, $password)) {
            Log::warning('Taichung activity authorization blocked: invalid password', [
                'userId' => $userId,
                'activityType' => $activityType,
            ]);

            return redirect()
                ->route('taichung.activities')
                ->withInput($request->only('activityType'))
                ->with('warning', '今日密碼不正確，請洽工作人員。');
        }

        $businessDate = Carbon::now('Asia/Taipei')->format('Y-m-d');
        $request->session()->put('taichung_activity_authorization', [
            'user_id' => $userId,
            'activity_type' => $activityType,
            'business_date' => $businessDate,
        ]);

        return redirect()->route('taichung.activity');
    }

    public function activity(Request $request)
    {
        $userId = $this->validUserId($request);
        if (!$userId) {
            return redirect()->route('taichung.login');
        }

        $authorization = $this->currentAuthorization($request, $userId);
        if (!$authorization) {
            return redirect()
                ->route('taichung.activities')
                ->with('warning', '請先選擇活動並輸入今日密碼。');
        }

        $activityType = $authorization['activity_type'];
        $activities = config('taichung.activities');
        if (!array_key_exists($activityType, $activities)) {
            $request->session()->forget('taichung_activity_authorization');
            return redirect()->route('taichung.activities');
        }

        $today = Carbon::now('Asia/Taipei');

        return view('taichung.activities', [
            'greetingName' => DBHelper::getUserName($userId),
            'displayDate' => $today->year . '年' . $today->month . '月' . $today->day . '日',
            'activities' => [$activityType => $activities[$activityType]],
            'checkedActivityTypes' => DBHelperTaichung::getTodayActivityTypes(
                $userId,
                $today->format('Y-m-d')
            ),
        ]);
    }

    public function store(Request $request, $activityType)
    {
        $userId = $this->validUserId($request);
        if (!$userId) {
            return redirect()->route('taichung.login');
        }

        $activities = config('taichung.activities');
        if (!array_key_exists($activityType, $activities)) {
            abort(404);
        }

        $businessDate = Carbon::now('Asia/Taipei')->format('Y-m-d');
        $authorization = $this->currentAuthorization($request, $userId);
        if (!$authorization || $authorization['activity_type'] !== $activityType) {
            return redirect()
                ->route('taichung.activities')
                ->with('warning', '請先選擇活動並輸入今日密碼。');
        }

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
            ->route('taichung.activity')
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
            ->route('taichung.activity')
            ->with('warning', '今天已完成「' . $activity['label'] . '」報到，不可重複點選。');
    }

    private function pageData($userId)
    {
        $today = Carbon::now('Asia/Taipei');

        return [
            'greetingName' => DBHelper::getUserName($userId),
            'displayDate' => $today->year . '年' . $today->month . '月' . $today->day . '日',
        ];
    }

    private function currentAuthorization(Request $request, $userId)
    {
        $authorization = $request->session()->get('taichung_activity_authorization');
        if (!$authorization
            || ($authorization['user_id'] ?? null) !== $userId
            || ($authorization['business_date'] ?? null) !== Carbon::now('Asia/Taipei')->format('Y-m-d')) {
            $request->session()->forget('taichung_activity_authorization');
            return null;
        }

        return $authorization;
    }

    private function validUserId(Request $request)
    {
        $userId = $this->currentUserId($request);
        if (!$userId || !DBHelper::getUser($userId)) {
            return null;
        }

        return $userId;
    }

    private function currentUserId(Request $request)
    {
        if (!$request->session()->get('taichung_authenticated')) {
            return null;
        }

        return $request->session()->get('line_user_id');
    }
}
