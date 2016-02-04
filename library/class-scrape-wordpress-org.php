<?php
/**
 * @package   Scrape svn.wordpress.org plugin or theme list
 * @author    Q Studio
 * @license   GPL-2.0+
 * @link      https://qstudio.us
 */

class Q_Scrape_Wordpress {

	/**
	 * The review.
	 *
	 * @since  0.1.0
	 * @var array
	 */
	protected $type, $url;

	public function __construct()
	{

	}

	/**
    * Scrape away
    *
    * @since        0.1
    * @return       void
    */
    public static function scrape( $query = null ){

        if ( is_null( $query ) ) {

            return false;

        }

        # Use the Curl extension to query Google and get back a page of results
        $url = "http://www.google.com";
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        curl_close($ch);

        # Create a DOM parser object
        $dom = new DOMDocument();

        # Parse the HTML from Google.
        # The @ before the method call suppresses any warnings that
        # loadHTML might throw because of invalid HTML in the page.
        @$dom->loadHTML($html);

        # Iterate over all the <a> tags
        foreach($dom->getElementsByTagName('a') as $link) {
            # Show the <a href>
            echo $link->getAttribute('href');
            echo "<br />";
        }

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