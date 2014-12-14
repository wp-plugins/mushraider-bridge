<?php
/*
Plugin Name: MushRaider Bridge
Plugin URI: http://mushraider.com
Description: MushRaider Bridge allows you to integrate MushRaider into wordpress.
Version: 1.0.4
Author: Mush
Author URI: http://mushraider.com
License: GPL2
*/

/*  
	Copyright 2014 MushRaider Bridge (email : contact@mushraider.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once('src/widget_events.php');
require_once('src/shortcodes.php');
define('MUSHRAIDER_PLUGIN_PATH', dirname(__FILE__));

register_activation_hook(__FILE__, 'mushraider_activate');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mushraider_plugin_action_links');
add_action('plugins_loaded', 'mushraider_loaded');
add_action('admin_menu', 'mushraider_menu');
add_action(
    'widgets_init',
    create_function('', 'register_widget("MushraiderBridgeEvents_Widget");')
);
add_action('wp_head', 'mushraider_css', 999);
add_shortcode('mushraider_roster', 'mushraider_sc_roster');

function mushraider_activate() {
    update_option('mushraider_api_key', '');
    update_option('mushraider_api_url', '');
    update_option('mushraider_roles_mapping', '');
    update_option('mushraider_css', '');
}

function mushraider_loaded() {
    load_plugin_textdomain('mushraider', false, dirname(plugin_basename(__FILE__)).'/locale/'); 
}

function mushraider_plugin_action_links($links) {
    $mylinks = array(
        '<a href="'.admin_url('admin.php?page=mushraider-bridge/mushraider-bridge.php').'">'.__('Settings', 'mushraider').'</a>',
        '<a href="http://mushraider.com" target="_blank">'.__('Get MushRaider', 'mushraider').'</a>'
    );
    return array_merge($mylinks, $links);
}

function mushraider_css() {
    $custom_css = get_option('mushraider_css');
    echo '<style type="text/css">'."\n";
        echo '.widget_mushraider_bridge_events_widget li { margin-bottom:5px }'."\n";
        echo '.widget_mushraider_bridge_events_widget .logo { display:inline-block;vertical-align:top;width:10%;border:0;margin:0 0.5em 0.5em 0 }'."\n";
        echo '.widget_mushraider_bridge_events_widget .logo img { width:100% }'."\n";
        echo '.widget_mushraider_bridge_events_widget .event { display:inline-block;vertical-align:top; }'."\n";
        echo '.widget_mushraider_bridge_events_widget .time { font-style:italic;font-size:80% }'."\n";
        echo '.mushraider_roster .icon { width:16px }'."\n";
        if(!empty($custom_css)) {
            echo $custom_css;
        }
    echo '</style>'."\n";
}

function mushraider_menu() {
    $main_page = apply_filters('mushraider_menu_main_page', __FILE__);
    add_menu_page(__('MushRaider', 'mushraider'), __('MushRaider', 'mushraider'), 'manage_options', $main_page, 'mushraider_render_bridge', plugins_url( 'images/icone.png', __FILE__ ));
    add_submenu_page($main_page, __('Bridge Settings', 'mushraider'), __( 'Bridge settings', 'mushraider' ), 'manage_options', basename(MUSHRAIDER_PLUGIN_PATH).'/mushraider-bridge.php', 'mushraider_render_bridge');
    add_submenu_page($main_page, __('Roles Settings', 'mushraider'), __( 'Roles mapping', 'mushraider' ), 'manage_options', basename(MUSHRAIDER_PLUGIN_PATH).'/mushraider-bridge-roles.php', 'mushraider_render_roles');
}

function mushraider_render_bridge() {
    $mushraiderLoginUrl = plugins_url('mushraider-bridge-login.php', __FILE__);
    $wpRoles = get_editable_roles();
    $mushraiderRoles = array(
        'notallowed' => 'Not allowed',
        'member' => 'Member',
        'officer' => 'Officer',
        'admin' => 'Admin'
    );

    if(isset($_POST['mushraider_update'])) {
        check_admin_referer('mushraider_bridge_page');

        if(isset($_POST['mushraider_api_key'])) {
            update_option('mushraider_api_key', $_POST['mushraider_api_key']);
        }

        if(isset($_POST['mushraider_api_url'])) {
            update_option('mushraider_api_url', trailingslashit($_POST['mushraider_api_url']));
        }

        if(isset($_POST['mushraider_css'])) {
            update_option('mushraider_css', $_POST['mushraider_css']);
        }

        echo "<div class='updated fade'><p><strong>Options saved</strong></p></div>";
    }
	?>
	<div class="wrap">
		<h2><?php echo __('MushRaider Bridge : Settings', 'mushraider');?></h2>

        <div class="manage-menus">
            <h3><?php echo __('Login url (Add it to your MushRaider\'s admin panel settings)', 'mushraider');?></h3>
            <span><?php echo $mushraiderLoginUrl;?></span>            
        </div>

		<form method="post" action="">
			<?php wp_nonce_field('mushraider_bridge_page'); ?>
			<table class="form-table">
				<tr valign="top">
                    <th scope="row"><?php echo __('API private key', 'mushraider');?></th>
                    <td>
                        <input type="text" name="mushraider_api_key" value="<?php echo get_option('mushraider_api_key'); ?>" style="width:400px" />
                        <p><small><?php echo __('API private key can be found in your MushRaider admin panel under the menu "Settings => API"', 'mushraider');?></small></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __('API url', 'mushraider');?></th>
                    <td>
                        <input type="text" name="mushraider_api_url" value="<?php echo get_option('mushraider_api_url'); ?>" style="width:400px" />
                        <p><small><?php echo __('URL of your MushRaider install', 'mushraider');?></small></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo __('Custom CSS', 'mushraider');?></th>
                    <td>
                        <textarea name="mushraider_css" style="width:400px;min-height:100px"><?php echo get_option('mushraider_css'); ?></textarea>
                        <p><small><?php echo __('Your own CSS to customize the widget display', 'mushraider');?></small></p>
                    </td>
                </tr>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="mushraider_api_key,mushraider_api_url" />
			</table>
			<p class="submit">
				<input type="submit" name="mushraider_update" class="button-primary" value="<?php echo __('Save Changes', 'mushraider') ?>" />
			</p>
		</form>        

        <p><?php echo __('To display your roster in pages you need to use the shortcode [mushraider_roster game="game_id"]. The "game_id" parameter is optional and can be found in your MushRaider website.', 'mushraider');?></p>
	</div>
	<?php
}

function mushraider_render_roles() {
    $apiKey = get_option('mushraider_api_key');
    $apiUrl = get_option('mushraider_api_url');

    $wpRoles = get_editable_roles();

    // MushRaider default roles
    $mushraiderRoles = array(
        'notallowed' => 'Not allowed',
        'member' => 'Member',
        'officer' => 'Officer',
        'admin' => 'Admin'
    );

    // Get roles from MushRaider API
    if(!empty($apiKey) && !empty($apiUrl)) {
        // Generate security key with the private key from the APP
        // And construct the url
        $endPoint = '/roles/index';
        $hmac = hash_hmac('sha1', $endPoint, $apiKey);
        $endPoint .= '/key:'.$hmac;
        // Request
        $remoteRoles = wp_remote_retrieve_body(wp_remote_get($apiUrl.'api'.$endPoint.'.json'));
        // Get datas
        if(!empty($remoteRoles)) {
            $remoteRoles = json_decode($remoteRoles);
            if(!empty($remoteRoles->roles)) {
                $mushraiderRoles = array('notallowed' => 'Not allowed');
                foreach($remoteRoles->roles as $remoteRole) {
                    $mushraiderRoles[$remoteRole->Role->alias] = $remoteRole->Role->title;
                }
            }
        }
    }


    if(isset($_POST['mushraider_update'])) {
        check_admin_referer('mushraider_roles_page');

        if(!empty($_POST['mushraider_roles_mapping'])) {
            $jsonRoleMapping = array();
            foreach($_POST['mushraider_roles_mapping'] as $roleMap) {
                list($wpRoleSlug, $mushraiderRoleSlug) = explode('|', $roleMap);
                $wpRole = get_role($wpRoleSlug);
                if(!empty($mushraiderRoleSlug)) {
                    $wpRole->add_cap('mushraider_login');
                    $jsonRoleMapping[$wpRoleSlug] = $mushraiderRoleSlug;
                }else {
                    $wpRole->remove_cap('mushraider_login');
                }
                update_option('mushraider_roles_mapping', json_encode($jsonRoleMapping));
            }

            $wpRoles = get_editable_roles();
        }
        echo "<div class='updated fade'><p><strong>Options saved</strong></p></div>";
    }
    ?>
    <div class="wrap">
        <h2><?php echo __('MushRaider Bridge : Roles mapping', 'mushraider');?></h2>

        <div class="manage-menus">
            <?php if(empty($apiKey) || empty($apiUrl)):?>
                <p><?php echo sprintf(__('To get your custom roles set in MushRaider you have to configure the <a href="%s">bridge settings page</a>', 'mushraider'), 'admin.php?page='.basename(MUSHRAIDER_PLUGIN_PATH).'/mushraider-bridge.php');?></p>
            <?php else:?>
                <p><?php echo __('Roles mapping is usefull to set default roles depending on your wordpress users roles', 'mushraider');?></p>
            <?php endif;?>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('mushraider_roles_page'); ?>
            <?php if(!empty($wpRoles)):?>
                <?php $rolesMapping = json_decode(get_option('mushraider_roles_mapping'));?>
                <table>
                    <thead>
                        <tr>
                            <th><?php echo __('Wordpress', 'mushraider');?></th>
                            <th><?php echo __('MushRaider', 'mushraider');?></th>
                        </th>
                    </thead>
                    <tbody>
                        <?php foreach($wpRoles as $roleSlug => $wpRole):?>
                            <tr>
                                <td><?php echo $wpRole['name'];?></td>
                                <td>
                                    <select name="mushraider_roles_mapping[]">
                                        <?php foreach($mushraiderRoles as $mrRoleSlug => $mushraiderRole):?>
                                            <?php $selected = isset($rolesMapping->$roleSlug) && $rolesMapping->$roleSlug == $mrRoleSlug?true:false;?>
                                            <option value="<?php echo $roleSlug.'|'.$mrRoleSlug;?>" <?php echo isset($wpRole['capabilities']['mushraider_login']) && $selected?'selected="selected"':'';?>><?php echo $mushraiderRole;?></option>
                                        <?php endforeach;?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            <?php endif;?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="mushraider_roles_mapping" />

            <p class="submit">
                <input type="submit" name="mushraider_update" class="button-primary" value="<?php echo __('Save Changes', 'mushraider');?>" />
            </p>
        </form>        
    </div>
    <?php
}
?>