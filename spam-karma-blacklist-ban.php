<?php
/*
Plugin Name: Spam Karma 2 Blacklist Ban
Version: 1.0.1
Plugin URI: http://www.pinoy.ca/
Description: Ban IP addresses caught and blacklisted by Spam Karma 2
Author: Pinoy.ca 
Author URI: http://www.pinoy.ca/

Copyright ( c ) 2009
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt

    This file is part of WordPress.
    WordPress is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    ( at your option ) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	INSTALL: 
	Unzip Plugin, upload to wp-content/plugins folder, activate. 

	LEGAL:
	GPL Copyright as above, Tested on WP 2.4+, No warranties promised or implied, use at your own risk
	
	UNINSTALL:
	Deactivate, then delete the plugin file.

*/

// Max number of links to keep
if ( !defined( SPAM_KARMA_BLACKLIST_COUNT_TO_BAN ) )
	define( SPAM_KARMA_BLACKLIST_COUNT_TO_BAN, 16 );


add_action( 'plugins_loaded', 'spam_karma_blacklist_ban', $priority = 1 );
function spam_karma_blacklist_ban() {
	// This isn't necessarily a permanent ban. SK2 removes from the 
	// blacklist those IPs who haven't spammed in 60 days.
	$ip = $_SERVER['REMOTE_ADDR'];
	$ip = apply_filters( 'spam_karma_blacklist_ban_get_ip', $ip );
	if( empty( $ip ) ) 
		return;
	
	global $wpdb;
	require_wp_db(); // in the off case that the database isn't loaded yet.

	$blacklist_count = $wpdb->get_var( $wpdb->prepare( "SELECT `used_count` FROM `sk2_blacklist` WHERE `type` = 'ip_black' AND `value` = '%s' LIMIT 1", $ip ) );
	apply_filters( 'spam_karma_blacklist_ban_count', $blacklist_count );
	if ( $blacklist_count - SPAM_KARMA_BLACKLIST_COUNT_TO_BAN > 0 ) {
		do_action( 'spam_karma_blacklist_ban_do_ban' );
		header("HTTP/1.0 403 Forbidden", true, 403);
		die();
	}
}

// The following three SQL statements are optional, but they make the 
// above SQL statement run *much* faster. Spam Karma didn't really 
// make the blacklist table as efficient as possible. 
// Deactivation puts the table structure back to SK2's defaults, but 
// isn't really critical.

register_activation_hook( __FILE__, 'spam_karma_blacklist_ban_activate' );
function spam_karma_blacklist_ban_activate() {
	global $wpdb;
	require_wp_db(); // in case the database isn't loaded yet
	do_action( 'spam_karma_blacklist_ban_activate' );
	$testfail = $wpdb->get_var( "ALTER TABLE `sk2_blacklist` CHANGE `type` `type` char(25) NOT NULL, CHANGE `value` `value` varchar(308) NOT NULL, DROP INDEX `spam_karma_blacklist_ban`, ADD UNIQUE `spam_karma_blacklist_ban` ( `value` , `type` )" );
	if ( empty( $testfail ) ) {
		// UNIQUE condition probably failed, so redo as a non-unique index
		$wpdb->get_var( "ALTER TABLE `sk2_blacklist` CHANGE `type` `type` char(25) NOT NULL, CHANGE `value` `value` varchar(308) NOT NULL, DROP INDEX `spam_karma_blacklist_ban`, ADD INDEX `spam_karma_blacklist_ban` ( `value` , `type` )" );
	}
}
	
register_deactivation_hook( __FILE__, 'spam_karma_blacklist_ban_deactivate' );
function spam_karma_blacklist_ban_deactivate() {
	global $wpdb;
	require_wp_db(); // in case the database isn't loaded yet
	$wpdb->get_var( "ALTER TABLE `sk2_blacklist` DROP INDEX `spam_karma_blacklist_ban`, CHANGE `type` `type` TINYTEXT NOT NULL, CHANGE `value` `value` TEXT NOT NULL" );
	do_action( 'spam_karma_blacklist_ban_deactivate' );
}
?>