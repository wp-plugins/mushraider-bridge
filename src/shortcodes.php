<?php
/**
 * @package  MushRaider Bridge
 * @author   Mush
 */

function mushraider_sc_roster($atts, $content = null) {
    $a = shortcode_atts(array(
        'game' => ''
    ), $atts);

    $apiKey = get_option('mushraider_api_key');
    $apiUrl = get_option('mushraider_api_url');

    $game = $a['game'];
    $endPoint = '/characters/index/game:'.$game;
    $hmac = hash_hmac('sha1', $endPoint, $apiKey);
    $endPoint .= '/key:'.$hmac;        
    $remoteCharacters = wp_remote_retrieve_body(wp_remote_get($apiUrl.'api'.$endPoint.'.json')); // Request
    $output = '<table class="mushraider_roster">';
        $output .= '<thead>';
            $output .= '<tr>';
                $output .= '<th>'.__('Name', 'mushraider').'</th>';
                $output .= '<th>'.__('Level', 'mushraider').'</th>';
                $output .= '<th>'.__('Role', 'mushraider').'</th>';
                $output .= '<th>'.__('Classe', 'mushraider').'</th>';
                $output .= '<th>'.__('Race', 'mushraider').'</th>';
                $output .= '<th>'.__('User', 'mushraider').'</th>';
            $output .= '</tr>';
        $output .= '</thead>';
        if(!empty($remoteCharacters)) {
            $remoteCharacters = json_decode($remoteCharacters);
            if(!empty($remoteCharacters->characters)) {
                foreach($remoteCharacters->characters as $character) {
                    $output .= '<tr>';
                        $gameLogo = strpos($character->Game->logo, 'http://') !== false || strpos($character->Game->logo, 'api.raidhead.com') !== false?$character->Game->logo:$apiUrl.$character->Game->logo;
                        $output .= '<td>'.(!empty($character->Game->logo)?'<img src="'.$gameLogo.'" class="icon" /> ':'').$character->Character->title.'</td>';
                        $output .= '<td>'.$character->Character->level.'</td>';
                        $output .= '<td>'.$character->RaidsRole->title.'</td>';
                        $classeIcon = strpos($character->Classe->icon, 'http://') !== false || strpos($character->Game->logo, 'api.raidhead.com') !== false?$character->Classe->icon:$apiUrl.$character->Classe->icon;
                        $output .= '<td style="color:'.$character->Classe->color.'">'.(!empty($character->Classe->icon)?'<img src="'.$classeIcon.'" class="icon" /> ':'').$character->Classe->title.'</td>';
                        $output .= '<td>'.$character->Race->title.'</td>';
                        $output .= '<td>'.$character->User->username.'</td>';
                    $output .= '</tr>';
                }
            }
        }
    $output .= '</table>';

    return $output;
}