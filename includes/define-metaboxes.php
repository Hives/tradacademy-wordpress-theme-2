<?php
/**
*   -----------------DEFINE CUSTOM METABOXES----------------------
*/

// add cmb custom metaboxes
// https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
function be_sample_metaboxes( $meta_boxes ) {
    $prefix = '_cmb_'; // Prefix for all fields

    // course: location
    $meta_boxes[] = array(
        'id' => 'location_metabox',
        'title' => 'Location',
        'pages' => array('course'), // post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Location',
                'id' => $prefix . 'location',
                'type' => 'pick_location',
            ),
        ),
    );

    // course: tutor
    $meta_boxes[] = array(
        'id' => 'tutor_metabox',
        'title' => 'Tutor',
        'pages' => array('course'), // post type
        'context' => 'normal',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Name',
                'id' => $prefix . 'tutor',
                'type' => 'text_medium'
            ),
        ),
    );

    // course: payment details
    $meta_boxes[] = array(
        'id' => 'payment_metabox',
        'title' => 'Payment',
        'pages' => array('course'), // post type
        'context' => 'normal',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Payment details',
                'desc' => 'Theme will append "(please email us about concessions)" unless text contains "free"',
                'id' => $prefix . 'payment',
                'type' => 'text'
            ),
        ),
    );

    // location: short address
    $meta_boxes[] = array(
        'id' => 'course_short_location_metabox',
        'title' => 'Short address',
        'pages' => array('location'), // post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Short address',
                'desc' => 'e.g. "Chats Palace, Hackney"',
                'id' => $prefix . 'short_address',
                'type' => 'text_medium'
            ),
        )
    );

    // location: full address
    $meta_boxes[] = array(
        'id' => 'course_location_metabox',
        'title' => 'Full address',
        'pages' => array('location'), // post type
        'context' => 'normal',
        'priority' => 'default',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'Address 1',
                'desc' => '(First line of the address, not the name of the venue)',
                'id' => $prefix . 'address_1',
                'type' => 'text_medium'
            ),
            array(
                'name' => 'Address 2',
                'id' => $prefix . 'address_2',
                'type' => 'text_medium'
            ),
            array(
                'name' => 'Address 3',
                'id' => $prefix . 'address_3',
                'type' => 'text_medium'
            ),
            array(
                'name' => 'City',
                'id' => $prefix . 'city',
                'type' => 'text_medium'
            ),
            array(
                'name' => 'Post Code',
                'id' => $prefix . 'post_code',
                'type' => 'text_medium'
            ),
            array(
                'name' => 'Map',
                'id' => $prefix . 'google_map_data',
                'type' => 'google_map'
            ),
        ),
    );

    // location: url
    $meta_boxes[] = array(
        'id' => 'course_location_url_metabox',
        'title' => 'Venue website',
        'pages' => array('location'), // post type
        'context' => 'normal',
        'priority' => 'low',
        'show_names' => true, // Show field names on the left
        'fields' => array(
            array(
                'name' => 'URL',
                'id' => $prefix . 'url',
                'type' => 'text_medium'
            ),
        )
    );

    return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'be_sample_metaboxes' );

// Initialize the metabox class
add_action( 'init', 'be_initialize_cmb_meta_boxes', 9999 );
function be_initialize_cmb_meta_boxes() {
    if ( !class_exists( 'cmb_Meta_Box' ) ) {
        require_once( get_template_directory() . '/lib/metaboxes/init.php' );
    }
}


/**
* Define custom meta box field types:
* - pick number of weeks
* - course duration picker
* - location picker
* - google maps
*/
add_action( 'cmb_render_pick_no_weeks', 'rrh_cmb_render_pick_no_weeks', 10, 2 );
function rrh_cmb_render_pick_no_weeks( $field, $meta ) {

    $values = array(1,2,3,4,5,6,7,8,9,10);

    echo '<select class="cmb_select_small" name="', $field['id'], '" id="', $field['id'], '">';
    foreach ( $values as $value ) {
        $selected = $value == $meta ? " selected" : "";
        echo '<option value="' . $value . '"' . $selected .'>' . $value . '</option>';
    }
    echo '</select>';
    echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';
}

add_action( 'cmb_render_pick_duration', 'rrh_cmb_render_pick_duration', 10, 2 );
function rrh_cmb_render_pick_duration( $field, $meta ) {

    $durations = array();
    // populate array with multiples of 1800 = half an hour of unix time
    for ($i=1; $i <= 8; $i++) {
        $durations[] = array(
            "value" => $i * 1800,
            // "label" => $i . ":" . $i % 2 === 0 ? "00" : "30"
            "label" => floor($i/2) . ":" . ((intval($i) % 2 === 0) ? "00" : "30")
        );
    }
    // error_log(print_r($meta));
    $meta = $meta ? $meta : 3600;

    echo '<select class="cmb_select_small" name="', $field['id'], '" id="', $field['id'], '">';
    foreach ( $durations as $duration ) {
        $selected = $duration['value'] == $meta ? " selected" : "";
        echo '<option value="' . $duration['value'] . '"' . $selected .'>' . $duration['label'] . '</option>';
    }
    echo '</select>';
    echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';
}

add_action( 'cmb_render_pick_location', 'rrh_cmb_render_pick_location', 10, 2 );
function rrh_cmb_render_pick_location( $field, $meta ) {
    global $wpdb;

    $locations = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'location' AND post_status = 'publish'" );

    echo '<select name="', $field['id'], '" id="', $field['id'], '">';
    foreach ( $locations as $location ) {
        $selected = $location->ID == $meta ? " selected" : "";
        echo '<option value="' . $location->ID . '"' . $selected . '>' . $location->post_title . '</option>';
    }
    echo '</select>';
    echo '<span class="cmb_metabox_description">', $field['desc'], '</span>';
}

add_action( 'cmb_render_google_map', 'rrh_cmb_render_google_map', 10, 2 );
function rrh_cmb_render_google_map( $field, $meta ) {
    echo "<input type='hidden' value='" . $meta . "' name='" . $field['id'] . "' id='" . $field['id'] . "'>";
    echo "<div id='_cmb_google_map'></div>";
}

