<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ClassCardService;
use App\Helpers\DBHelper as DBHelper;

class ClassCardController extends Controller
{
    protected $classcardService;

    public function __construct(ClassCardService $classcardService)
    {
        $this->classcardService = $classcardService;
    }


    public function registeclassByPoint($point, $cardId)
    {
        DBHelper::registeclassByPoint($cardId, $point);
        DBHelper::insertConsume($cardId, $point);
        return redirect('classcard/' . $cardId);
    }

    public function buyClassCard(Request $request)
    {
        $userId = $request->userId;
        DBHelper::buyNewCard($userId);

        $card = DBHelper::getValidCard($userId);
        return redirect('classcard/' . $card['CardID']);
    }

    public function showClassCard($cardId)
    {
        $card = DBHelper::getCard($cardId);
        if (!$card) {
            print_r('無此課卡');
            return;
        }
        Log::info("showClassCard({$cardId})");
        return view('classcard', [
            'card' => $card,
        ]);
    }
}
