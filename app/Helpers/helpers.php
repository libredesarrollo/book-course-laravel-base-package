<?php

use Detection\Exception\MobileDetectException;
use Detection\MobileDetect;

function isMobile()
{
    $detect = new MobileDetect;
    // var_dump($detect->getUserAgent());
    try {
        return $detect->isMobile();
    } catch (MobileDetectException $th) {
    }

    return false;
}
