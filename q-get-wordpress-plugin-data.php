<?php
/**
 * Plugin Name: 	Get WordPress Plugin Data
 * Plugin URI: 		https://qstudio.us
 * Description: 	Get WordPress.org Plugin data and diplay using a shortcode.
 * Version: 		0.1
 * Author: 			Q Studio
 * Author URI: 		https://qstudio.us
 * License: 		GPLv2 or later
 * Class:           Q_Get_WordPress_Plugin_Data
 * Text Domain:     q-gwpd
 * Based on:		https://github.com/wp-plugins/dcg-display-plugin-data/
 */

// no cheating ##
defined( 'ABSPATH' ) OR exit;

if ( ! class_exists( 'Q_Get_WordPress_Plugin_Data' ) ) {
    
    // instatiate plugin via WP plugins_loaded - init is too late for CPT ##
    add_action( 'plugins_loaded', array ( 'Q_Get_WordPress_Plugin_Data', 'get_instance' ), 1 );
    
    class Q_Get_WordPress_Plugin_Data {
                
        // Refers to a single instance of this class. ##
        private static $instance = null;

        // Plugin Settings ##
        const version = '0.1';
        const text_domain = 'q-gwpd'; // for translation ##
        const cache = true;
        const cache_timeout = DAY_IN_SECONDS; // 60*60*24

        // default args list ##
        public static 
        	$default_api_args = false,
        	$default_shortcode_args = false,
        	$default_stats_args = array(),
        	$api_args = false,
        	$plugin_stats_data = false,
        	$plugins_section_titles = array(),
            $cache_handle = 'q-gwpd'
    	;

     	/**
         * Creates or returns an instance of this class.
         *
         * @return  Foo     A single instance of this class.
         */
        public static function get_instance() 
        {
            
            if ( null == self::$instance ) {
                self::$instance = new self;
            }

            return self::$instance;

        }
        

        /**
         * Instatiate Class
         * 
         * @since       0.1
         * @return      void
         */
        private function __construct() 
        {

        	// set text domain ##
            add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );

        	// set default API args ##
        	self::set_default_api_args();

        	// set default shortcode args ##
        	self::set_default_shortcode_args();

        	// set default stats args ##
        	self::set_default_stats_args();

        	// build shortcode ##
			add_shortcode( 'wp_plugin_data', array( $this, 'do_shortcode' ) );

			if ( is_admin() ) {
                
                // styles and scripts ##
                #add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
                
            } else {
                
                // styles and scripts ##
                add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 1 );

                // chart scripts ##
                add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts_amcharts_js' ), 2 );

            }

        }



        /**
         * Load Text Domain for translations
         * 
         * @since       0.1.0
         * @return      void
         */
        public function load_plugin_textdomain() 
        {
            
            // set text-domain ##
            $domain = self::text_domain;
            
            // The "plugin_locale" filter is also used in load_plugin_textdomain()
            $locale = apply_filters('plugin_locale', get_locale(), $domain);

            // try from global WP location first ##
            load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );
            
            // try from plugin last ##
            load_plugin_textdomain( $domain, FALSE, plugin_dir_path( __FILE__ ).'languages/' );
            
        }


		/**
         * Admin Enqueue Scripts
         * 
         * @since       0.1
         * @return      void
         */
        public function admin_enqueue_scripts() {

            wp_enqueue_script( 'admin-'.self::text_domain.'-js',  self::get_plugin_url( 'assets/javascript/'.self::text_domain.'.js' ), array( 'jquery' ), self::version, true );
            
            wp_register_style( 'admin-'.self::text_domain.'-css', self::get_plugin_url( 'assets/css/'.self::text_domain.'.css' ) );
            wp_enqueue_style( 'admin-'.self::text_domain.'-css' );
            
        }
        
        
        /**
         * WP Enqueue Scripts - on the front-end of the site
         * 
         * @since       0.1
         * @return      void
         */
        public function wp_enqueue_scripts() {
            
        	global $post;
			
			if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wp_plugin_data') ) {

	            // Register the script ##
	            // wp_register_script( ''.self::text_domain.'-js', self::get_plugin_url( 'assets/javascript/'.self::text_domain.'.js' ), array( 'jquery' ), self::version, true );

	            // Now we can localize the script with our data.
	            // $translation_array = array( 
	            //         'stylesheet_directory_uri'  => get_stylesheet_directory_uri()
	            //     ,   'search_in'                 => __( "in", self::text_domain )
	            //     ,   'search_from'               => __( "from", self::text_domain )
	            //     ,   'load_more'                 => __( "Load More", self::text_domain )
	            // );
	            // wp_localize_script( ''.self::text_domain.'-js', 'q_gwpd', $translation_array );

	            // enqueue the script ##
	            // wp_enqueue_script( ''.self::text_domain.'-js' );
	            
	            #wp_register_style( 'q-control-css', self::get_plugin_url( 'css/q-control.css' ) );
	            #wp_enqueue_style( 'q-control-css' );
	            
	            wp_register_style( self::text_domain.'-css', self::get_plugin_url( 'assets/css/'.self::text_domain.'.css' ) );
	            wp_enqueue_style( self::text_domain.'-css' );

	        }

        }


        /**
         * WP Enqueue Scripts - on demand for charts
         * 
         * @since       0.1
         * @return      void
         */
        public static function wp_enqueue_scripts_amcharts_js() {
            
        	global $post;
			
			if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wp_plugin_data') ) {

				// Register chart scripts - only enqueue on demand ##
	            wp_register_script( 'amcharts-js', self::get_plugin_url( 'assets/javascript/amcharts.js' ), '', self::version, false );
	            wp_register_script( 'serial-js', self::get_plugin_url( 'assets/javascript/serial.js' ), array( 'amcharts-js' ), self::version, false );
	            wp_register_script( 'patterns-js', self::get_plugin_url( 'assets/javascript/themes/patterns.js' ), array( 'amcharts-js' ), self::version, false );

	            // enqueue the script ##
	            wp_enqueue_script( 'amcharts-js' );
	            wp_enqueue_script( 'serial-js' );
	            wp_enqueue_script( 'patterns-js' );

        	}

        }


		/**
         * Get Plugin URL
         * 
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_url( $path = '' ) 
        {

            return plugins_url( ltrim( $path, '/' ), __FILE__ );

        }



        /**
         * 
         * Write to WP Error Log
         * 
         * @since       1.5.0
         * @return      void
         */
        public static function log( $log )  
        {
            if ( true === WP_DEBUG ) {
                if ( is_array( $log ) || is_object( $log ) ) {
                    error_log( print_r( $log, true ) );
                } else {
                    error_log( $log );
                }
            }
        }


        /**
         * Pretty print_r / var_dump
         * 
         * @since       0.1
         * @param       Mixed       $var        PHP variable name to dump
         * @param       string      $title      Optional title for the dump
         * @return      String      HTML output
         */
        public static function pr( $var, $title = null ) 
        { 
            
            if ( $title ) $title = '<h2>'.$title.'</h2>';
            print '<pre class="var_dump">'; echo $title; var_dump($var); print '</pre>'; 
            
        }


        /* PLUGIN SPECIFICS */


		/**
        * API wordpress.org plugin defaults
        *
        * @since 		0.1
        * @return 		Array 		Arguments for API query
        */
        public static function set_default_api_args() 
        {

        	// already set ##
        	if ( self::$default_api_args ) return;

        	// kick back array ##
			return (object)self::$default_api_args = array(
				'slug' 			=> 'export-user-data',
				'is_ssl' 		=> is_ssl(),
				'fields'		=> array(
					'short_description' 	=> true,
					'description' 			=> false,
					'sections' 				=> true,
					'tested' 				=> true,
					'requires' 				=> true,
					'rating' 				=> true,
					'ratings' 				=> true,
					'downloaded' 			=> true,
					'downloadlink' 			=> true,
					'last_updated' 			=> true,
					'added' 				=> true,
					'tags' 					=> false,
					'compatibility' 		=> true,
					'homepage' 				=> true,
					'versions' 				=> false,
					'donate_link' 			=> true,
					'reviews' 				=> false,
					'banners' 				=> false,
					'icons' 				=> false,
					'active_installs' 		=> true,
					'group' 				=> false,
					'contributors' 			=> false
				)
			);

        }



        /**
        * shortcode defaults
        *
        * @since 		0.1
        * @return 		Array 		Arguments for API query
        */
        public static function set_default_shortcode_args() 
        {

        	// already set ##
        	if ( self::$default_shortcode_args ) return;

        	// kick back array ##
			return (object)self::$default_shortcode_args = array(
				'slug' 			=> 'export-user-data',
				'downloaded' 	=> true,
				'description' 	=> false,
				'installation' 	=> false,
				'faq' 			=> false,
				'screenshots' 	=> false,
				'stats'			=> false // day by day download stats, presented in a graph ##
			);

        }


		/**
        * stats defaults
        *
        * @since 		0.1
        * @return 		Array 		Arguments for API query
        */
        public static function set_default_stats_args() 
        {

        	// already set ##
        	if ( self::$default_stats_args ) return;

        	// kick back array ##
			return (object)self::$default_stats_args = array(
				'slug' 			=> 'export-user-data',
				'limit' 		=> 'max',
			);

        }



        /**
		* Sanitize and clean-up data returned from API
		* 
		* 
        */
        public static function sanitize_api_data( $data = null )
        {

        	if ( is_null ( $data ) ) { return false; }

			$plugins_allowedtags = array(
				'a' => array( 'href' => array(), 'title' => array(), 'target' => array() ),
				'abbr' => array( 'title' => array() ), 'acronym' => array( 'title' => array() ),
				'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
				'div' => array( 'class' => array() ), 'span' => array( 'class' => array() ),
				'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
				'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
				'img' => array( 'src' => array(), 'class' => array(), 'alt' => array() )
			);

			self::$plugins_section_titles = array(
				'description'  => _x( 'Description',  'Plugin installer section title', self::text_domain ),
				'installation' => _x( 'Installation', 'Plugin installer section title', self::text_domain ),
				'faq'          => _x( 'FAQ',          'Plugin installer section title', self::text_domain ),
				'screenshots'  => _x( 'Screenshots',  'Plugin installer section title', self::text_domain ),
				'changelog'    => _x( 'Changelog',    'Plugin installer section title', self::text_domain ),
				'reviews'      => _x( 'Reviews',      'Plugin installer section title', self::text_domain ),
				'other_notes'  => _x( 'Other Notes',  'Plugin installer section title', self::text_domain )
			);

			// Sanitize HTML
			if ( isset( $data->sections ) ) {
				foreach ( (array) $data->sections as $section_name => $content ) {
					$data->sections[$section_name] = wp_kses( $content, $plugins_allowedtags );
				}
			}

			foreach ( array( 'version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug' ) as $key ) {
				if ( isset( $data->$key ) ) {
					$data->$key = wp_kses( $data->$key, $plugins_allowedtags );
				}
			}

			// kick it back ##
			return $data;

        }


		/**
        * Request data from wordpress.org plugin API
        *
        * @since 		0.1
        * @return 		String 		HTML from API request to wordpres.org 
        * @link 		https://developer.wordpress.org/reference/functions/plugins_api/
        */
        public static function get_plugin_data( $action = null, $args = null, $cache_handle = null ) 
        {

            #wp_die( self::pr( 'cache handle: '.self::$cache_handle.'_info'.$cache_handle ) );

        	if ( self::cache && $get_plugin_data = get_transient( self::$cache_handle.$cache_handle ) ) {

        		return $get_plugin_data;

        	}

        	// sanity check ##
        	if ( is_null( $action ) || is_null( $args ) ) {

        	 	return new WP_Error( 'get_plugin_data', __( "Error in API method call", self::text_domain ) );

        	}

        	// merge default args ##
	        $args = (object) wp_parse_args( $args, self::$default_api_args );

        	// make sure we've got the plugins_api() function loaded ##
	        if ( ! function_exists( 'plugins_api' ) ) {
			
				require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

			}

		    // call WP's built in plugins_api function ##
		    $get_plugin_data = self::sanitize_api_data( plugins_api( $action, $args ) );

		    // cache ##
		    if ( self::cache ) {

		    	set_transient( self::$cache_handle.$cache_handle, $get_plugin_data, self::cache_timeout );

		    }

		    // kick it back ##
        	return $get_plugin_data;
 
        }


		/**
        * Get additional download stats
        *
        * @since 		0.1
        * @return 		Mixed 		Boolean || Object
        * @link 		https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug=export-user-data&limit=max
        */
        public static function get_plugin_stats( $args = null )
        {

            // merge default args ##
            $args = (object) wp_parse_args( $args, self::$default_stats_args );

        	if ( self::cache && $get_plugin_stats = get_transient( self::$cache_handle.'-'.$args->slug ) ) {

        		#wp_die( self::pr( 'using cached data' ) );
        		return $get_plugin_stats;

        	}

        	// sanity check ##
        	if ( is_null( $args ) ) { return false; }

        	// start empty ##
        	$request = false;

	        // builr end-point to API ##
			$url = $http_url = "https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug={$args->slug}&limit={$args->limit}";

			// check for SSL support ##
			if ( $ssl = wp_http_supports( array( 'ssl' ) ) ) {

				$url = set_url_scheme( $url, 'https' );

			}

			//build args array ##
			$http_args = array (
				'timeout' => 15,
			);

			// send transaction to API ##
			$request = wp_remote_post( $url, $http_args );

			// check for SSL errors ##
			if ( $ssl && is_wp_error( $request ) ) {
				
				// log error ##
				self::log( 
					sprintf( 
						__( 'Error in %s :: $s / %s', self::text_domain )
						, 	__CLASS__
						,  	__METHOD__
						, 	$request->get_error_message()
					)
				); 

				// try again without SSL ##
				$request = wp_remote_post( $http_url, $http_args );

			}

			// still not happening ##
			if ( is_wp_error( $request ) ) {

				// log error ##
				self::log( 
					sprintf( 
						__( 'Error in %s :: $s / %s', self::text_domain )
						, 	__CLASS__
						,  	__METHOD__
						, 	$request->get_error_message()
					)
				); 

			} else {

				$request = json_decode( wp_remote_retrieve_body( $request ), true );

			}

			// cache ##
		    if ( self::cache && $request ) {

		    	set_transient( self::$cache_handle.'-'.$args->slug, $request, self::cache_timeout );

		    }

			return $request;

        }



		/**
        * Render Markup for JS charts based on imported stats 
        *
        * @since 		0.1
        * @return 		String 		HTML for charts
        */
        public static function render_stats()
        {

        	// check if we have data ##
        	if ( ! self::$plugin_stats_data ) {

        		return false;

        	}

			// produce JS object ##
			$data_points = '';

			// loop over each item ##
			/*
			{ "date": "2012-07-27", "value": 13 }, 
		    */
			foreach ( self::$plugin_stats_data as $key => $value ) {

				$data_points .= sprintf( 
						'{ "date": "%s", "value": %d },'
					,	$key
					,	$value
				);

			}

			#wp_die( self::pr( $data_points ) );

			// print out JS ##		
?>
			<script>
			var chart = AmCharts.makeChart("chartdiv", {
			    "type": "serial",
			    "theme": "patterns",
			    "marginRight": 40,
			    "marginLeft": 40,
			    "autoMarginOffset": 20,
			    "dataDateFormat": "YYYY-MM-DD",
			    "valueAxes": [{
			        "id": "v1",
			        "axisAlpha": 0,
			        "position": "left",
			        "ignoreAxisWidth":true
			    }],
			    "balloon": {
			        "borderThickness": 1,
			        "shadowAlpha": 0
			    },
			    "graphs": [{
			        "id": "g1",
			        "balloon":{
			          "drop":true,
			          "adjustBorderColor":false,
			          "color":"#ffffff"
			        },
			        "bullet": "round",
			        "bulletBorderAlpha": 1,
			        "bulletColor": "#FFFFFF",
			        "bulletSize": 5,
			        "hideBulletsCount": 50,
			        "lineThickness": 2,
			        "title": "red line",
			        "useLineColorForBulletBorder": true,
			        "valueField": "value",
			        "balloonText": "<span style='font-size:18px;'>[[value]]</span>"
			    }],
			    "chartScrollbar": {
			        "graph": "g1",
			        "oppositeAxis":false,
			        "offset":30,
			        "scrollbarHeight": 80,
			        "backgroundAlpha": 0,
			        "selectedBackgroundAlpha": 0.1,
			        "selectedBackgroundColor": "#888888",
			        "graphFillAlpha": 0,
			        "graphLineAlpha": 0.5,
			        "selectedGraphFillAlpha": 0,
			        "selectedGraphLineAlpha": 1,
			        "autoGridCount":true,
			        "color":"#AAAAAA"
			    },
			    "chartCursor": {
			        "pan": true,
			        "valueLineEnabled": true,
			        "valueLineBalloonEnabled": true,
			        "cursorAlpha":1,
			        "cursorColor":"#258cbb",
			        "limitToGraph":"g1",
			        "valueLineAlpha":0.2
			    },
			    "valueScrollbar":{
			      "oppositeAxis":false,
			      "offset":50,
			      "scrollbarHeight":10
			    },
			    "categoryField": "date",
			    "categoryAxis": {
			        "parseDates": true,
			        "dashLength": 1,
			        "minorGridEnabled": true
			    },
			    "export": {
			        "enabled": false
			    },
			    "dataProvider": [<?php echo $data_points; ?>]
			});

			chart.addListener("rendered", zoomChart);

			zoomChart();

			function zoomChart() {
			    chart.zoomToIndexes(chart.dataProvider.length - 40, chart.dataProvider.length - 1);
			}
			</script>
<?php

        }


		/**
        * Do shortcode with passed arguments 
        *
        * @since 		0.1
        * @return 		String 		HTML from API request to wordpres.org 
        */
        public function get_sections( $attributes = null ) 
        {

        	// nada ##
        	if ( is_null ( $attributes ) || is_null ( self::$api_args ) ) {

        		return false;

        	}

        	#wp_die( self::pr( self::$api_args ) );

        	// 
        	if ( 
        			false == $attributes->description 
    			&& 	false == $attributes->installation
    			&& 	false == $attributes->faq
    			&& 	false == $attributes->screenshots
    		) {

        		self::$api_args['fields']['sections'] = false;

    		}

        }



        /**
        * Generate cache handle from passed shortcode attributes
        *
        * @since        0.1
        * @return       String      HTML from API request to wordpres.org 
        */
        public static function get_cache_handle( $attributes = null ) 
        {

            if ( is_null( $attributes ) ) {

                // random 6 char string ##
                return rand( 6 );

            }

            // new string ##
            $handle = $attributes->slug;

            foreach ( $attributes as $key => $value ) {

                if ( true === $value ) {

                    $handle .= '-'.$key;

                }

            }

            #wp_die( self::pr( $handle ) );

            // kick it back ##
            return $handle;

        }


		/**
        * Do shortcode with passed arguments 
        *
        * @since 		0.1
        * @return 		String 		HTML from API request to wordpres.org 
        */
        public function do_shortcode( $attributes = null ) 
        {

        	// parse user attributes with defaults ##
        	// https://codex.wordpress.org/Function_Reference/shortcode_atts
        	$attributes = (object)shortcode_atts( self::$default_shortcode_args, $attributes, 'wp_plugin_data' );
            
        	// build args list for API request ##
        	self::$api_args = wp_parse_args( array( 'slug' => $attributes->slug ), self::$default_api_args );

        	// check if we can reduce the API load by removing the sections data ##
        	self::get_sections( $attributes );

        	// grab plugin data ##
        	$data = self::get_plugin_data( 
	        		    'plugin_information' 
	        		,   self::$api_args
                    ,   self::get_cache_handle( $attributes )
        		);

        	/** Check for Errors & Display the results */
		    if ( is_wp_error( $data ) ) {
		 
		        return $data->get_error_message();

		    }

			// test it ##
			#wp_die( self::pr( $data ) );

			// no response ##
			if ( ! $data ) {

				return __( "No data found for this plugin", self::text_domain );

			}

			// stats ##
			if ( 'true' == $attributes->stats ) { 

                #wp_die( self::pr( $attributes ) );

				// try and grab stats ##
				if ( $data->stats = self::get_plugin_stats( array( 'slug' => $attributes->slug ) ) ) {

					// assign stats data to static property ##
					self::$plugin_stats_data = $data->stats;

					// add JS in footer ##
					add_action( 'wp_footer', array ( $this, 'render_stats' ) );

				}

			}

			// kick back rendered shortcode ##
			return self::render_shortcode( $data, $attributes );

		}


		/**
        * Do shortcode with passed arguments 
        *
        * @since 		0.1
        * @return 		String 		HTML from API request to wordpres.org 
        */
        public static function render_shortcode( $data = null, $attributes = null ) 
        {

?>
			<ul class="q-get-wordpress-plugin-data">
<?php

        	// sanity check ##
        	if ( is_null( $data ) || is_null( $attributes ) ) {

        		_e( "Can't render shortcode without data", self::text_domain );

        	}

        	// test it ##
			#wp_die( self::pr( $data->sections ) );

        	// tidy and diplay - @todo - this needs some tidying ##
			$rating_stars_path = self::get_plugin_url( 'assets/images/rating_stars.png' );

			$rating_stars_holder_style = "position: relative;height: 17px;width: 92px; background: url($rating_stars_path) repeat-x bottom left; vertical-align: top; display:inline-block;";
			$rating_stars_style = "background: url($rating_stars_path) repeat-x top left; height: 17px;float: left;text-indent: 100%;overflow: hidden;white-space: nowrap; width: {$data->rating}%";
			$rating_stars_value = floor($data->rating/20);

			// Count average rating
			$stars = array();
			#self::pr( $data->ratings );
			foreach ( $data->ratings as $value ) {
			    $stars[] = isset( $value ) ? $value : 0 ;
			}

			$calculate_average_rating = 0;

			if ( ! empty( array_filter( $stars ) ) ) {
				$calculate_average_rating = ( ( ( $stars[0] * 5 ) + ( $stars[1] * 4 ) + ( $stars[2] * 3 ) + ( $stars[3] * 2 ) + ( $stars[4] * 1 ) ) / $data->num_ratings );
			}
			#if ( empty( $calculate_average_rating ) ) { $calculate_average_rating = 0; }

			// Format rating. Eg: 4.7 out of 5 stars, but 5 (no decimal) out of 5 stars
			$average_rating = ( is_float( $calculate_average_rating ) ? number_format( $calculate_average_rating, 1 ) : $calculate_average_rating );
			$release_date = date( "d F Y", strtotime( $data->added ) );
			$last_updated_date = date( "d F Y", strtotime( $data->last_updated ) );
			$wordpress_url = "https://wordpress.org/plugins/".$data->slug;

            // cast sections to object ##
            if ( isset( $data->sections ) ) { $data->sections = (object)$data->sections; }

?>
				<li class='q-gwpd-name'>
					<span class="title">Title:</span>
					<span class="value"><?php echo $data->name; ?></span>
				</li>
				<li class='q-gwpd-version'>
					<span class="title">Version:</span>
					<span class="value"><?php echo $data->version; ?></span>
				</li>
				<li class='q-gwpd-url'>
					<span class="title">URL:</span>
					<span class="value"><a href="<?php echo $wordpress_url; ?>" target="_blank"><?php echo $wordpress_url; ?></a></span>
				</li>
				<li class='q-gwpd-rating'>
					<span class="title">Rating:</span>
					<ul class="star-rating">
						<li class='q-gwpd-rating-stars-holder' style='<?php echo $rating_stars_holder_style; ?>'>
							<span class='q-gwpd-rating-stars' style='<?php echo $rating_stars_style; ?>'><?php echo $rating_stars_value; ?></span>
						</li>
					</ul>
					<span class='q-gwpd-average-rating' style='margin-left:4px;'>( <?php echo $average_rating; ?> out of 5 stars )</span>
				</li>
<?php

				if ( isset( $data->active_installs ) ) {

?>
				<li class='q-gwpd-active-installs'>
					<span class="title">Active Installs:</span>
					<span class="value"><?php echo $data->active_installs; ?> +</span>
				</li>
<?php

				} // active installs ##

?>				
				<li class='q-gwpd-last_updated'>
					<span class="title">Last Updated:</span>
					<span class="value"><?php echo $last_updated_date; ?></span>
				</li>
				<li class='q-gwpd-downloaded'>
					<span class="title">Downloads:</span>
					<span class="value"><?php echo $data->downloaded; ?></span>
				</li>
<?php

			if ( isset( $data->stats ) ) {

?>
				<li><div id="chartdiv"></div></li>
<?php

			} // stats ##

?>		
				<li class='q-gwpd-requires_wp'>
					<span class="title">Requires:</span>
					<span class="value"><?php echo $data->requires; ?> or higher</span>
				</li>
				<li class='q-gwpd-tested_wp'>
					<span class="title">Compatible up to:</span>
					<span class="value"><?php echo $data->tested; ?></span>
				</li>
				<li class='q-gwpd-released'>
					<span class="title">Released:</span>
					<span class="value"><?php echo $release_date; ?></span>
				</li>
				
				
<?php

			if ( $attributes->description == true && isset( $data->sections ) && isset( $data->sections->description ) ) {

?>
				<li class='q-gwpd-desription'>
					<span class="title">Descriptions:</span>
					<span class="value"><?php echo $data->sections->description; ?></span>
				</li>
<?php

			}

			if ( $attributes->installation == true && isset( $data->sections ) && isset( $data->sections->installation ) ) {

?>
				<li class='q-gwpd-installation'>
					<span class="title">Installation:</span>
					<span class="value"><?php echo $data->sections->installation; ?></span>
				</li>
<?php

			}

			if ( $attributes->faq == true && isset( $data->sections ) && isset( $data->sections->faq ) ) {

?>
				<li class='q-gwpd-faq'>
					<span class="title">FAQ:</span>
					<span class="value"><?php echo $data->sections->faq; ?></span>
				</li>
<?php

			}

			if ( $attributes->screenshots == true && isset( $data->sections ) && isset( $data->sections->screenshots ) ) {

?>
				<li class='q-gwpd-screenshots'>
					<span class="title">Screenshots:</span>
					<span class="value"><?php echo $data->sections->screenshots; ?></span>
				</li>
<?php

			}

?>
			</ul>
<?php

        }

	}

}

