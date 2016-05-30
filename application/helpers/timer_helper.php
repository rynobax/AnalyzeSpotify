<?php

function timer_starts() {
    global $tstart;

    $tstart = microtime(true);
    ;
}

function timer_ends() {
    global $tend;

    $tend = microtime(true);
    ;
}

function timer_calc() {
    global $tstart, $tend;

    return (round($tend - $tstart, 2));
}

?>

