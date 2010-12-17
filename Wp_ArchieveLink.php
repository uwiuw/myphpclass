<?php
/**
 * Library for wordpress archieve management.
 *
 * @property string $date_format regular read/write property
 * @property string $month regular read/write property
 * @property string $type regular read/write property
 * @property bool $echo regular read/write property
 *
 * @category Archieve
 * @package Archieve
 * @subpackage Archieve Custom
 * @example _example_Wp_ArchieveLink.php
 * 
 * @access public
 *
 * @author uwiuw
 * @copyright 2010 uwiuw
 */

class Wp_ArchieveLink {

     public $date_format;
     public $month;
     public $type;
     public $echo;

     function init(){
        if ($this->date_format == '') {
            $this->date_format  = $this->set_date_format();
        }
        if ($this->type == '') {
            $this->type  = 'monthly';
        }
        if ($this->echo == '') {
            $this->echo = TRUE;
        }

        $this->BridgeIntoWpLocale();
     }

     public function set_type($type =''){
         if (empty($type )) {
                $type = 'monthly';
         }

        return $this->type = $type;
     }

     public function get_type(){
        return $this->type;
     }

     public function set_date_format($date_format= '') {
         if (empty($date_format )) {
            $date_format = get_option('date_format');
            if (empty($date_format )) {
                $date_format = 'm.d.y';
            }
         }

         return $this->date_format = $date_format;
     }
     public function get_date_format() {
        return $this->date_format;
     }

     public function set_echo($echo) {
         if (empty($echo )) {
            if ($this->echo == '') {
                $this->echo = TRUE;
            }
         }

         return $this->echo = $echo;
     }
     public function get_echo() {
        return $this->echo;
     }

    /**
    * changing month properties of wp_locale class, a global wp variable
    */
     private function BridgeIntoWpLocale(){
        if (empty($this->month)) {
            global $wp_locale;

            $wp_locale->month['01'] = 'Jan';
            $wp_locale->month['02'] = 'Feb';
            $wp_locale->month['03'] = 'Mar';
            $wp_locale->month['04'] = 'Apr';
            $wp_locale->month['05'] = 'May';
            $wp_locale->month['06'] = 'Jun';
            $wp_locale->month['07'] = 'Jul';
            $wp_locale->month['08'] = 'Aug';
            $wp_locale->month['09'] = 'Sep';
            $wp_locale->month['10'] = 'Oct';
            $wp_locale->month['11'] = 'Nov';
            $wp_locale->month['12'] = 'Dec';
        }
     }

     /**
      * Get the archives wih custom format where it will print post
      * based on years while it able to show a month with empty posts.
      *
      * @todo memindahkan seluruh pembuatan perintah sql ke dalam method yg terpisah
      *
      * @global <type> $wpdb
      * @global <type> $wp_locale
      * @param boolean $echo print the output
      * @return mixed
      */
     public function get_archives($echo = TRUE) {
        global $wpdb, $wp_locale;

        $where = "WHERE post_type = 'post' AND post_status = 'publish'";
        $join = '';
        $limit ='';
        if ( 'monthly' == $this->get_type()) {
            /**
             * get distinct years
             */
            $query = "SELECT DISTINCT YEAR(post_date) AS `year` FROM $wpdb->posts
                        $where
                        GROUP BY YEAR(post_date)
                        ORDER BY post_date DESC
                        $limit";
            $yearresults = $wpdb->get_results($query);

            $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts
                        FROM $wpdb->posts
                        $join
                        $where
                        GROUP BY YEAR(post_date), MONTH(post_date)
                        ORDER BY post_date ASC
                        $limit";
            $arcresults = $wpdb->get_results($query);

            /**
             * processeing
             * @todo : menambah properties standar bagi class ini seperti $after, $before, $format
             */
            $format = 'custom';
            $before = ' [';
            $after = '] ';
            foreach ($yearresults as $years) {
                $url = get_year_link($years->year);
				$text = sprintf('%d', $years->year);
                $before = '<div class="year">';
                $after = '</div>';
                $output .= get_archives_link($url, $text, $format, $before, $after);

                $month_number = 1;
                foreach ($arcresults as $arcresult) {
                    while($month_number <= 13 && $month_number < $arcresult->month) {
                        $before = '<div class="nopost">';
                        $after = '</div>';
                        $text = sprintf(__('%1$s'), $wp_locale->get_month($month_number));
                        $output .=  "\t$before $text $after\n";

                        $month_number++;
                    }

                    $before = '<div class="haspost">';
                    $after = '</div>';

                    $url = get_month_link( $arcresult->year, $arcresult->month );
                    $text = sprintf(__('%1$s'), $wp_locale->get_month($arcresult->month));
                    $output .= get_archives_link($url, $text, $format, $before, $after);
                    $month_number++;
                }

                $beforeyear = '<li>';
                $afteryear = '</li>';
                /***
                 * adding xhtml after year/before years
                 */
                $output = "\t$beforeyear $output $afteryear\n";

            }
        }

        if ($this->echo == TRUE){
            echo $output;
        } else {
            return $output;
        }
    }
 } //end of class