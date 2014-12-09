<?php
/**
 * @package  MushRaider Bridge
 * @author   Mush
 */

// require_once('../../../wp-blog-header.php');
require_once( '../../../wp-load.php' );
header('Content-Type: application/json');

$salt = get_option('mushraider_api_key');
$jsonRoleMapping = json_decode(get_option('mushraider_roles_mapping'));

if(!empty($salt)) {
    if(empty($_POST['login']) || empty($_POST['pwd'])) {
        echo json_encode(array('authenticated' => false));
        exit;
    }

    // Decrypt password
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $pwd = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $salt, stripslashes($_POST['pwd']), MCRYPT_MODE_ECB, $iv));

    /*
    * Test here if the user exist and have the good permissions etc...
    */
    $auth = array();
    $auth['user_login'] = $_POST['login'];
    $auth['user_password'] = $pwd;
    $auth['remember'] = false;
    $user = wp_signon($auth, false);
    if(is_wp_error($user)) {
        echo json_encode(array('authenticated' => false));
        exit;
    }

    if(!$user->has_cap('mushraider_login')) {
        echo json_encode(array('authenticated' => false));
        exit;
    }

    // Return json to mushraider
    $userInfos = array();
    $userInfos['authenticated'] = true;
    $userInfos['email'] = $user->get('user_email');

    if(!empty($user->roles)) {
        foreach($user->roles as $userRoleSlug) {
            if(isset($jsonRoleMapping->$userRoleSlug)) {
                if($jsonRoleMapping->$userRoleSlug != 'notallowed' || empty($userInfos['role'])) {
                    $userInfos['role'] = $jsonRoleMapping->$userRoleSlug;
                }
            }
        }
    }

    if($userInfos['role'] == 'notallowed') {
        echo json_encode(array('authenticated' => false));
        exit();
    }
     
    echo json_encode($userInfos);
    exit;
}