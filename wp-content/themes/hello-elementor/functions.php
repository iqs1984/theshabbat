<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '2.6.1' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		$hook_result = apply_filters_deprecated( 'elementor_hello_theme_load_textdomain', [ true ], '2.0', 'hello_elementor_load_textdomain' );
		if ( apply_filters( 'hello_elementor_load_textdomain', $hook_result ) ) {
			load_theme_textdomain( 'hello-elementor', get_template_directory() . '/languages' );
		}

		$hook_result = apply_filters_deprecated( 'elementor_hello_theme_register_menus', [ true ], '2.0', 'hello_elementor_register_menus' );
		if ( apply_filters( 'hello_elementor_register_menus', $hook_result ) ) {
			register_nav_menus( [ 'menu-1' => __( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => __( 'Footer', 'hello-elementor' ) ] );
		}

		$hook_result = apply_filters_deprecated( 'elementor_hello_theme_add_theme_support', [ true ], '2.0', 'hello_elementor_add_theme_support' );
		if ( apply_filters( 'hello_elementor_add_theme_support', $hook_result ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			$hook_result = apply_filters_deprecated( 'elementor_hello_theme_add_woocommerce_support', [ true ], '2.0', 'hello_elementor_add_woocommerce_support' );
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', $hook_result ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$enqueue_basic_style = apply_filters_deprecated( 'elementor_hello_theme_enqueue_style', [ true ], '2.0', 'hello_elementor_enqueue_style' );
		$min_suffix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', $enqueue_basic_style ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		$hook_result = apply_filters_deprecated( 'elementor_hello_theme_register_elementor_locations', [ true ], '2.0', 'hello_elementor_register_elementor_locations' );
		if ( apply_filters( 'hello_elementor_register_elementor_locations', $hook_result ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

/**
 * If Elementor is installed and active, we can load the Elementor-specific Settings & Features
*/

// Allow active/inactive via the Experiments
require get_template_directory() . '/includes/elementor-functions.php';

/**
 * Include customizer registration functions
*/
function hello_register_customizer_functions() {
	if ( is_customize_preview() ) {
		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_register_customizer_functions' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check hide title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * Wrapper function to deal with backwards compatibility.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		if ( function_exists( 'wp_body_open' ) ) {
			wp_body_open();
		} else {
			do_action( 'wp_body_open' );
		}
	}
}

wp_enqueue_style( 'example-script', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');

// add_shortcode( 'all-custom-events', 'allcustomevents' );


// function allcustomevents(){

// 	$atts = shortcode_atts(
//         [
//             'id'     => 6019,
//             'user'   => '',
//             'fields' => '',
//             'number' => '',
//             'type'   => 'all', // all, unread, read, or starred.
//             'sort'   => '',
//             'order'  => 'asc',
//         ],
//         $atts
//     );

// 	$entries_args = [
//         'form_id' => absint( $atts[ 'id' ] ),
//     ];

// 	if ( ! empty( $atts[ 'number' ] ) ) {
//         $entries_args[ 'number' ] = absint( $atts[ 'number' ] );
//     }

// 	if ( $atts[ 'type' ] === 'unread' ) {
//         $entries_args[ 'viewed' ] = '0';
//     } elseif( $atts[ 'type' ] === 'read' ) {
//         $entries_args[ 'viewed' ] = '1';
//     } elseif ( $atts[ 'type' ] === 'starred' ) {
//         $entries_args[ 'starred' ] = '1';
//     }

// 	$entries = json_decode(json_encode(wpforms()->entry->get_entries( $entries_args )), true);

// 	if ( empty( $entries ) ) {
//         return '<p>No Event Found.</p>';
//     }

// 	ob_start();

// 	echo'<div class="event-main">';
// 		echo'<div class="row">';
// 				echo'<div class="col-lg-6 col-md-6">';
// 					echo'<h2><a><span>PASSOVER 2023</span>APRIL 4-14</a></h2>';
// 					echo'<iframe class="elementor-video" frameborder="0" allowfullscreen="1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" title="2023 Passover Shabbaton" width="640" height="360" src="https://www.youtube.com/embed/b8OVsuM4YMU?controls=1&amp;rel=0&amp;playsinline=0&amp;modestbranding=0&amp;autoplay=0&amp;enablejsapi=1&amp;origin=https%3A%2F%2Fwww.theshabbat.org&amp;widgetid=1" id="widget2" data-gtm-yt-inspected-8="true"></iframe>';
// 				echo'</div>';
// 			foreach($entries as $entry) 
// 			{
// 				$fields_data = $entry['fields'];
// 				$fields_arr = json_decode($fields_data,true);
// 				$event_status = $fields_arr[19]['value'];
// 				if($event_status == 'Approve')
// 				{
// 					$event_name = $fields_arr[1]['value'];
// 					$from_date = date('F d', strtotime($fields_arr[16]['value']));
// 					$to_date = date('d', strtotime($fields_arr[17]['value']));
// 					$event_image = $fields_arr[18]['value'];
// 					$event_register_url = $fields_arr[22]['value'];
					
// 					echo'<div class="col-lg-6 col-md-6">';
// 						echo'<h2><a href="'.$event_register_url.'"><span>'.$event_name.'</span>'.$from_date.'-'.$to_date.'</a></h2>';
// 						echo'<a href="'.$event_register_url.'"><img alt="" width="100%" src="'.$event_image.'" /></a>';
// 					echo'</div>';
// 				}
// 			}
// 		echo'</div>';
// 	echo'</div>';
//     $output = ob_get_clean();
// 	echo $output;
// }


wp_enqueue_script( 'full-calender-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js');
add_shortcode( 'full-event-calender', 'fullEventCalender' );

function fullEventCalender(){

	$eventJSON = allEventsJSON();

	$eventJSONencode = json_encode($eventJSON);
	echo '
	<style>
	.evt-banner.dd {
		background-color: #ddd;
		padding: 50px 0;
	}
	.evt-banner.dd .col-lg-3 {
		margin: 40px 0 0;
	}
	.calendar-sec .event-form {
		height: 100% !important;
		margin: 0 !important;
	}
	.calendar-sec #calendar {
		background-color: #fff;
		padding: 5px;
	}
	.calendar-sec .fc .fc-scroller-liquid-absolute {
		overflow: unset !important;
	}
	.calendar-sec .fc-theme-standard td, .calendar-sec .fc-theme-standard .fc-theme-standard th {
		background-color: #fff;
	}
	.cal-box {
		background-color: #fff;
		padding: 0 0 15px;
		height: 100%;
		text-align: left;
	}
	.cal-box h3 {
		color: #000;
		font-size: 18px;
		font-weight: bold;
		margin: 12px 15px 0;
	}
	.cal-box h3 span {
		display: block;
		padding: 8px 0 0;
		text-transform: uppercase;
	}
	.cal-box h6 {
		color: #000;
		font-size: 17px;
		margin: 0 15px;
		display: inline-block;
		border-bottom: 1px solid #000;
		padding: 15px 0 5px;
	}
	.cal-img {
		background-color: #eee;
	}
	.cal-img img {
		height: 200px;
		object-fit: contain;
	}
	.cal-box p {
		color: #000;
		font-size: 16px;
		font-weight: 600;
		margin: 0 15px 8px;
	}
	.reg-btn {
		color: #007bff;
		display: inline-block;
		font-size: 16px;
		font-weight: 600;
		margin: 0 15px;
	}
	</style>
	<div class="calendar-sec">
		<div class="row">
			<div class="col-lg-8 col-md-8">
				<div id="calendar"></div>
			</div>
			<div class="col-lg-4 col-md-4" id="event_build_form_content">
				<div class="cal-txt">
					<h3>Reservations</h3>
					<p>Reserve room accommodations, meals, and activities. Schedule your stay and let us know your needs.</p>
					<h3>Join an Event</h3>
					<p>Participate in a public event or activity. Peruse through public events and reserve your tickets
						online.</p>
					<h3>Create Your Own Event</h3>
					<p>Need space for a group, party, or convention? We provide all your needs from lighting, sound, decor,
						staff, meals, room accommodations, and entertainment. We work with private individuals and
						organizations to host their events in a kosher and safe family-friendly resort.</p>
					<a id="show_event_form" href="javascript:void(0)" onclick="javascript:myLinkButtonClick();">Create Event</a>
				</div>
			</div>
			<div id="home_event_build_form" style="display:none;" class="col-lg-4 col-md-4">'.do_shortcode('[wpforms id="6019"]').'</div>
    	</div>
	<script>
		function myLinkButtonClick()
		{
			if(!(document.body.classList.contains("logged-in"))) {
				Swal.fire({
					icon: "info",
					title: "Please login to create event",
					}).then(function () {
					window.location.href = "https://www.theshabbat.org/login";
					})
			} else if(document.body.classList.contains("logged-in")) {
				jQuery("#event_build_form_content").hide();
				jQuery("#home_event_build_form").show();
				
			}
		}
	</script>';
	echo '<div class="row">
	';
	$i=0;
	foreach($eventJSON as $evt){	
	
      echo  '<div class="col-lg-3 col-md-4">
	  <div class="cal-box">
	  <a href="https://www.theshabbat.org/event-view/?event_name='.str_replace(' ', '-', $evt['title']).'&event_id='.$evt['event_id'].'"><div class="cal-img"><img alt="" width="100%" src="'.$evt['image'].'"/></div>
		 <h6>'.date('F d',strtotime($evt['start'])).'-'.date('d',strtotime($evt['end'])).'</h6>
		 <h3>'.$evt['title'].'</h3>
		 <P>'.$evt['host_name'].'</P>';
		 if($evt['event_view_url']){
		 echo '<a class="reg-btn" href="'.$evt['url'].'">Register</a>';
		 }
		echo '</a></div>
	   </div>
	   ';
	   if($i==7){
		   break;
	   }
	   $i++;
	}
	echo '</div>
';	
	if(count($eventJSON)>8){
	echo '<a href="'.home_url('/').'events" class="see-btn">See More</a></div>	
	';
	}
	echo "<script> 
	document.addEventListener('DOMContentLoaded', function() {
	  var calendarEl = document.getElementById('calendar');
	  var calendar = new FullCalendar.Calendar(calendarEl, {
		headerToolbar: {
			left: 'prevYear,prev,next,nextYear today',
			center: 'title',
			right: 'dayGridMonth,dayGridWeek,dayGridDay'
		  },
		  height:566,
		events:$eventJSONencode,
	  });
	  calendar.render();
	});

  </script>";

}

add_shortcode( 'full-event-calender-list', 'fullEventCalenderList' );
function fullEventCalenderList(){

	$eventJSON = allEventsJSON();
	$eventJSONencode = json_encode($eventJSON);
	echo '
	<style>
	.evt-banner.dd {
		background-color: #ddd;
		padding: 50px 0;
	}
	.evt-banner.dd .col-lg-3 {
		margin: 40px 0 0;
	}
	.calendar-sec .event-form {
		height: 100% !important;
		margin: 0 !important;
	}
	.calendar-sec #calendar {
		background-color: #fff;
		padding: 5px;
	}
	.calendar-sec .fc .fc-scroller-liquid-absolute {
		overflow: unset !important;
	}
	.calendar-sec .fc-theme-standard td, .calendar-sec .fc-theme-standard .fc-theme-standard th {
		background-color: #fff;
	}
	.cal-box {
		background-color: #fff;
		padding: 0 0 15px;
		height: 100%;
		text-align: left;
	}
	.cal-box h3 {
		color: #000;
		font-size: 18px;
		font-weight: bold;
		margin: 12px 15px 0;
	}
	.cal-box h3 span {
		display: block;
		padding: 8px 0 0;
		text-transform: uppercase;
	}
	.cal-box h6 {
		color: #000;
		font-size: 17px;
		margin: 0 15px;
		display: inline-block;
		border-bottom: 1px solid #000;
		padding: 15px 0 5px;
	}
	.cal-box p {
		color: #000;
		font-size: 16px;
		font-weight: 600;
		margin: 0 15px 8px;
	}
	.cal-img {
		background-color: #eee;
	}
	.cal-img img {
		height: 200px;
		object-fit: contain;
	}
	.reg-btn {
		color: #007bff;
		display: inline-block;
		font-size: 16px;
		font-weight: 600;
		margin: 0 15px;
	}
	</style>
	<div class="calendar-sec">
   ';
	echo '<div class="row">
	';
	
	foreach($eventJSON as $evt){		
	
      echo  '<div class="col-lg-3 col-md-4">
	  <div class="cal-box">
	  <a href="https://www.theshabbat.org/event-view/?event_name='.str_replace(' ', '-', $evt['title']).'&event_id='.$evt['event_id'].'">
	  <div class="cal-img"><img alt="" width="100%" src="'.$evt['image'].'" /></div>
		 <h6>'.date('F d',strtotime($evt['start'])).'-'.date('d',strtotime($evt['end'])).'</h6>
		 <h3>'.$evt['title'].'</h3>
		 <P>'.$evt['host_name'].'</P>';
		 if($evt['event_view_url']){
			echo '<a class="reg-btn" href="'.$evt['url'].'">Register</a>';
			}
		echo'</a></div>
	   </div>
	   ';
	}
	
	echo '</div></div>';


}

function allEventsJSON(){

	$atts = shortcode_atts(
        [
            'id'     => 6019,
            'user'   => '',
            'fields' => '',
            'number' => '',
            'type'   => 'all', // all, unread, read, or starred.
            'sort'   => '16',
            'order'  => 'asc',
        ],
        $atts
    );

	$entries_args = [
        'form_id' => absint( $atts[ 'id' ] ),
    ];

	if ( ! empty( $atts[ 'number' ] ) ) {
        $entries_args[ 'number' ] = absint( $atts[ 'number' ] );
    }

	if ( $atts[ 'type' ] === 'unread' ) {
        $entries_args[ 'viewed' ] = '0';
    } elseif( $atts[ 'type' ] === 'read' ) {
        $entries_args[ 'viewed' ] = '1';
    } elseif ( $atts[ 'type' ] === 'starred' ) {
        $entries_args[ 'starred' ] = '1';
    }

	$entries = json_decode(json_encode(wpforms()->entry->get_entries( $entries_args )), true);

	if ( !empty( $entries ) ) 
	{
		$eventDataArr = array();
		 foreach($entries as $key => $entry) {
        $entries[$key][ 'fields' ] = json_decode($entry[ 'fields' ], true);
        $entries[$key][ 'meta' ] = json_decode($entry[ 'meta' ], true);
    }
     
    if ( !empty($atts[ 'sort' ]) && isset($entries[0][ 'fields' ][$atts[ 'sort' ]] ) ) {
        if ( strtolower($atts[ 'order' ]) == 'asc' ) {
            usort($entries, function ($entry1, $entry2) use ($atts) {
                return strcmp($entry1[ 'fields' ][$atts[ 'sort' ]][ 'value' ], $entry2[ 'fields' ][$atts[ 'sort' ]][ 'value' ]);
            });         
        } elseif ( strtolower($atts[ 'order' ]) == 'desc' ) {
            usort($entries, function ($entry1, $entry2) use ($atts) {
                return strcmp($entry2[ 'fields' ][$atts[ 'sort' ]][ 'value' ], $entry1[ 'fields' ][$atts[ 'sort' ]]['value']);
            });
        }
    } 
        foreach($entries as $entry) 
		{
			$fields_data = $entry['fields'];
			//$fields_arr = json_decode($fields_data,true);
			$fields_arr = $fields_data;
			
			$event_status = $fields_arr[19]['value'];
			$event_id = $entry['entry_id'];
			if($event_status == 'Approve')
			{
				$event_name = $fields_arr[1]['value'];
				$from_date = date('Y-m-d H:i:s', strtotime($fields_arr[16]['value']));
				$to_date = date('Y-m-d H:i:s', strtotime($fields_arr[17]['value']));
				$event_image = $fields_arr[18]['value'];
				$image_id   = attachment_url_to_postid( $event_image ); 
				$thumbnail_url = wp_get_attachment_image_src( $image_id, 'medium' );
				$event_register_url = $fields_arr[22]['value'];
				$event_view_url = "https://www.theshabbat.org/event-view/?event_name=".str_replace(' ', '-', $event_name)."&event_id=".$event_id;
				

				$eventDataArr[] = [
					'event_id' => $entry['entry_id'],
					'title' => $event_name,
					'start' => $from_date,
					'end'   => $to_date,
					'url'	=>$event_register_url?$event_register_url:$event_view_url,
					'event_view_url' =>$event_register_url?$event_register_url:'',
					'image' =>$event_image,
					'thumbnail' =>($thumbnail_url[0])?$thumbnail_url[0]:$event_image,
					'host_name'=>$fields_arr[14]['value'],
					'host_email'=>$fields_arr[15]['value'],
					'type_of_event'=>$fields_arr[13]['value']
					
				];
			}
		}

		return $eventDataArr;
    }
}







