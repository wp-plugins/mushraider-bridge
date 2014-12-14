<?php
/**
 * @package  MushRaider Bridge
 * @author   Mush
 */

class MushraiderBridgeEvents_Widget extends WP_Widget {
    var $apiKey;
    var $apiUrl;
    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'mushraider_bridge_events_widget', // Base ID
            __('MushRaider events', 'mushraider'), // Name
            array('description' => __('Display events from your MushRaider website', 'mushraider')) // Args
        );

        $this->apiKey = get_option('mushraider_api_key');
        $this->apiUrl = rtrim(get_option('mushraider_api_url'), '/');
    }

    /**
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }

        $game = $instance['game'];
        $period = !empty($instance['period'])?$instance['period']:7;
        $eventsUntil = mktime(23, 59, 59, date('n'), date('j') + $period, date('Y'));
        $endPoint = '/events/index/end:'.$eventsUntil.'/game:'.$game;
        $hmac = hash_hmac('sha1', $endPoint, $this->apiKey);
        $endPoint .= '/key:'.$hmac;        
        $remoteEvents = wp_remote_retrieve_body(wp_remote_get($this->apiUrl.'/api'.$endPoint.'.json')); // Request
        if(!empty($remoteEvents)) {
            $remoteEvents = json_decode($remoteEvents);
            if(!empty($remoteEvents->events)) {
                $dWidget = '<ul>';
                    foreach($remoteEvents->events as $event) {
                        $logo = strpos($event->Game->logo, '//') !== false?$event->Game->logo:$this->apiUrl.'/'.trim($event->Game->logo, '/');
                        $dWidget .= '<li>';
                            $dWidget .= '<div class="logo"><img src="'.$logo.'" alt="'.$event->Game->title.'" /></div>';
                            $dWidget .= '<div class="event">';
                                $dWidget .= '<div class="title"><a href="'.$this->apiUrl.'/events/view/'.$event->Event->id.'" target="_blank">'.$event->Event->title.'</a></div>';
                                $dWidget .= '<div class="dungeon">'.$event->Dungeon->title.'</div>';
                                $dWidget .= '<div class="time">'.$this->niceDate($event->Event->time_invitation, true).'</div>';
                            $dWidget .= '</div>';
                        $dWidget .= '</li>';
                    }
                $dWidget .= '</ul>';
                echo $dWidget;
            }
        }
        
        echo $args['after_widget'];
    }

    /**
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        // Get games
        $endPoint = '/games/index';
        $hmac = hash_hmac('sha1', $endPoint, $this->apiKey);
        $endPoint .= '/key:'.$hmac;        
        $remoteGames = wp_remote_retrieve_body(wp_remote_get($this->apiUrl.'/api'.$endPoint.'.json')); // Request
        // Periods
        $periods = array('1' => '1 '.__('day', 'mushraider'), '3' => '3 '.__('days', 'mushraider'), '5' => '5 '.__('days', 'mushraider'), '7' => '7 '.__('days', 'mushraider'), '10' => '10 '.__('days', 'mushraider'), '15' => '15 '.__('days', 'mushraider'), '30' => '30 '.__('days', 'mushraider'));

        $wTitle = isset($instance['title'])?$instance['title']:__('Incoming events', 'mushraider');
        $wGame = isset($instance['game'])?$instance['game']:'';
        $wPeriod = isset($instance['period'])?$instance['period']:'';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title');?>"><?php echo __('Title:', 'mushraider'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($wTitle); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('game');?>"><?php echo __('Game:', 'mushraider'); ?></label> 
            <select class="widefat" id="<?php echo $this->get_field_id('game'); ?>" name="<?php echo $this->get_field_name('game'); ?>">
                <option value=""><?php echo __('All', 'mushraider');?></option>
                <?php if(!empty($remoteGames)):?>
                    <?php $remoteGames = json_decode($remoteGames);?>
                    <?php if(!empty($remoteGames->games)):?>
                        <?php foreach($remoteGames->games as $game):?>
                            <option value="<?php echo $game->Game->id;?>" <?php echo $wGame == $game->Game->id?'selected="selected"':'';?>><?php echo $game->Game->title;?></option>
                        <?php endforeach;?>
                    <?php endif;?>
                <?php endif;?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('period');?>"><?php echo __('Period:', 'mushraider'); ?></label> 
            <select class="widefat" id="<?php echo $this->get_field_id('period'); ?>" name="<?php echo $this->get_field_name('period');?>">
                <?php foreach($periods as $value => $period):?>
                    <option value="<?php echo $value;?>" <?php echo $wPeriod == $value?'selected="selected"':'';?>><?php echo $period;?></option>
                <?php endforeach;?>
            </select>
        </p>
        <?php 
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']))?strip_tags($new_instance['title']):'';
        $instance['game'] = (!empty($new_instance['game']))?strip_tags($new_instance['game']):'';
        $instance['period'] = (!empty($new_instance['title']))?strip_tags($new_instance['period']):'';

        return $instance;
    }

    public function niceDate($date, $preciseDate = false) {
        $dates = explode(' ', $date);
        $jours = explode('-', $dates[0]);
        $heures = explode(':', $dates[1]);

        if($date > date('Y-m-d H:i:s') && !$preciseDate) {
            $date_a = new DateTime(date('Y-m-d H:i:s'));
            $date_b = new DateTime($date);
            $interval = date_diff($date_a, $date_b);
            $intervalFormat = explode(':', $interval->format('%d:%h:%I'));

            $str = $intervalFormat[0] > 0?$intervalFormat[0].' '.__('day', 'mushraider').($intervalFormat[0] > 1?'s':'').' ':'';
            $str .= $intervalFormat[1] > 0?$intervalFormat[1].'h':'';
            $str .= $intervalFormat[0] == 0 && $intervalFormat[2] > 0?$intervalFormat[2]:'';

            if($preciseDate && $dates[0] == date("Y-m-d")) {
                $str .= ' ('.__('Today at', 'mushraider').' '.$heures[0].'h'.$heures[1].')';
            }

            return __('in', 'mushraider').' '.$str;
        }elseif($dates[0] == date("Y-m-d")) {
            $text = __('Today at', 'mushraider');
            return $text.' '.$heures[0].'h'.$heures[1];
        }elseif($dates[0] == date('Y-m-d', time() - 3600 * 24)) {
            $text = __('Yesterday at', 'mushraider');
            return $text.' '.$heures[0].'h'.$heures[1];
        }else {
            $wording = date('D', mktime(0, 0, 0, $jours[1], $jours[2], $jours[0])).' ';
            $wording .= $jours[2].' '.(date('M', intval($jours[1]))).' '.$jours[0];
            return $wording;
        }
    }
}