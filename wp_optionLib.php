<?php
/**
 * Library for exploring wordpress options table
 *
 * @uses wordpress core 3.0.x
 * @category Options
 * @package Options
 * @subpackage Option
 * @example _example_wp_optionLib.php
 *
 * @version 0.0.1 18desc2010
 *
 * @access public
 * @todo create function to list non-autoload/autoload option
 * @todo translate every phpdoc comment into english
 * @author uwiuw
 * @copyright 2010 uwiuw
 */

class wp_optionLib {

    public function wp_optionLib(){

    }

    /**
     * membuat perintah sql yg akan meretrieve field2x yg berada pada table wp_options
     *
     * @global object $wpdb
     * @param string $field
     * @param string $where
     * @param bool $echo
     * @return string
     *
     * @todo Cari perintah sql untuk meretrieve nama2x field pd sebuah table
     * @todo buat param $where bisa berbentuk array
     */
    public function get_optiontable_sql($field ='*' , $where='', $echo = FALSE){
        global $wpdb;

        $sql ="SELECT $field FROM " . $wpdb->prefix . "options";
        if ($where != '') {
            $sql .= ' WHERE ' . $where;
        }

        if (!$echo){
            return $sql;
        } else {
            echo $sql;
        }
    }

    /**
     * meretrieve isi table options berdasar prasayat sql
     *
     * @example $this->get_optiontable() will retrieve all information from the 
     * option table cause default field is *
     *
     * @global object $wpdb
     * @param string $field
     * @param string $where
     * @param bool $echo
     * @return array
     */
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

    /**
     * retrieve value sebuah option name dan unserialize value itu bila berbentuk array
     *
     * Sebagai catatan, wordpress option_name bisa memiliki berbagai macam value. bisa berbentuk
     * string, serialiaze array, numerical string. Dan semuanya disimpan ke dalam format string
     * sql hanya bisa menyimpan format string. Jadi kita @todo membuat function yg bisa
     * menerjemahkan option kedalam format format asalnya
     *
     * @global  $wpdb
     * @param string $option_name
     * @param bool $serialize if true and the $output is array then serialize them
     * @param bool $echo
     * @return mixed
     */
    public function get_option_value($option_name, $serialize=FALSE, $echo = FALSE){
        global $wpdb;

        $field = 'option_value';
        $where = "option_name='$option_name'";

        $sql = $this->get_optiontable_sql($field, $where);
        $outputs = $wpdb->get_results($sql, ARRAY_A);

        /*
         * check whether $outputs is an array
         */
        if (count($outputs) > 0) {
            if ($serialize) {
                if (count($outputs) > 1) {
                    /*
                     * if total count result lebih dari satu
                     */
                    foreach ($outputs as $output ) {
                        $outputs_temp[] = unserialize($output['option_value']);
                    }
                    $outputs = $outputs_temp;
                    unset($outputs_temp);
                } else {
                    /*
                     * bila total count cuma ada satu
                     */
                    $outputs = unserialize($outputs[0]['option_value']);

                }
            }
//            else {
//                /**
//                 * by passing
//                 */
//            }

            if (!$echo){
                return $outputs;
            } else {
                echo $outputs;
            }
        }
    }

    /**
     * retrieve value dari sekumpulan option value     *
     *
     * @global  $wpdb
     * @param array $option_names_args  pertiap index-array memiliki 2 option, pertama nama dan yg kedua
     *                                  apakah hendak diserialiaze atawa tidak
     * @param bool $echo
     * @return mixed
     */
    public function get_options_value($option_names_args=array(), $echo = FALSE){
        if (is_array($option_names_args) && count($option_names_args) > 0 ) {
            foreach ($option_names_args as $key_optionName =>$serial)  {
                    if ($hasil = $this->get_option_value($key_optionName, $serial)) {
                        $outputs[$key_optionName] = $hasil;
                    } 
            }

            if (!$echo){
                return $outputs;
            } else {
                echo $outputs;
            }
        }
    }
}