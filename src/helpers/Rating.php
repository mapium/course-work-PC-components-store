<?php

namespace App\Helpers;

class Rating {
    public static function normalize($value) {
        return is_numeric($value) && is_finite((float)$value) ? round(max(0, min(5, (float)$value)), 1) : 0.0;
    }

    public static function fills($value) {
        $rating = self::normalize($value);
        $fills = [];
        for ($star = 0; $star < 5; $star++) {
            $fills[] = (int)round(max(0, min(1, $rating - $star)) * 100);
        }
        return $fills;
    }

    public static function reviewLabel($count) {
        $count = max(0, (int)$count);
        $last = $count % 10;
        $last_two = $count % 100;
        $word = $last === 1 && $last_two !== 11 ? 'отзыв' :
            ($last >= 2 && $last <= 4 && ($last_two < 12 || $last_two > 14) ? 'отзыва' : 'отзывов');
        return $count . ' ' . $word;
    }
}
