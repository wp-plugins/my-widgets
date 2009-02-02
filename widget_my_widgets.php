<?php
/*
Plugin Name:  My Widgets
Plugin URI: http://www.vjcatkick.com/?page_id=5693
Description: Display your repositoried-widgets on your sidebar with its description, version number, and update. If you update repository, the widget will collect latest data automatically and put it onto your sidebar (once at an hour, cached).
Version: 0.0.1
Author: V.J.Catkick
Author URI: http://www.vjcatkick.com/
*/

/*
License: GPL
Compatibility: WordPress 2.6 with Widget-plugin.

Installation:
Place the widget_single_photo folder in your /wp-content/plugins/ directory
and activate through the administration panel, and then go to the widget panel and
drag it to where you would like to have it!
*/

/*  Copyright V.J.Catkick - http://www.vjcatkick.com/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/* Changelog
* Jan 06 2009 - v0.0.1
- Initial release
*/


function widget_my_widgets_init() {
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_my_widget_get_html( $pname, $loop_counter ) {
		$options = get_option('widget_my_widgets');
		$display_description = $options['widget_my_widgets_display_description'];
		$display_version = $options['widget_my_widgets_display_version'];
		$display_update = $options['widget_my_widgets_display_update'];

		$filedata = false;
		$workstr = 'http://wordpress.org/extend/plugins/profile/' . $pname . '/page/' . $loop_counter;
		$filedata = @file_get_contents( $workstr );
		$_output = '';
		$_ismore = false;

		if( $filedata ) {
			$w_datas = array();
			$spos = strpos( $filedata, '<div class="plugin-block">' );
			do {
				if( $spos !== false ) {
					$one_widget = array();
					$filedata = substr( $filedata, $spos );

					$spos = strpos( $filedata, '</h3>' );
					$workstr = substr( $filedata, 0, $spos );
					$retv = strip_tags( $workstr );
					$one_widget[ 'title' ] = $retv;
					$workstr = substr( $workstr, strpos( $workstr, '<h3>' ) );
					$workstr = substr( $workstr, strpos( $workstr, '"' ) + 1 );
					$workstr = substr( $workstr, 0, strpos( $workstr, '"' ) );
					$one_widget[ 'pgurl' ] = $workstr;
					$filedata = substr( $filedata, $spos );

					$spos = strpos( $filedata, '<ul class="plugin-meta">' );
					$retv = strip_tags( substr( $filedata, 0, $spos ) );
					$one_widget[ 'desc' ] = $retv;
					$filedata = substr( $filedata, $spos );

					$spos = strpos( $filedata, '</li>' );
					$retv = strip_tags( substr( $filedata, 0, $spos ) );
					$retv = str_replace( "Version ", "", $retv );
					$one_widget[ 'ver' ] = $retv;
					$filedata = substr( $filedata, $spos );

					$spos = strpos( $filedata, '</li>', 5 );
					$retv = strip_tags( substr( $filedata, 0, $spos ) );
					$retv = str_replace( "Updated ", "", $retv ) ;
					$one_widget[ 'update' ] = $retv;
					$filedata = substr( $filedata, $spos );

					$spos = strpos( $filedata, '</li>', 5 );
					$retv = strip_tags( substr( $filedata, 0, $spos ) );
					$retv = str_replace( "Downloads ", "", $retv ) ;
					$one_widget[ 'down' ] = $retv;
					$filedata = substr( $filedata, $spos );

					$w_datas[] = $one_widget;
				}else{
					break;
				} /* if else */
				$spos = strpos( $filedata, '<div class="plugin-block">' );
			} while( $spos !== false );

			$_ismore = strpos( $filedata, "<a class='next page-numbers'" );

			for( $i=0; $i<count($w_datas); $i++) {
				$workstr = $w_datas[$i][pgurl];
				if( $workstr && strlen( $workstr ) > 0 ) {
					$filedata = @file_get_contents( $workstr );
					if( $filedata ) {
						$spos = strpos( $filedata, '<div id="fyi" class="block">' );
						$filedata = substr( $filedata, $spos );
						$spos = strpos( $filedata, 'Author Homepage' );
						$filedata = substr( $filedata, $spos );
						$spos = strpos( $filedata, '<a href=' );
						$filedata = substr( $filedata, $spos );
						$filedata = substr( $filedata, 0, strpos( $filedata, '>' ) + 1 );
						$w_datas[$i]['plginpage_atag'] = $filedata;
					} /* if */
				} /* if */
			} /* for */

			foreach( $w_datas as $wdata ) {
				$_output .= '<li class="my_widget_title" >' . $wdata[ 'plginpage_atag' ] . $wdata[ 'title' ] . '</a></li>';

				if( $display_description ) {
					$_output .= '<div class="my_widget_description" style="margin-left: 10px; margin-right: 10px; text-align:left; text-align: justify; text-justify: auto; margin-bottom: 0.4em; font-size:7pt; color:#888;">';
					$_output .= $wdata[ 'desc' ];
					$_output .= '</div>';
				} /* if */

				if( $display_version || $display_update ) {
					$_output .= '<div class="my_widget_version_date" style="margin-left: 10px; margin-right: 10px; text-align:right; margin-bottom: 0.4em; font-size:7pt; color:#888;">';
					if( $display_version ) { $_output .= 'ver' . $wdata[ 'ver' ] ; }
					if( $display_update ) { $_output .= ' ' . $wdata[ 'update' ]; }
					$_output .= '</div>';
				} /* if */
			} /* foreach */
		} /* if */

		$retv = array( 'the_html' => $_output, 'is_more_page' => $_ismore );

		return( $retv );
	} /* widget_my_widget_get_html */

	function widget_my_widgets( $args ) {
		extract($args);

		$options = get_option('widget_my_widgets');
		$title = $options['widget_my_widgets_title'];
		$wp_repository_pageid = $options['widget_my_widgets_repository_pageid'];
		$thecache = $options[ 'widget_my_widgets_cache' ];
		$cached_time = $options['widget_my_widgets_cached_time'];

		$output = '<div id="widget_my_widgets"><ul>';

		// section main logic from here 
		$wp_org_profile_name = $wp_repository_pageid;
		$page_loop_counter = 1;
		$cache_timeout = 60 * 60;	// 60 sec x 60 min = 1 hour

		if( $cached_time + $cache_timeout < time() ) {
			$retv = '<div class= "my_widget_section_title" style="font-size:7pt; color:#888;text-align:center;" >- released to repository -</div>';

			do {
				$retData = widget_my_widget_get_html( $wp_org_profile_name, $page_loop_counter++ );
				$retv .= $retData[ 'the_html' ];
			} while( $retData[ 'is_more_page' ] );

			$options['widget_my_widgets_cached_time'] = time();
			$options[ 'widget_my_widgets_cache' ] = $retv . '<!-- cached -->';
			update_option('widget_my_widgets', $options);
		}else{
			$retv = $thecache;
		} /* if else */

		$output .= $retv;
// --


// if you want to add fixed entry, add here
// section title looks like this
// $output .= '<div class="my_widget_section_title" style="margin-top:1em; font-size:7pt; color:#888;text-align:center;" >- not on repository but released -</div>';
//
// first, put widget name
// $output .= '<li class="my_widget_title" ><a  href="http://www.yourdomain.com/yourpage.html">' . "Your widget title here" . '</a></li>';
//
// then description and version-date with optionable switch
//	if( $display_description ) {
//		$output .= '<div class="my_widget_description" style="margin-left: 10px; margin-right: 10px; text-align:left; text-align: justify; text-justify: auto; margin-bottom: 0.4em; font-size:7pt; color:#888;">';
//		$output .= 'description text here';
//		$output .= '</div>';
//	}
//
//	if( $display_version || $display_update ) {
//		$output .= '<div class="my_widget_version_date" style="margin-left: 10px; margin-right: 10px; text-align:right; margin-bottom: 0.4em; font-size:7pt; color:#888;">';
//		if( $display_version ) { $output .= 'ver' . '0.0.0'; }
//		if( $display_update ) { $output .= ' ' . '2009-12-25'; }
//		$output .= '</div>';
//	}
//
// and so on.

// --
		$output .= '<div style="text-align:left;"></div>'; // this is dummy for IE
		// These lines generate the output
		$output .= '</ul></div>';

		echo $before_widget . $before_title . $title . $after_title;
		echo $output;
		echo $after_widget;
	} /* widget_my_widgets() */

	function widget_my_widgets_control() {
		$options = $newoptions = get_option('widget_my_widgets');
		if ( $_POST["widget_my_widgets_submit"] ) {
			$newoptions['widget_my_widgets_title'] = strip_tags(stripslashes($_POST["widget_my_widgets_title"]));
			$newoptions['widget_my_widgets_repository_pageid'] = strip_tags(stripslashes($_POST["widget_my_widgets_repository_pageid"]));

			$newoptions['widget_my_widgets_display_description'] = (boolean)$_POST["widget_my_widgets_display_description"];
			$newoptions['widget_my_widgets_display_version'] = (boolean)$_POST["widget_my_widgets_display_version"];
			$newoptions['widget_my_widgets_display_update'] = (boolean)$_POST["widget_my_widgets_display_update"];

			$newoptions['widget_my_widgets_cached_time'] = 0;
			$newoptions['widget_my_widgets_cache'] = "";
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_my_widgets', $options);
		}

		// those are default value

		$title = htmlspecialchars($options['widget_my_widgets_title'], ENT_QUOTES);
		$wp_repository_pageid = $options['widget_my_widgets_repository_pageid'];
		$display_description = $options['widget_my_widgets_display_description'];
		$display_version = $options['widget_my_widgets_display_version'];
		$display_update = $options['widget_my_widgets_display_update'];

?>
	    <?php _e('Title:'); ?> <input style="width: 170px;" id="widget_my_widgets_title" name="widget_my_widgets_title" type="text" value="<?php echo $title; ?>" /><br />

	    <?php _e('PageID:'); ?> <input style="width: 170px;" id="widget_my_widgets_repository_pageid" name="widget_my_widgets_repository_pageid" type="text" value="<?php  echo  $wp_repository_pageid; ?>" /><br /><div style="font-size:7pt; color: #888;text-align:center;" >copy pageid from wordpress.org</div>

        <input id="widget_my_widgets_display_description" name="widget_my_widgets_display_description" type="checkbox" value="1" <?php if( $display_description ) echo 'checked';?>/> <?php _e('Description'); ?><br />
        <input id="widget_my_widgets_display_version" name="widget_my_widgets_display_version" type="checkbox" value="1" <?php if( $display_version ) echo 'checked';?>/> <?php _e('Version'); ?><br />
        <input id="widget_my_widgets_display_update" name="widget_my_widgets_display_update" type="checkbox" value="1" <?php if( $display_update ) echo 'checked';?>/> <?php _e('Date'); ?><br />

  	    <input type="hidden" id="template_src_submit" name="widget_my_widgets_submit" value="1" />
<?php
	} /* widget_my_widgets_control() */

	register_sidebar_widget('My Widgets', 'widget_my_widgets');
	register_widget_control('My Widgets', 'widget_my_widgets_control' );
} /* widget_my_widgets_init() */

add_action('plugins_loaded', 'widget_my_widgets_init');

?>