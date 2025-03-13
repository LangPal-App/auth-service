<?php

function generateRandomNumbers(int $length)
{
    return substr( str_shuffle("01234567890123456789"), 0, $length);
}