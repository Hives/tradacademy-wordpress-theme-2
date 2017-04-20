<?php
/**
*   -----------------UTILITIES----------------------
*/


function dump($v) {
    echo "<pre>" . print_r($v, true) . "</pre>";
}

function error($v) {
    error_log(print_r($v, TRUE));
}

function timestamp_to_date($s) {
    dump( date('l jS \of F Y h:i:s A', $s) );
}

function get_current_courses($var) {
    $ID = $var->ID;
    $final_date = get_end_date_from_course_ID($ID);
    // $meta = get_post_meta( $var->ID, $key = '', $single = false );
    // $initial_date = $meta['_ta_initial_date'][0];
    // $num_weeks = $meta['_ta_num_weeks'][0];
    // $final_date = strtotime("+" . ($num_weeks - 1) . " weeks", $initial_date);

    if ( $final_date > time() ) {
        return true;
    } else {
        return false;
    }

/*
function get_end_date_from_course_ID($ID) {

    $dateblocks = get_course_date_blocks($ID);
    $end_date = get_end_date_from_course_date_blocks($dateblocks);
    return $end_date;

}
*/

}

function event_sorter($a, $b)
{
    if ($a['start timestamp'] == $b['start timestamp']) {
        return 0;
    }
    return ($a['start timestamp'] < $b['start timestamp']) ? -1 : 1;
}