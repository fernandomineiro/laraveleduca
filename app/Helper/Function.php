<?php

namespace App\Helper;


    if (! function_exists('start_datetime_db')) {

        function start_datetime_db($date)
        {
            return date_db($date) . ' 00:00:00';
        }
    }

