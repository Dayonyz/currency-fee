<?php

function ceilToByPrecision(float $number, int $precision = 2): float {
    $factor = 10 ** $precision;
    return ceil($number * $factor) / $factor;
}
