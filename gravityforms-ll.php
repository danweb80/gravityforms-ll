<?php

/*
 * Plugin Name: Gravity Forms - LeadLovers
 * Description: Cadastra em Cursos e/ou Máquinas
 * Author: Daniel Weber 
 * Author URI: mailto://prof.daniel.weber@gmail.com
 * Version: 0.7
 * License: GPLv2 or later
 */

 //Prefix/slug - gravityforms-ll

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'GF_LEADLOVERS_VERSION', '0.7' );

add_action( 'gform_loaded', array( 'GF_LeadLovers_Bootstrap', 'load' ), 5 );
class GF_LeadLovers_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gravityforms-ll.php' );

        GFAddOn::register( 'GFLeadLoversAddOn' );
    }

}

function gf_leadlovers_addon() {
    return GFLeadLoversAddOn::get_instance();
}

