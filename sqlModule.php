<?php

class sqlModule {

    public $tbl_prefix;
    
    /**
     * merging array of output with last sql request
     *
     * @param <type> $output
     * @param <type> $sql
     * @return <type>
     */

    public function sqlModule(){
        global $wpdb;
        $this->tbl_prefix = $wpdb->prefix;
    }

    /**
     * get cobination of sql for table user and table usermeta
     * @todo : fix this
     * @global  $wpdb
     * @param <type> $user_id
     * @return string
     */
    public function getUserData_sql($user_id){
         global $wpdb;
 
         $sql =  ' SELECT * ';
         $sql .= ' FROM ' . $wpdb->prefix . 'users';
         $sql .= ' WHERE ID =' . $user_id;

         return $sql;
	}

    /**
     *
     * @global  $wpdb
     * @param <type> $user_id
     * @return string 
     */
    public function getUserMeta_sql($user_id){
         global $wpdb;

         $sql =  ' SELECT user_id, meta_key, meta_value';
         $sql .= ' FROM ' . $wpdb->prefix . 'usermeta';
         $sql .= ' WHERE user_id =' . $user_id; 

         return $sql;
	}
    
    /**
     *
     * @global <type> $wpdb
     * @param <type> $user_id
     * @return string
     */
    public function getUserMetaCapability_sql($user_id){
         global $wpdb;

         $sql = self::getUserMeta_sql($user_id, TRUE);
         $sql .= ' AND meta_key="wp_capabilities"';
         
         return $sql;
	}

	public function mergeSql($output, $sql){
		return array_merge($output, array('mergesql' => $sql));
	}


}