<?php

/* Comparison functions */

function cmp_pop($a, $b) {
    if ($a->popularity == $b->popularity) {
        return 0;
    }
    return ($a->popularity > $b->popularity) ? -1 : 1;
}

function cmp_cnt($a, $b) {
    if ($a->count == $b->count) {
        return 0;
    }
    return ($a->count > $b->count) ? -1 : 1;
}

function cmp_flw($a, $b) {
    if ($a->followers == $b->followers) {
        return 0;
    }
    return ($a->followers > $b->followers) ? -1 : 1;
}
?>

