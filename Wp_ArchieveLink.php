<?php
/**
 * Library for wordpress archieve management.
 *
 * @property string $date_format regular read/write property
 * @property string $month regular read/write property
 * @property string $type regular read/write property
 * @property bool $echo regular read/write property
 *
 * @category Archieve
 * @package Archieve
 * @subpackage Archieve Custom
 * @example _example_Wp_ArchieveLink.php
 *
 * @access public
 *
 * @author @uwiuw
 * @copyright 2010 uwiuw
 */

class Wp_ArchieveLink {

     public $date_format;
     public $month;
     public $type;
     public $echo;
     public $paginationfilepath;
     public $target_url;

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
    * determine url for pagination class target_url properties
    */
     public function set_target_url($target_url) {
         if (empty($target_url )) {
            if ($this->target_url == '') {
                $this->target_url = get_permalink();
            }
         }

         $this->target_url = $target_url;
     }

     /**
      * get target url that need for pagination
      * @return string
      */
     public function get_target_url() {
        return $this->target_url;
     }

     /**
      * get the pagination file path
      *
      * @return string
      */
     public function get_paginationfilepath() {
        return $this->paginationfilepath;
     }

     /**
      * set the pagination file path
      *
      * @param string $paginationfilepath the path file of pagination class
      * @return string
      */
     public function set_paginationfilepath($paginationfilepath) {
         if (empty($paginationfilepath )) {
            if ($this->paginationfilepath == '') {
                $this->paginationfilepath = TRUE;
            }
         }

         $this->paginationfilepath = $paginationfilepath;
     }

    /**
    * brige changing month properties of wp_locale class, a global wp variable
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

     public function get_date_from() {
        $year = get_query_var('year');
        $monthnum = get_query_var('monthnum');
        $datenum = get_query_var('datenum');

        $monthnum = !empty($monthnum) ? $monthnum : '01';
        $datenum = !empty($datenum) ? $datenum : '01';

        $monthnum = vsprintf("%02d", array($monthnum));
        $datenum = vsprintf("%02d", array($datenum));
        $date_from = $year . '-' . $monthnum . '-' . $datenum;

        if ($date_from) {
            $date_from = ' AND post_modified >= "' . $date_from  . '" ';

            return $date_from;
        }
     }

     public function get_date_to() {
        $year = get_query_var('year');
        $monthnum = get_query_var('monthnum');
        $datenum = get_query_var('datenum');

        /**
         * has month number
         */
        if ($monthnum) {
            /**
             * checking combination of date
             */
            $monthnum = vsprintf("%02d", array($monthnum));
            if ($datenum) {//
                $datenum = vsprintf("%02d", array($datenum));
                $date_to = $year . '-' . $monthnum . '-' . $datenum;
            } else {
                if (checkdate($monthnum, 31, $year)) {
                    $date_to = $year . '-' . $monthnum . '-' . '31';
                } elseif(checkdate($monthnum, 30, $year))  {
                    $date_to = $year . '-' . $monthnum . '-' . '30';
                } elseif(checkdate($monthnum, 29, $year))  {
                    $date_to = $year . '-' . $monthnum . '-' . '29';
                } elseif(checkdate($monthnum, 28, $year))  {
                    $date_to = $year . '-' . $monthnum . '-' . '28';
                }
            }
        } else {
            $monthnum = 12;
            if (checkdate($monthnum, 31, $year)) {
                $date_to = $year . '-' . $monthnum . '-' . '31';
            } elseif(checkdate($monthnum, 30, $year))  {
                $date_to = $year . '-' . $monthnum . '-' . '30';
            } elseif(checkdate($monthnum, 29, $year))  {
                $date_to = $year . '-' . $monthnum . '-' . '29';
            } elseif(checkdate($monthnum, 28, $year))  {
                $date_to = $year . '-' . $monthnum . '-' . '28';
            }
        }

        if ($date_to) {
            $date_to = ' AND post_modified <= "'. $date_to  . '" ';
            return $date_to;
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
     public function get_YearsLinkArchives($echo = TRUE) {
        global $wpdb, $wp_locale;

        $where = "WHERE post_type = 'post' AND post_status = 'publish' ";
        $join = '';
        $limit ='';
        if ( 'monthly' == $this->get_type()) {
            /**
             * get distinct years
             */
            $query = "SELECT DISTINCT YEAR(post_date) AS `year` FROM $wpdb->posts
                        $where
                        GROUP BY YEAR(post_date)
                        ORDER BY post_date ASC
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
             * @todo :  menambah properties standar bagi class ini seperti $after, $before, $format
             * @todo :  algoritma darurat ini sebaiknya diganti dengan metode yg rapi dan
             *          tidak membingungkan
             */
            $format = 'custom';
            $before = ' [';
            $after = '] ';
            foreach ($yearresults as $years) {
                $url = get_year_link($years->year);
				$text = sprintf('%d', $years->year);
                $before = '<div class="year">';
                $after = '</div>';
                $output = get_archives_link($url, $text, $format, $before, $after);

                $month_number = 0;
                foreach ($arcresults as $arcresult) {
                    if ($month_number >= 13) {
                        $month_number = 0;
                    }

                    if ($years->year == $arcresult->year ) {
                        $month_number++;
                        /***
                         * try to print all month event those who doesn't have post in it
                         * $last_haspost_month bertugas sbg penanda bagi
                         * operasi CONDITIONAL Ke-2 (below)
                         */
                        $last_haspost_month = $arcresult->month;
                        while($month_number < 13 && $month_number < $last_haspost_month) {
                            $before = '<div class="nopost">';
                            $after = '</div>';
                            $text = sprintf(__('%1$s'), $wp_locale->get_month($month_number));
                            $output .=  "\t$before $text $after\n";
                            $month_number++;
                        }

                        $before = '<div class="haspost">';
                        $after = '</div>';

                        $url = get_month_link( $arcresult->year, $arcresult->month ) . '/';



                        $text = sprintf(__('%1$s'), $wp_locale->get_month($arcresult->month));
                        $output .= get_archives_link($url, $text, $format, $before, $after);


                     } else {
                           /**
                            * CONDITIONAL Ke-2
                            *
                            * Reverting last year's month that consist posts. then ileterate into
                            * tha last possible months (bulan ke-12)
                            */
                            while($last_haspost_month < 13) {
                                $last_haspost_month++;
                                $before = '<div class="nopost">';
                                $after = '</div>';
                                $text = sprintf(__('%1$s'), $wp_locale->get_month($last_haspost_month));
                                $output .=  "\t$before $text $after\n";
                            }

                           continue;
                     }
                }

                $beforeyear = '<li>';
                $afteryear = '</li>';
                /***
                 * adding xhtml after year/before years where the latest post will be
                 * in highest ladders
                 */
                $last_output = $beforeyear . $output . $afteryear . $last_output;
            }
        }


        if ($this->get_echo() == TRUE){
            echo $last_output;
        } else {
            return $last_output;
        }
    }

    /**
     * Create looping consist archieve posts based on years format
     *
     * @todo create how function work on non-pagination mode
     * @param <type> $echo
     * @param <type> $pagination
     * @return mixed
     */
    public function get_YearlyArhievesPosts($pagination=TRUE, $date_from='', $date_to= ''){
        global $wpdb;

        /*
        * determine current_page to know where is our pagination page and for our main loop
         * @todo membuat mode meretrieve all posts, lalu berdasar per-tahun,
        */
        $current_page = (get_query_var('paged')) ? get_query_var('paged') : get_query_var('page');
        $current_page = (!empty($current_page) && is_numeric($current_page) ) ? $current_page : 1;

        if ($pagination) {
            if (file_exists($pagination_path = $this->get_paginationfilepath() )) {
                /*
                * determine total post to know how many pagination
                * the class 'pagination' will create
                */
                if (empty($date_from)) {
                    $date_from = $this->get_date_from();
                }
                if (empty($date_to)) {
                    $date_to = $this->get_date_to();
                }

                $where = ' WHERE post_type="post" AND post_status="publish" ';
                $where .= $date_from . $date_to;

                $query = "SELECT ID, guid, post_type, post_date, post_title, post_status
                            FROM $wpdb->posts
                            $where";
                $postresults = $wpdb->get_results($query, ARRAY_A);

                if (is_array($postresults)) {
                    $total_posts = count($postresults);


                    /**
                     * get total pagination page that going to be created
                     */
                    $posts_per_page = get_option('posts_per_page');
                    $max_pagination_pages = ceil($total_posts / $posts_per_page);
                    $max_pagination_pages = ($max_pagination_pages == 0) ? 1 : $max_pagination_pages;

                    if ($total_posts > $posts_per_page) {
                        include($pagination_path);
                        $pagination = new Pagination();
                        $pagination->Items($total_posts);
                        $pagination->limit($posts_per_page);
                        $pagination->adjacents(1);
                        $pagination->currentPage($current_page);
                        $pagination->parameterName('page');
                        $pagination->target($this->get_target_url());
                        $pagination->changeClass('');
                        $pagination->nextLabel('DD');
                        $pagination->prevLabel('EE');
                        $pagination->nextT = '&gt;';
                        $pagination->prevT = '&lt;';
                        $pagination->nextI = '';
                        $pagination->prevI = '';
                        $pagination_loop = $pagination->getOutput();

                        $output['pagination'] = $pagination_loop;
                    }
                }
            }
        } //end has_pagination state

        /**
        * get the main loop
        */
        $output['main_loop'] = $this->get_YearlyArhieves_loop($current_page, $max_pagination_pages, $posts_per_page, $date_from, $date_to);

        return $output;
    }

    /**
     * @todo create the main looping then adding pagination, if they want it in echo mode
     * then print them. If they want it in return value or non-echo mode then return it in seperated
     * output;
     *
     * @global  $wpdb
     * @param <type> $current_page
     * @param <type> $max_pagination_pages
     * @return <type>
     */
    public function get_YearlyArhieves_loop($current_page, $max_pagination_pages, $posts_per_page, $date_from='', $date_to='' ) {
        global $wpdb;

        if (empty($date_from)) {
            $date_from = $this->get_date_from();
        }
        if (empty($date_to)) {
            $date_to = $this->get_date_to();
        }

        $where = ' WHERE post_type="post" AND post_status="publish" ';
        $where .= $date_from . $date_to;

		if ($max_pagination_pages != '') {
            $limit = ' LIMIT ' . (($current_page - 1) * $max_pagination_pages) . ',' . $posts_per_page;
        }

        $query = "SELECT ID, post_type, post_date, post_title, post_status
                    FROM $wpdb->posts
                    $where
                    ORDER BY post_date DESC
                    $limit";

        return  $wpdb->get_results($query);;
    }

    /**
     * Get archieve loop based on type of loopings
     *
     * @param <type> $pagination
     * @return <type>
     */
    public function get_ArchivesLoop($pagination = TRUE){
        switch($this->type) {
            case 'yearly' :
                $output = $this->get_YearlyArhievesPosts($pagination);
                break;
            default :
                break;
        };

        return $output;

    }
 } //end of class