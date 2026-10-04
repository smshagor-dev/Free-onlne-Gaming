<?php

namespace App\Helpers;

class AdsHelper
{
    public static function insertAds($items, $view, $every = 5, $adType = 'display')
    {
        $output = '';
        foreach ($items as $index => $item) {
            $output .= view($view, compact('item'))->render();

            if (($index + 1) % $every === 0) {
                $output .= view("components.ads.$adType")->render();
            }
        }
        return $output;
    }
}
