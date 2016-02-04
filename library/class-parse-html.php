<?php
/**
 * @package   Parse a string of HTML Markup and return an array
 * @author    Q Studio
 * @license   GPL-2.0+
 * @link      https://qstudio.us
 */

class Q_Parse_HTML {

	/**
	 * The review.
	 *
	 * @since  0.1.0
	 * @var array
	 */
	protected $string;

	public function __construct( $string )
	{

        // assign property ##
        $this->string = $string;

	}

	/**
    * parse changelog html string and return an array
    *
    * <h4>1.2.7</h4>
    * <ul>
    *    <li>Fix: to remove minor security loop hole</li>
    *    <li>New: Added option to remove standard wp_users data from export</li>
    *    <li>Fix: removed roles and groups columns from export when options hidden</li>
    * </ul>
    *
    * @since        0.1
    * @return       Mixed, array or boolean false
    */
    public function changelog(){

        if ( ! isset( $this->string ) || is_null( $this->string ) ) {

            return false;

        }

        // new array ##
        $array = array();

        $DOM = new DOMDocument;
        $DOM->loadHTML( $this->string );

        // get all H4 ##
        $h4 = $DOM->getElementsByTagName('h4');

        // h4 ##
        for ( $i = 0; $i < $h4->length; $i++ ) {

            // set $array key and value to $i ##
            $array[$h4->item($i)->nodeValue] = $i;

        }

        // count ##
        $i = 0;

        // loop over all <ul>s ##
        foreach ( $DOM->getElementsByTagName('ul') as $li ){

            // create a new empty array ##
            $children = array();

            // loop over all <li>s ##
            foreach ( $li->getElementsByTagName('li') as $item ){

                // add childen ##
                $children[] = $item->nodeValue;

            }

            // grab key from $i value ##
            $key = array_search ( $i, $array );

            // update array value ##
            $array[$key] = $children;

            // iterate ##
            $i++;

        }

        // kick it back ##
        return $array;

    }



    /**
    * parse screenshots html string and return an array
    *
    * <ol>
    *   <li>
    *       <a href="//s.w.org/plugins/export-user-data/screenshot-1.png?r=1342780" title="Click to view full-size screenshot 1">
    *           <img class="screenshot" src="//s.w.org/plugins/export-user-data/screenshot-1.png?r=1342780" alt="export-user-data screenshot 1">
    *       </a>
    *       <p>User export screen</p>
    *   </li>
    * </ol>
    *
    * @since        0.1
    * @return       Mixed, array or boolean false
    */
    public function screenshots(){

        if ( ! isset( $this->string ) || is_null( $this->string ) ) {

            return false;

        }

        // new array ##
        $array = array();

        $DOM = new DOMDocument;
        $DOM->loadHTML( $this->string );

        $ol = $DOM->getElementsByTagName('ol');

        // iterate ##
        $key = 0;

        foreach ( $ol->item(0)->getElementsByTagName('li') as $li ) {

            if ( $li->hasChildNodes() ) {

                // link & src ##
                foreach( $li->getElementsByTagName('a') as $a ) {

                    if ( isset( $a ) ) {

                        $array[$key]['a']["href"] = $a->getAttribute('href');
                        $array[$key]['a']["title"] = $a->getAttribute('title');

                        foreach( $a->getElementsByTagName('img') as $img ) {

                            if ( isset( $img ) ) {

                                $array[$key]['img']["class"] = $img->getAttribute('class');
                                $array[$key]['img']["src"] = $img->getAttribute('src');
                                $array[$key]['img']["alt"] = $img->getAttribute('alt');

                            }

                        }

                    }

                }

            }

            // add caption text ##
            $array[$key]['caption'] = isset( $li->nodeValue ) ? trim( $li->nodeValue ) : null;

            // iterate ##
            $key ++;

        }

        // test ##
        #wp_die( pr( $array ) );

        // kick it back ##
        return $array;

    }

}