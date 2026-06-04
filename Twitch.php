<?php
/**
 * Twitch Module Plugin
 *
 * Plugin Name:       Twitch Module
 * Description:       Een eenvoudige module voor het integreren van de Twitch Player in WordPress.
 * Version:           1.0.0
 * Author:            Jouw Naam
 * Text Domain:       twitch-module
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

// Versie van de plugin
define( 'TWITCH_MODULE_VERSION', '1.0.0' );

/**
 * Activatie van de plugin
 */
function twitch_module_activate() {
    // Acties bij activatie, bijv. opties aanmaken
}
register_activation_hook( __FILE__, 'twitch_module_activate' );

/**
 * Deactivatie van de plugin
 */
function twitch_module_deactivate() {
    // Acties bij deactivatie, bijv. tijdelijke data verwijderen
}
register_deactivation_hook( __FILE__, 'twitch_module_deactivate' );

/**
 * Core class voor de Twitch Module
 */
class Twitch_Module {

    public function __construct() {
        // Admin of frontend hooks
        add_shortcode('twitch_player', [$this, 'display_twitch_player']);
    }

    /**
     * Display Twitch Player via shortcode [twitch_player channel="kanaalnaam"]
     */
    public function display_twitch_player($atts) {
        $atts = shortcode_atts([
            'channel' => 'twitch',
            'width'   => '800',
            'height'  => '450',
        ], $atts, 'twitch_player');

        $channel = esc_attr($atts['channel']);
        $width   = esc_attr($atts['width']);
        $height  = esc_attr($atts['height']);

        return '<iframe
            src="https://player.twitch.tv/?channel=' . $channel . '&parent=' . $_SERVER['HTTP_HOST'] . '"
            height="' . $height . '"
            width="' . $width . '"
            allowfullscreen="true">
        </iframe>';
    }
}

// Plugin starten
function run_twitch_module() {
    $plugin = new Twitch_Module();
}
run_twitch_module();
