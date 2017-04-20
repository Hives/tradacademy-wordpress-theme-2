<?php
/**
*   vv Old date picker vv
*/

add_action( 'add_meta_boxes', 'ta_add_date_box' );

// backwards compatible (before WP 3.0)
// add_action( 'admin_init', 'myplugin_add_custom_box', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'ta_save_date_data' );

/* Adds a box to the main column on the course edit screens */
function ta_add_date_box() {
        $screens = array( 'course' );
        foreach ($screens as $screen) {
                add_meta_box(
                        'ta_date_picker',
                        __( 'Time and Date', 'ta_date_picker' ),
                        'ta_print_date_box',
                        $screen
                );
        }
}

/* Prints the box content */
function ta_print_date_box( $post ) {

    // Use get_post_meta to retrieve an existing value from the database and use the value for the form
    $meta = get_post_meta( $post->ID );

    if (array_key_exists('_ta_initial_date', $meta)) {
        $datestamp = intval($meta['_ta_initial_date'][0]);
        if ($datestamp === 0) {
            $datestamp = "";
            $time = date("G:i", 18 * 60 * 60);
        } else {
            $time = date("G:i", $datestamp);
        }
    } else {
        $datestamp = '';
        $time = date("G:i", 18 * 60 * 60);
    }

    if (array_key_exists('_ta_duration', $meta)) {
        $duration = intval($meta['_ta_duration'][0]);
        $duration = $duration === 0 ? 3600 : $duration;
    } else {
        $duration = 3600;
    }

    if (array_key_exists('_ta_num_weeks', $meta)) {
        $num_weeks = intval($meta['_ta_num_weeks'][0]);
    } else {
        $num_weeks = 4;
    }


    $times = array();
    for ($i=9; $i < 22; $i++) {
        $times[] = $i . ":00";
        $times[] = $i . ":30";
    }

    // store multiples of 1800 = 30 mins unix time
    $durations = array();
    for ($i=1; $i <= 8; $i++) {
        $hours = floor($i/2);
        $minutes = ((intval($i) % 2 === 0) ? "00" : "30");
        $durations[] = array(
            "value" => 1800 * $i,
            "label" => $hours . ":" . $minutes
        );
    }


    // Use nonce for verification
    wp_nonce_field( plugin_basename( __FILE__ ), 'ta_date_picker_noncename' ); ?>

    <table class="form-table cmb_metabox">
        <tr>
            <th style="width:18%"><label for="">
                <label for="ta_initial_date_field"><?php _e("Date (initial)", 'ta_date_picker' ); ?></label>
            </th>
            <td>
                <input type="text" id="ta_initial_date_field_human" name="ta_initial_date_field_human" value="<?php echo $datestamp !== "" ? date("d/m/Y", $datestamp) : ""; ?>" size="25" />
                <input type="text" id="ta_initial_date_field" name="ta_initial_date_field" value="<?php echo $datestamp !== "" ? date("Y-m-d", $datestamp) : ""; ?>" />
            </td>
        </tr>
        <tr>
            <th style="width:18%"><label for="">
                <label for="ta_time_field"><?php _e("Start time", 'ta_time_picker' ); ?></label>
            </th>
            <td>
                <select class="cmb_select_small" name="ta_time_field" id="ta_time_field">
                <?php foreach ( $times as $t ) {
                    $selected = $t === $time ? " selected" : "";
                    echo '<option value="' . $t . '"' . $selected . '>' . $t . '</option>';
                } ?>
                </select>
                <span class="cmb_metabox_description"></span>
            </td>
        </tr>
        <tr>
            <th style="width:18%"><label for="">
                <label for="ta_duration_field"><?php _e("Duration", 'ta_duration_picker' ); ?></label>
            </th>
            <td>
                <select class="cmb_select_small" name="ta_duration_field" id="ta_duration_field">
                <?php foreach ( $durations as $d ) {
                    $selected = $d['value'] === $duration ? " selected" : "";
                    echo '<option value="' . $d['value'] . '"' . $selected . '>' . $d['label'] . '</option>';
                } ?>
                </select>
                <span class="cmb_metabox_description">Hours</span>
            </td>
        </tr>
        <tr>
            <th style="width:18%"><label for="">
                <label for="ta_weeks_field"><?php _e("Number of weeks", 'ta_num_weeks' ); ?></label>
            </th>
            <td>
                <select class="cmb_select_small" name="ta_weeks_field" id="ta_weeks_field">
                <?php for ($i=1; $i < 11; $i++) {
                    $selected = $i == $num_weeks ? " selected" : "";
                    echo '<option value="' . $i . '"' . $selected .'>' . $i . '</option>';
                } ?>
                </select>
                <span class="cmb_metabox_description"></span>
            </td>
        </tr>
    </table>




    <?php

}

/* When the post is saved, saves our custom data */
function ta_save_date_data( $post_id ) {

    // First we need to check if the current user is authorised to do this action.
    if ( 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) )
                return;
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) )
                return;
    }

    // Secondly we need to check if the user intended to change this value.
    if ( ! isset( $_POST['ta_date_picker_noncename'] ) || ! wp_verify_nonce( $_POST['ta_date_picker_noncename'], plugin_basename( __FILE__ ) ) )
            return;

    // Thirdly we can save the values to the database

    //if saving in a custom table, get post_ID
    $post_ID = $_POST['post_ID'];

    //sanitize user input
    $datestamp = strtotime(sanitize_text_field( $_POST['ta_initial_date_field'] ) . " " . $_POST['ta_time_field']);
    $num_weeks = intval($_POST['ta_weeks_field']);
    $duration = $_POST['ta_duration_field'];


    // save initial date field
    add_post_meta($post_ID, '_ta_initial_date', $datestamp, true) or
        update_post_meta($post_ID, '_ta_initial_date', $datestamp);

    // save duration
    add_post_meta($post_ID, '_ta_duration', $duration, true) or
        update_post_meta($post_ID, '_ta_duration', $duration);

    // save number of weeks
    add_post_meta($post_ID, '_ta_num_weeks', $num_weeks, true) or
        update_post_meta($post_ID, '_ta_num_weeks', $num_weeks);


    // // save individual events
    // delete_post_meta( $post_id, '_ta_course_event' );
    // for ($i=0; $i < $num_weeks; $i++) {
    //  add_post_meta($post_ID, '_ta_course_event', strval($date->getTimestamp()));
    //  $date->add($week);
    // }

}
