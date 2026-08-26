<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class TaichungPreviewController extends Controller
{
    public function buttons()
    {
        return view('taichung.preview-buttons', $this->previewData());
    }

    public function select()
    {
        return view('taichung.preview-select', $this->previewData());
    }

    private function previewData()
    {
        $today = Carbon::now('Asia/Taipei');

        return [
            'greetingName' => 'xx',
            'displayDate' => $today->year . '年' . $today->month . '月' . $today->day . '日',
        ];
    }
}
