<?php
/**
*   My metabox - new datepicker
*/

/**
 *  Having a go at writing my own metaboxes
 *  http://themefoundation.com/wordpress-meta-boxes-guide/
 */

function ta_course_date_meta() {
    add_meta_box( 'ta_course_date_meta', 'Date', 'ta_course_date_meta_callback', 'course' );
}
add_action( 'add_meta_boxes', 'ta_course_date_meta' );

/**
 * Outputs the content of the meta box
 */
function ta_course_date_meta_callback( $post ) {
    global $wpdb;
    $table = $wpdb->prefix . 'ta_course_dates';

    if ( isset($_GET['post']) ) {
        $post_id = $_GET['post'];
        $saved_date_data = $wpdb->get_results(
            "
            SELECT   *
            FROM     $table
            WHERE    post_id = $post_id
            ORDER BY block_no
            ",
            OBJECT
        );
    } else {
        $saved_date_data = false;
    }

    wp_nonce_field( basename( __FILE__ ), 'ta_course_date_nonce' );

    $init_data = array();    
    if ( $saved_date_data ) {
        foreach ($saved_date_data as $key => $d) {

            $init_data_temp = array(
                'all_day_checkbox' => $d->all_day == 1 ? "checked" : "",
                'all_day_hidden'   => $d->all_day == 1 ? "yes" : "no",

                'repeats_checkbox' => $d->repeats == 1 ? "checked" : "",
                'repeats_hidden'   => $d->repeats == 1 ? "yes" : "no",

                'num_repeats'      => $d->repeats == 1 ? $d->num_repeats : 6,
            );


            if (  ! is_null( $d->start_date_time ) ) {
                $start_date_time = strtotime($d->start_date_time . " UTC");
                $init_data_temp['start_date']       = date( "l, j F, Y", $start_date_time);
                $init_data_temp['start_date_robot'] = date( "Y-m-d", $start_date_time);
                $init_data_temp['start_time']       = date( "H:i", $start_date_time);
            } else {
                $init_data_temp['start_date']       = "";
                $init_data_temp['start_date_robot'] = "";
                $init_data_temp['start_time']       = "";
            }

            if (  ! is_null( $d->end_date_time ) ) {
                $end_date_time = strtotime($d->end_date_time . " UTC");
                $init_data_temp['end_date']       = date( "l, j F, Y", $end_date_time);
                $init_data_temp['end_date_robot'] = date( "Y-m-d", $end_date_time);
                $init_data_temp['end_time']       = date( "H:i", $end_date_time);
            } else {
                $init_data_temp['end_date']       = "";
                $init_data_temp['end_date_robot'] = "";
                $init_data_temp['end_time']       = "";
            }

            $init_data[] = $init_data_temp;

        }

    } else {
        $init_data[] = array(
            'start_date'       => "",
            'start_date_robot' => "",
            'start_time'       => "",

            'end_date'         => "",
            'end_date_robot'   => "",
            'end_time'         => "",

            'all_day_checkbox' => "",
            'all_day_hidden'   => "no",

            'repeats_checkbox' => "",
            'repeats_hidden'   => "no",

            'num_repeats'      => 6,
        );        
    }

    ?>


    <div class="date-blocks">

        <?php foreach ($init_data as $i => $d): ?>
        
            <div class="date-block">
                <div class="date-block-heading">
                    <h3>Date block <span class="date-block-number"><?php echo $i+1; ?></span></h3>
                    <a class="delete-this-date" href="#">Delete this date block</a>            
                </div>
                <div class="date-block-body">
                    <table class="form-table cmb_metabox">
                        <tbody>
                            <tr>
                                <th style="width:18%">
                                    <label>Date/time</label>
                                </th>
                                <td class="date-range-initial">
                                    <input type="text" class="date human start" value="<?=$d['start_date']; ?>" readonly/>
                                    <input type="text" name="ta_date_meta_time_initial[]" class="time start" value="<?=$d['start_time']; ?>" />
                                    to
                                    <input type="text" name="ta_date_meta_time_final[]" class="time end" value="<?=$d['end_time']; ?>" />
                                    <input type="text" class="date human end" value="<?=$d['end_date']; ?>" readonly />
                                    <input type="text" name="ta_date_meta_date_initial[]" class="robot start hidden" value="<?=$d['start_date_robot']; ?>"/>
                                    <input type="text" name="ta_date_meta_date_final[]" class="robot end hidden" value="<?=$d['end_date_robot']; ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <th style="width:18%">
                                    <label>All day</label>
                                </th>
                                <td>
                                    <input type="checkbox" class="all-day-check" <?=$d['all_day_checkbox']; ?>>
                                    <input type="hidden" class="all-day-hidden" name="ta_date_meta_allday[]" value="<?=$d['all_day_hidden']; ?>" />
                                 </td>
                            </tr>
                            <tr>
                                <th style="width:18%">
                                    <label>Repeats</label>
                                </th>
                                <td>
                                    <input type="checkbox" class="repeats-check" <?=$d['repeats_checkbox']; ?>>
                                    <input type="hidden" class="repeats-hidden" name="ta_date_meta_repeats[]" value="<?=$d['repeats_hidden']; ?>" />
                                </td>
                            </tr>
                            <tr class="weeks hidden">
                                <th style="width:18%">
                                    <label>Number of weeks</label>
                                </th>
                                <td>
                                    <select name="ta_date_meta_number_repeats[]">
                                        <?php for ($i=2; $i < 11; $i++) { ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($i == $d['num_repeats'] ? "selected" : ""); ?> >
                                                <?php echo $i; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <p class="cmb_metabox_description final-date"></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endforeach ?>
    
    </div>


    <div class="add-another-date">
        <a href="#">Add another date block</a>
    </div>

<?php

}

/**
 * Saves the custom meta input
 */
function ta_course_date_meta_save( $post_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'ta_course_dates';

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ '_wpnonce' ] ) && wp_verify_nonce( $_POST[ '_wpnonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
 
    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }
 
    // Checks for input and sanitizes/saves if needed
    // if( isset( $_POST[ 'meta-text' ] ) ) {
    //     update_post_meta( $post_id, 'meta-text', sanitize_text_field( $_POST[ 'meta-text' ] ) );
    // }

    // get exisiting IDs
    if( isset( $_POST[ 'post_ID' ] ) ) {
        $post_id = $_POST['post_ID'];
        $saved_date_data = $wpdb->get_results(
            "
            SELECT   *
            FROM     $table
            WHERE    post_id = $post_id
            ORDER BY block_no
            ",
            OBJECT
        );        
    } else {
        $saved_date_data = new stdClass();
    }


    // prepares data for saving
    if( isset( $_POST[ 'ta_date_meta_date_initial' ] ) ) {
        $num_date_blocks = count($_POST['ta_date_meta_date_initial']);
    } else {
        $num_date_blocks = 0;
    }

    $dates_to_save = array();

    for ($i=0; $i < $num_date_blocks; $i++) {
        $dates = array();

        if ( $_POST['ta_date_meta_allday'][$i] == "yes") {
            $dates['all_day'] = true;
        } else {
            $dates['all_day'] = false;
        }

        // only save initial date/time if they're both actually set
        if ( $_POST['ta_date_meta_date_initial'][$i] !== "" && $_POST['ta_date_meta_time_initial'][$i] !== "") {

            $datestring = $_POST['ta_date_meta_date_initial'][$i];
            $timestring = $dates['all_day']
                            ? "0:00"
                            : $_POST['ta_date_meta_time_initial'][$i];
            $timestamp = strtotime($datestring . " " . $timestring);

            // convert time to UTC before saving
            $dates['start_date_time'] = gmdate('Y-m-d H:i', $timestamp);
        }

        // only save final date/time if they're both actually set
        if ( $_POST['ta_date_meta_date_final'][$i] !== "" && $_POST['ta_date_meta_time_final'][$i] !== "") {

            $datestring = $_POST['ta_date_meta_date_final'][$i];
            $timestring = $dates['all_day']
                            ? "23:59"
                            : $_POST['ta_date_meta_time_final'][$i];
            $timestamp = strtotime($datestring . " " . $timestring);

            // convert time to UTC before saving
            $dates['end_date_time'] = gmdate('Y-m-d H:i', $timestamp);
        }

        if ( $_POST['ta_date_meta_repeats'][$i] == "yes") {
            $dates['repeats'] = true;
            $dates['num_repeats'] = $_POST['ta_date_meta_number_repeats'][$i];
        } else {
            $dates['repeats'] = false;
            $dates['num_repeats'] = 1;
        }

        $dates['block_no'] = $i;
        $dates['period'] = "weekly";
        $dates['post_id'] = $_POST['post_ID'];

        $dates_to_save[] = $dates;

    }

    // iterate over whichever is biggest - number of dates already saved, or number of dates to save
    for ($i=0; $i < max( count($dates_to_save), count($saved_date_data) ); $i++) {
        if ( array_key_exists( $i, $saved_date_data ) and array_key_exists( $i, $dates_to_save ) ) {
            // if both exist, save the new date over the old date
            $dates = $dates_to_save[$i];
            $dates['dateblock_id'] = $saved_date_data[$i]->dateblock_id;
            $wpdb->replace(
                $table,
                $dates
            );
        } elseif ( array_key_exists( $i, $dates_to_save ) ) {
            // if only new date exists, save it without specifying id
            $wpdb->insert(
                $table,
                $dates_to_save[$i]
            );
        } elseif ( array_key_exists( $i, $saved_date_data ) ) {
            // if only old date exists, delete it
            $wpdb->delete(
                $table,
                array('dateblock_id' => $saved_date_data[$i]->dateblock_id )
            );
        }

    }
 
}
add_action( 'save_post', 'ta_course_date_meta_save' );

/**
 * Initialises the course date metabox.
 * 1. Create a table in the database to store course dates,
 * 2. Copy all existing course dates into the new database
 * this function runs when the theme is activated
 */
function ta_initialise_course_date_meta( ) {

    global $wpdb;

    $table_name = $wpdb->prefix . "ta_course_dates"; 

    $charset_collate = $wpdb->get_charset_collate();

    // first create the new table

    $sql = "CREATE TABLE $table_name (
      dateblock_id mediumint(9) NOT NULL AUTO_INCREMENT,
      post_id bigint(20) NOT NULL,
      block_no smallint(2) NOT NULL,
      start_date_time datetime,
      end_date_time datetime,
      all_day tinyint(1) NOT NULL,
      repeats tinyint(1) NOT NULL,
      num_repeats smallint(2) DEFAULT 1 NOT NULL,
      period char(255),
      PRIMARY KEY  (dateblock_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // then copy all existing course dates into the new table
    $courses = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_type = 'course'");

    foreach ($courses as $course) {
        $course =  $course->ID;

        $results_initial = $wpdb->get_results("
                        SELECT meta_value
                        FROM wp_postmeta
                        WHERE post_id = $course
                        AND meta_key = '_ta_initial_date'
                        ");
        $results_duration = $wpdb->get_results("
                        SELECT meta_value
                        FROM wp_postmeta
                        WHERE post_id = $course
                        AND meta_key = '_ta_duration'
                        ");
        $results_num_weeks = $wpdb->get_results("
                        SELECT meta_value
                        FROM wp_postmeta
                        WHERE post_id = $course
                        AND meta_key = '_ta_num_weeks'
                        ");

        $dateage = array(
                "post_id" => $course,
                "block_no" => 0,
                "all_day" => false,
                "period" => "weekly"
            );

        if ( 
             array_key_exists(0, $results_initial) &&
             array_key_exists(0, $results_duration) &&
             array_key_exists(0, $results_num_weeks)
            )
        {
            $num_weeks = $results_num_weeks[0]->meta_value;
            $dateage["num_repeats"] = $num_weeks;
            $dateage["repeats"] = ($num_weeks > 1);

            $initial = $results_initial[0]->meta_value;
            $final = $initial + $results_duration[0]->meta_value;

            $dateage['start_date_time'] = date('Y-m-d H:i', $initial);
            // $dateage['start_time'] = date('H:i', $initial);

            $dateage['end_date_time'] = date('Y-m-d H:i', $final);
            // $dateage['end_time'] = date('H:i', $final);

        } else {
            error_log("There was a problem with course_id=" . $course);
        }

        $wpdb->insert(
            $table_name,
            $dateage
        );
        
    }



}
add_action( 'after_switch_theme', 'ta_initialise_course_date_meta' );

