<?php
/***
 * 	description : Flag and state to control user State on wordpress
**/
/**
 * Library for wordpress user state management. This library try to extend how wordpress control
 * user registration, especially how about user waiting for them to click registration validation
 * that send to them
 *
 * @property boolean $isGuest regular read property
 * @category User
 * @package User Registration
 * @subpackage User Registration Validation
 * 
 * @example _example_WpUserState.php
 * @access public
 *
 * @author uwiuw
 * @copyright 2010 uwiuw
 */
class WpUserState {
	var $isGuest;
    public $sqlModule;

    /**
     * @notes builidng class with injection capability
     * @param <type> $module
     */
    function WpUserState($module) {
        include($module);
        $sqlModule = new sqlModule;
    }

	/*
     * apakah dia ini seorang guest
     */
	private function isGuest() {


	}

	/**
     * 	fungsi bersifat publik yg akan menetapkan prasyarat seseorang dianggap guest
     * 	oleh sistem.
	**/
	public function whoGuest($argms){
		$default = array (
			'login' => FALSE,
			'activated' => FALSE,
			'registersince' => '',
		);

		return array_merge($argms);
	}

    private function getWpdbOutput($sql){
        global $wpdb;
        return $wpdb->get_results($sql, ARRAY_A);
    }


    /**
     * get then combine ouputs of table user and table usermeta
     *
     * @param <type> $user_id
     * @param <type> $sql_only
     * @param <type> $mergesql
     * @return <type>
     */
    public function getUserData($user_id,  $sql_only = FALSE, $mergesql=FALSE){
        $sqlModule = new sqlModule;
		$sql = $sqlModule::getUserData_sql($user_id);

        if ($sql_only == TRUE) {
            return $sql;
        }

        $output = self::getWpdbOutput($sql, ARRAY_A);
        $output_usermeta = self::getUserMeta($user_id);

        if (count($output_usermeta) > 0) {
            $i = 0;
            foreach ($output as $users) {

                foreach ($output_usermeta as $usermeta) {
                    if ($usermeta['user_id'] == $users['ID']) {
                        $output[$i][$usermeta['meta_key']] = $usermeta['meta_value'];
                    }
                }

                $i++; //array parent indes
            }
        }

        if ($mergesql == TRUE) {
            $output = $sqlModule::mergeSql($output, $sql);
        }

        return $output;
    }

    /**
     * get user meta based on usermeta_sql return value
     *
     * @param integer $user_id
     * @param boolean $sql_only hanya mereturn perintah sql-nya aja
     * @param boolean $mergesql when true, sql command akan digabungkan ke dalam output yg dihasilkan
     * @return array of user sql
     */
    public function getUserMeta($user_id,  $sql_only = FALSE, $mergesql=FALSE){
        $sqlModule = new sqlModule;
		$sql = $sqlModule::getUserMeta_sql($user_id);

        if ($sql_only == TRUE) {
            return $sql;
        }

        $output = self::getWpdbOutput($sql, ARRAY_A);

		if ($mergesql == TRUE) {
            $output = $sqlModule::mergeSql($output , $sql);
		}

        return $output;
    }

    /**
     *
     * @global <type> $wpdb
     * @param <type> $user_id
     * @param <type> $mergesql
     * @return <type>
     */
	public function getUserCapability($user_id,  $sql_only = FALSE, $mergesql=FALSE){
		$sqlModule = new sqlModule;
		$sql = $sqlModule::getUserMetaCapability_sql($user_id);

        if ($sql_only == TRUE) {
            return $sql;
        }

		$output = self::getWpdbOutput($sql, ARRAY_A);
        $output['user_capability'] = $output[0];
        /*
         * membuat key array teratas tetap bernama 'user_capability'
         */
        unset($output[0]);

		if ($mergesql == TRUE) {
            $output = $sqlModule::mergeSql($output , $sql);
		}

		return $output;
	}

    /**
     * get array of user roles in user capability
     *
     * @global  $wpdb
     * @param <type> $user_id
     * @param <type> $serialize
     * @param <type> $mergesql
     * @return <type>
     */
	public function getUserCapabilityRoles($user_id, $serialize = TRUE, $mergesql=FALSE){
		$sqlModule = new sqlModule;
		$sql = $sqlModule::getUserMetaCapability_sql($user_id);
		$output = self::getWpdbOutput($sql, ARRAY_A);

        if ($output) {
            if ($serialize) {
                /**
                 * Array dipotong untuk mengambil isi meta value kemudian data yg
                 * berbentuk string dijadikan array php
                 */
                $output = array('user_roles' => unserialize($output[0]['meta_value']));
            } else {
                $output = array('user_roles' => $output[0]['meta_value']);
            }

            if ($mergesql == TRUE) {
                $output = $sqlModule::mergeSql($output, $sql);
            }
        }


		return $output;
	}
}

/**
 * Class that work with register/unregister user option in wordpress
 *
 * @notes   "default_password_nag" is an option name for a user who need
 *          to click a link that send to them before able to register in
 *          the blog
 * @see     wordpress 3.0.1
 *          - [common.dev.js]   setUserSetting('default_password_nag', 'hide');  [position 288:19]]
 *          - [upgrade.php]     update_user_option($user_id, 'default_password_nag', true, true); [position 70:33]
 *          -                   update_user_option($user_id, 'default_password_nag', true, true);      [position 70:33]]
 *          - [user.php]        function default_password_nag_edit_user($user_ID, $old_data)
 *          -                   function default_password_nag()
 */

class userRegister extends WpUserState{

    /**
     * loading then injection class
     *
     * @notes arsitektur class seperti ini membantu saat membangun phpunittest
     *
     * @param <type> $module
     * @param <type> $class_obj
     */
    public function userRegister($module = '', $class_obj = ''){
        if ($module && $class_obj) {
            include_once($module);
        }
    }

    public function isProtectedUser($user_id){
        if (empty($user_id)) {
            return false;
        }

        $output = self::getUserData($user_id);

        /*
         * checking whether the user has ID 1, pertanda user pertama
         */
        if ($output[0]['ID'] == '1') {
            return true;
        } elseif ((int) $output[0]['wp_user_level'] >= 10) {
            return true;
        } elseif ($output[0]['wp_capabilities'] != '') {
            /*
             * mulai unserializing menjadi array
             * @todo membuat mekanisme system yg berbeda yg menentukan roles apa
             * saja yg akan diprotected
             */
            $user_roles = unserialize($output[0]['wp_capabilities']);
            if ($user_roles['administrator'] == '1') {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

}