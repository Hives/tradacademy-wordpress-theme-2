<?php
/**
*   -----------------FUNCTIONS FOR TEMPLATES AND STUFF----------------------
*/

function print_menu () {
    global $wpdb;

    $pages = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'page' AND post_status = 'publish' ORDER BY menu_order" );

    $pages_associative = array();
    foreach ($pages as $page) {
        $pages_associative[$page->ID] = $page;
    }

    $courses = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'course' AND post_status = 'publish' ORDER BY menu_order" );
    // $$$ change this
    $courses = array_filter($courses, "get_current_courses");

    $tutors = $wpdb->get_results( "SELECT * FROM wp_posts WHERE post_type = 'tutor' AND post_status = 'publish' ORDER BY menu_order" );

    $page_hierarchy = get_page_hierarchy( $pages );
    $page_list = array();
    foreach ($page_hierarchy as $ID => $title) {
        // dump();
        $page_list[] = array('ID' => $ID, 'title' => $pages_associative[$ID]->post_title);
    }

    $parents = array(0);

    echo "<ul class='menu'>";
    while (count($page_list) > 0) {
        // dump($page_list);
        // dump(count($page_list));
        // dump("poo");

        $page = array_shift($page_list);
        
        if (count($page_list) > 0) {
            $next_page_parent = $pages_associative[$page_list[0]['ID']]->post_parent;
        } else {
            $next_page_parent = "all gone";
        }
        echo "<li class='page_item page-item-" . $page['ID'] . "'>";
        echo "<a href='" . get_permalink( $pages_associative[$page['ID']] ) . "'>" . $pages_associative[$page['ID']]->post_title . "</a>";

        // special behaviour to create children of "what's on" page
        if ($page['ID'] == 7){
            echo "<ul>";
            $parents[] = $page['ID'];
            foreach ($courses as $course) {
                echo "<li class='course_item course-item-" . $course->ID . "'>";
                echo "<a href='" . get_permalink( $course->ID ) . "'>" . $course->post_title . "</a>";
                echo "</li>";
            }
            if ($next_page_parent != end($parents)) {
                array_pop($parents);
                echo "</ul>";
            }
        }

        // special behaviour to create children of "tutors" page
        if ($page['ID'] == 11){
            echo "<ul>";
            $parents[] = $page['ID'];
            foreach ($tutors as $tutor) {
                echo "<li class='tutor_item tutor-item-" . $tutor->ID . "'>";
                echo "<a href='" . get_permalink( $tutor->ID ) . "'>" . $tutor->post_title . "</a>";
                echo "</li>";
            }
            if ($next_page_parent != end($parents)) {
                array_pop($parents);
                echo "</ul>";
            }
        }

        if (!in_array($next_page_parent, $parents)) {
            echo "<ul>";
            $parents[] = $next_page_parent;
        } else {
            echo "</li>";
            while ($next_page_parent != end($parents)) {
                array_pop($parents);
                echo "</ul>";
                echo "</li>";
            }
        }
    }
    echo "</ul>";
    ?>

<?php }

function print_course_info ($ID, $brief = false) {
    // $$$ change this
    
    $meta = get_post_meta( $ID );
    // TIME
    print_human_readable_course_dates($ID, $brief);

    /* $$$ this is the old way of doing it. can delete?
    $initial_timestamp = intval($meta['_ta_initial_date'][0]);
    $duration = intval($meta['_ta_duration'][0]);
    $num_weeks = intval($meta['_ta_num_weeks'][0]);

    $initial_date = new DateTime();
    $initial_date->setTimestamp($initial_timestamp);

    if ($num_weeks > 1) {
        $final_date = new DateTime();
        $final_date->setTimestamp($initial_timestamp);
        $final_date->add(new DateInterval("P" . ($num_weeks - 1) . "W"));
        $final_timestamp = $final_date->getTimestamp();
    }

    if ($num_weeks > 1) {
        echo date('l', $initial_timestamp) . "s ∙ ";
        echo date('g:ia', $initial_timestamp) . " - " . date('g:ia', $initial_timestamp + $duration) . " ∙ ";
        echo date('j F Y', $initial_timestamp) . " to " . date('j F Y', $final_timestamp) .  "<br />";
    } else {
        echo date('g:ia', $initial_timestamp) . " - " . date('g:ia', $initial_timestamp + $duration) . ", " ;
        echo date('l d M Y', $initial_timestamp) .  "<br />";
    }
    */

    // PAYMENT
    if (!$brief) {
        $payment = isset($meta['_cmb_payment'][0]) ? $meta['_cmb_payment'][0] : "";
        if ($payment !== "") {
            echo $payment;
            if (strpos(strtolower($payment), "free") === FALSE) {
                echo  " (<a href='mailto:" . get_bloginfo( 'admin_email' ) . "'>email</a> us for information about concessions)<br />";
            } else {
                echo "<br />";
            }
        }
    }

    // LOCATION
    $location = get_post($meta['_cmb_location'][0]);
    $location_meta = get_post_meta($meta['_cmb_location'][0]);
    if (isset($location_meta['_cmb_short_address']) && $location_meta['_cmb_short_address'][0] !== "") {
        $address = $location_meta['_cmb_short_address'][0];
    } elseif (isset($location->post_title)) {
        $address = $location->post_title;
    }
    if ($address != "") {
        echo $address .  "<br />";
    }

    // TUTOR
    $tutor = isset($meta['_cmb_tutor'][0]) ? $meta['_cmb_tutor'][0] : "";
    if ($tutor !== "") {
        echo "With " . $tutor . "<br />";
    }

    // MAIL US
    if (!$brief) {
        echo "<strong>Please <a href='mailto:" . get_bloginfo( 'admin_email' ) . "'>email</a> us to reserve your place</strong><br/>";
    }
}

function print_course_location_map ($meta) {

    $location = get_post($meta['_cmb_location'][0]);
    $location_meta = get_post_meta($meta['_cmb_location'][0]);
    $address_labels = array('_cmb_address_1', '_cmb_address_2', '_cmb_address_3', '_cmb_city', '_cmb_post_code');
    $url = isset($location_meta['_cmb_url']) ? $location_meta['_cmb_url'][0] : "";
    if (isset($location->post_title) && $location->post_title != "") {
        if ($url != "") {
            if (substr($url, 0, 4) != "http://") {
                $url = "http://" . $url;
            }
            $address = "<a href='" . $url . "'>" . $location->post_title . "</a>";
        } else {
            $address = $location->post_title;
        }
    } else {
        $address = "";
    }

    foreach ($address_labels as $label) {
        if (isset($location_meta[$label]) && $location_meta[$label][0] != "") {
            if ($address == "") {
                $address .= $location_meta[$label][0];
            } else {
                $address .= "<br /> " . $location_meta[$label][0];
            }
        }
    }

    if (isset($location_meta['_cmb_google_map_data']) && $location_meta['_cmb_google_map_data'][0] != "") {
        $google_maps_data = $location_meta['_cmb_google_map_data'][0];
    } else {
        $google_maps_data = "false";
    }
    ?>

    <div class="address">
        <p><?php echo $address; ?></p>
    </div>
    <div class="google-map" data-googlemaps='<?php echo $google_maps_data; ?>'></div>
    <a class="reset-map" href="#reset-map">Reset map</a>

<?php }

function get_courses_for_sidebar () {
    global $wpdb;

    $courses = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_type = 'course' AND post_status = 'publish'");

    $courses_expanded = array();
    foreach ($courses as $course) {
        $dateblocks = get_course_date_blocks( $course->ID );

        $meta = get_post_meta( $course->ID );

        $location = get_post( $meta['_cmb_location'][0] );
        $location_meta = get_post_meta( $meta['_cmb_location'][0] );

        if (isset($location_meta['_cmb_short_address']) && $location_meta['_cmb_short_address'][0] !== "") {
            $short_address = $location_meta['_cmb_short_address'][0];
        } elseif (isset($location->post_title) && $location->post_title !== "") {
            $short_address = $location->post_title;
        } else {
            $short_address = false;
        }

        // $$$
        foreach ($dateblocks as $dateblock) {
    
            $start_date_time = new DateTime ( $dateblock->start_date_time . " UTC" );
            $start_date_time->setTimezone(new DateTimeZone('Europe/London'));
            $end_date_time = new DateTime ( $dateblock->end_date_time . " UTC" );
            $end_date_time->setTimezone(new DateTimeZone('Europe/London'));
            $num_weeks = intval($dateblock->num_repeats);

            for ($i=0; $i < $num_weeks; $i++) {

                $start_timestamp = $start_date_time->getTimestamp();
                $end_timestamp = $end_date_time->getTimestamp();

                $courses_expanded[] = array(
                    "start timestamp" => $start_timestamp,
                    "end timestamp" => $end_timestamp,
                    "allday" => $dateblock->all_day,
                    // "duration" => $duration,
                    "url" => get_permalink( $course->ID ),
                    "course" => $course,
                    "location" => $location,
                    "short address"  => $short_address,
                );

                $start_date_time->add(new DateInterval("P1W"));
                $end_date_time->add(new DateInterval("P1W"));
            }
    
        }

    }

    usort($courses_expanded, 'event_sorter');

    return $courses_expanded;

}

function get_course_date_blocks ($ID) {
    global $wpdb;
    $table = $wpdb->prefix . 'ta_course_dates';

    // $$$ what if this fails? need to return false
    $dateblocks = $wpdb->get_results("SELECT * FROM $table WHERE post_id = $ID");

    return $dateblocks;    
}

function get_end_date_from_course_ID($ID) {

    $dateblocks = get_course_date_blocks($ID);
    $end_date = get_end_date_from_course_date_blocks($dateblocks);
    return $end_date;

}

function get_end_date_from_course_date_blocks($dateblocks) {
    // $$$ could replace this function by cacheing the value every time a course is saved
    // $$$ could do with testing this over daylight savings etc?

    if ($dateblocks) {

        $end_dates = array();

        foreach ($dateblocks as $dateblock) {

            $end_of_first_session = new DateTime($dateblock->end_date_time . " UTC");

            if ($dateblock->repeats) {

                if ($dateblock->period === "weekly") {

                    $repeats = new DateInterval("P" . ($dateblock->num_repeats - 1) . "W");

                    // need to set timezone to London so that adding X weeks respects daylight savings time
                    // (i think)
                    $end_of_first_session->setTimezone(new DateTimeZone('Europe/London'));
                    $end_of_course = $end_of_first_session->add($repeats);

                    $end_dates[] = $end_of_course->getTimestamp();
                
                } else {
                    // all dateblock periods are set to weekly at the moment so this should never happen...
                }

            } else {

                $end_dates[] = $end_of_first_session->getTimestamp();

            }

        }

        return(max($end_dates));  
        
    } else {
        return false;
    }

}

function print_human_readable_course_dates($ID, $brief=false) {
    // $$$ sort this out - is it good enough to just join them with "and"?

    $dateblocks = get_course_date_blocks($ID);
    $dateblocks_human_readable = array();
    
    foreach ($dateblocks as $dateblock) {
        $dateblocks_human_readable[] = get_human_readable_single_dateblock($dateblock, $brief);
    }

    echo join(" and ", $dateblocks_human_readable) . "<br>";
}

function get_human_readable_single_dateblock($dateblock, $brief=false) {
    // $$$ this is sort of okish now?


    // if $brief then we just leave out the years.
    if ($brief) {
        $y = "";
    } else {
        $y = " Y";
    }


    $start = new DateTime();
    $start_ts = strtotime($dateblock->start_date_time . " UTC");
    $start->setTimestamp($start_ts);

    $end = new DateTime();
    $end_ts =strtotime($dateblock->end_date_time . " UTC");
    $end->setTimestamp($end_ts);

    // dump($start);
    // dump($end);

    // $start_date = new DateTime($dateblock->start_date);
    // $start_time = new DateTime($dateblock->start_time);
    // $end_date = new DateTime($dateblock->end_date);
    // $end_time = new DateTime($dateblock->end_time);
    $interval = date_diff($start, $end);

    if ($dateblock->repeats) {
        // it repeats

        $num_sessions = $dateblock->num_repeats;

        if ($dateblock->period == "weekly") {
            // repeats weekly

            if ( $interval->days > 0 ) {
                // $$$
                // so it repeats on a weekly basis, but the sessions are > 1 day long?
                // that's weird
                // not gonna return anything
                return "☹";

            } else {
                // sessions start and end on the same day

                $block_end_date = new DateTime ($dateblock->end_date_time . " + " . ($num_sessions - 1) . " weeks");

                $day = $start->format("l") . "s ∙ ";
                $time = $dateblock->all_day
                    ? ""
                    : $start->format("g:ia") . " - " . $end->format("g:ia") . " ∙ ";

                if ( $start->format("Y") == $block_end_date->format("Y") ) {
                    // start and end dates fall in the same year
                    if ( $start->format("m") == $block_end_date->format("m") ) {
                        // start and end dates fall in the same month
                        // e.g. Thursdays ∙ 7.00pm - 8.30pm ∙ 6th to 20th April 2017
                        $dates = $start->format("jS") . " to " . $block_end_date->format("jS F$y");
                        return $day . $time . $dates;
                    } else {
                        // start and end dates fall in the same year but not the same month
                        // e.g. Thursdays ∙ 7.00pm - 8.30pm ∙ 16th March to 6 April 2017
                        $dates = $start->format("jS F") . " to " . $block_end_date->format("jS F$y");
                        return $day . $time . $dates;
                    }
                } else {
                    // start and end dates fall in different years
                    // e.g. Thursdays ∙ 7.00pm - 8.30pm ∙ 14th December 2017 to 11th January 2018
                    $dates = $start->format("jS F$y") . " to " . $block_end_date->format("jS F$y");
                        return $day . $time . $dates;
                }

            }

        } else {
            // repeat period is something other than "weekly"
            // this should never happen
            return "?!?";
        }
    } else {
        // it's a one off 

        if ($dateblock->all_day) {
            // sessions last all day

            if ($interval->days > 0) {
                // more than one day
                if ( $start->format("Y") == $end->format("Y") ) {
                    // start and end dates fall in the same year
                    if ( $start->format("m") == $end->format("m") ) {
                        // start and end dates fall in the same month
                        // e.g. Thursday 8th - Friday 9th February 2017
                        return $start->format("l jS") . " - " . $end->format("l jS F$y");
                    } else {
                        // start and end dates fall in the same year but not the same month
                        // e.g. Thursday 31st March - Friday 1st April 2017
                        return $start->format("l jS F") . " - " . $end->format("l jS F$y");
                    }
                } else {
                    // start and end dates fall in different years
                    // e.g. Thursday 31st December 2017 - Friday 1st January 2018
                    return $start->format("l jS F$y") . " - " . $end->format("l jS F$y");                
                }

            } else {
                // session is exactly one day
                return $start->format("l jS F$y");
            }

        } else {
            // not all day

            if ($interval->days > 0) {
                // so it's overnight or something?
                // $$$
            } else {
                // just a one off session that doesn't last the whole day
                // e.g. 3:30pm - 5:00pm, Saturday 27th May 2017
                $time = $start->format("g:ia") . " - " . $end->format("g:ia");
                $date = $start->format("jS F$y");
                return $time . " ∙ " . $date;

            }
        }

    }

}

function print_social_media_buttons() { ?>
    <div class="social-media-buttons">
        <div class="g-plus" data-action="share" data-annotations="none"></div>
        <a href="https://twitter.com/share" class="twitter-share-button" data-count="none">Tweet</a>
        <div class="fb-like" data-annotation="none" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true"></div>
    </div>
    <script>
        (function(d,s,id){
            var
                js,
                fjs=d.getElementsByTagName(s)[0],
                p=/^http:/.test(d.location)?'http':'https';

            if(!d.getElementById(id)){
                js=d.createElement(s);
                js.id=id;
                js.src=p+'://platform.twitter.com/widgets.js';
                fjs.parentNode.insertBefore(js,fjs);
            }

        }(document, 'script', 'twitter-wjs'));
    </script>
<?php }

function print_carousel() {
    global $wpdb;

    $args=array(
        'post_type' => 'carousel_page',
        'post_status' => 'publish',
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );
    $query = new WP_Query($args);
    $carousels = $query->posts;

    ?>

    <div class="carousel-full">
        <?php
            $first = true;
            $counter = 0;
            $count = count($carousels);
            foreach ($carousels as $i => $carousel):
                $image = get_the_post_thumbnail( $carousel->ID, 'carousel-full' );
                $class = $first ? 'first' : '';
                $first = false;
                $counter += 1;
                ?>
                <div class="carousel-element <?php echo $class; ?>">
                    <div class="carousel-text">
                        <h2><?php echo $carousel->post_title; ?></h2>
                        <p><?php echo apply_filters('the_content',$carousel->post_content); ?></p>
                        <div class="controls"><a href="#prev" class="prev">&lt;</a> <?php echo $counter; ?>/<?php echo $count; ?> <a href="#next" class="next">&gt;</a></div>
                    </div>
                    <?php print_r($image); ?>
                </div>
        <?php endforeach; ?>
    </div>

<?php }
