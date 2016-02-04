<?php
/**
 * @package   Get WordPress Plugin Stats
 * @author    Q Studio
 * @license   GPL-2.0+
 * @link      https://qstudio.us
 */

class Q_WP_Plugin_Stats {

	/**
	 * The review.
	 *
	 * @since  0.1.0
	 * @var array
	 */
	protected $query;

	public function __construct() 
	{
		
	}

	/**
    * Run a Google query
    *
    * @since        0.1
    * @return       void
    */
    public static function google_query( $query = null ){

        if ( is_null( $query ) ) {

            return false;

        }

        // urlencode query ##
        $query = urlencode( $query );

        // user IP ##
        $userip = self::get_ip();

        // build query ##
        $url = "https://ajax.googleapis.com/ajax/services/search/web?v=1.0&q={$query}&userip={$userip}";

        // fire it off ##
        $response = wp_remote_post( $url, 
            array(
                'method'        => 'GET',
                'timeout'       => 45,
                'redirection'   => 5,
                'httpversion'   => '1.0',
                'blocking'      => true,
                'headers'       => array(),
                'body'          => array(),
                'cookies'       => array()
            )
        );

        if ( is_wp_error( $response ) ) {

           $error_message = $response->get_error_message();
           return 
                sprintf( 
                    __( "Google query failed: %s", self::text_domain )
                    ,   $error_message
                );
        
        }

        // now, process the JSON string
        $json = json_decode( $response["body"]  );

        // test ##
        #wp_die( self::pr( $json ) );

        // kick it back ##
        return $json;

    }


    /**
    * Get current IP address
    * 
    * @since        0.1
    * @return       String      Current IP Address
    */
    public static function get_ip()
    {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }

    }

}