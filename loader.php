<?php
/*
 Plugin Name: BuddyPress xProfile Select2
 Plugin URI: http://themekraft.com/
 Description: select2
 Version: 1.0
 Author: Sven Lehnert
 Author URI: http://themekraft.com/members/svenl77/
 License: GPLv2 or later
 Network: false

 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */


function xprofile_select2_init() {

    include_once(dirname(__FILE__) . '/includes/field-types/multiselectbox2.php');

    add_filter( 'bp_xprofile_get_field_types', 'xprofile_select2_field_types', 10, 1 );
    add_action('bp_init', 'xprofile_select2_enqueue_script');
}
add_action( 'bp_include', 'xprofile_select2_init' );

function xprofile_select2_field_types($fields) {
    $new_fields = array(
        'multiselectbox2'  => 'BP_XProfile_Field_Type_Multiselectbox2',
    );
    $fields = array_merge($fields, $new_fields);

    return $fields;
}

function xprofile_select2_enqueue_script(){
    if (bp_is_user_profile_edit() || bp_is_register_page()) {
        wp_enqueue_script(	'xProfile-select2-js',        plugins_url('includes/resources/select2/select2.min.js', __FILE__) , array( 'jquery' ), '3.5.2' );
        wp_enqueue_style(	'xProfile-select2-css',       plugins_url('includes/resources/select2/select2.css', __FILE__));

        wp_enqueue_script(	'xProfile-select2-custom-js',   plugins_url('assets/js/xprofile-select2.js', __FILE__) , array( 'jquery' ) );
        wp_enqueue_style(	'xProfile-select2-custom-css',  plugins_url('assets/css/xprofile-select2.css', __FILE__));

    }
}

