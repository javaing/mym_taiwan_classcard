<?php

namespace App\Services;

class ClassCardService
{
    public function getLoginBaseUrl($user, $index)
    {
        $url = 'registeclass/' . $index;
        return $url;
    }
}
