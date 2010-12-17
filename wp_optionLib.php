<?php
/**
 * Library for exploring wordpress options
 *
 * @category Options
 * @package Options
 * @subpackage Option
 * @example _example_wp_optionLib.php
 *
 * @access public
 *
 * @author uwiuw
 * @copyright 2010 uwiuw
 */

class wp_optionLib {

    public function wp_optionLib(){

    }

    /**
     *
     * @global <type> $wpdb
     * @param <type> $field
     * @param <type> $echo
     * @return string
     *
     * @todo Cari perintah sql untuk meretrieve nama2x field pd sebuah table
     */
    public function get_optiontable_sql($field ='*' , $where='', $echo = FALSE){
        global $wpdb;

        $sql ="SELECT $field FROM " . $wpdb->prefix . "options";
        if ($where != '') {
            $sql .= ' WHERE ' . $where;
        }


        $hasildebug = print_r($sql, TRUE);
        echo '<pre style="font-size:14px">' . '$hasildebug : ' . htmlentities2($hasildebug) . '</pre>';


        if (!$echo){
            return $sql;
        } else {
            echo $sql;
        }
    }

    public function get_optiontable($field ='*', $where='', $echo = FALSE){
        global $wpdb;

        $sql = $this->get_optiontable_sql($field, $where, $echo);
        $outputs = $wpdb->get_results($sql, ARRAY_A);

        if (count($outputs) > 0) {
            if (!$echo){
                return $outputs;
            } else {
                echo $outputs;
            }
        }
    }
    
    public function get_option_value($option_name, $echo = FALSE){
        global $wpdb;

        $field = 'option_name,option_value';
        $where = " $option_name";

        $sql = $this->get_optiontable_sql($field, $where);
        $outputs = $wpdb->get_results($sql, ARRAY_A);

        if (count($outputs) > 0) {
            if (!$echo){
                return $outputs;
            } else {
                echo $outputs;
            }
        }
    }

}