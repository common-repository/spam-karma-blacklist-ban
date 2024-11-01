=== Plugin Name ===
Contributors: Pinoy.ca
Donate link: http://wwf.com/
Tags: spam-karma, blacklist, ip-ban, ban
Requires at least: 2.1
Tested up to: 2.9
Stable tag: trunk

Bans IP addresses that have been caught and blacklisted by Spam Karma more than 5 times.

== Description ==

Once an IP address is knee-deep in Spam Karma's blacklist, Spam Karma will never approve a comment from this IP, no matter how often it tries.

Unfortunately, many of these IPs don't stop trying, thus wasting your bandwidth and server resources.

This small plugin bans them.

Note: this is not a Spam Karma 2 plugin; that's different.

== API ==

Here is the complete set of filters and hooks, so you never have to need to edit the plugin.  How's that for service?

= spam_karma_blacklist_ban_get_ip =

Filter applied to `$_SERVER['REMOTE_ADDR']` before the plugin uses it.  This filter runs on each page load.

*Possible use:* Disabling the plugin, or performing additional steps

= spam_karma_blacklist_ban_count =

Filter applied to the result of the query for the used_count of the blacklist table.  This filter runs on each page load.

*Possible use:* Increasing or decreasing the count, so the plugin bans more or less IPs.

= spam_karma_blacklist_do_ban =

Action run before "403 Forbidden" headers are sent to the browser.

*Possible use:* Logging the event, or sending a "301 Redirect" header instead

= spam_karma_blacklist_ban_activate =

Action run *before* the plugin initializes, i.e., alters the sk2_blacklist table structure.

= spam_karma_blacklist_ban_deactivate =

Action run *after* the plugin deinitializes, i.e., restores the sk2_blacklist table structure.

== Installation ==

1. Upload `spam-karma-blacklist-ban.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Is this a permanent ban? =

No. If you would look at your Spam Karma 2 admin screens, under "Blacklist", there is a section that instructs Spam Karma to remove blacklist entries added or last used more than x days ago with scores less than y. This is where you configure whether and which IPs are banned temporarily or permanently.

= Is this fast? =

We made this with speed foremost in mind.  During plugin initialization, the plugin tells MySQL to create an table index of the SK2 Blacklist table.  IP lookup therefore becomes insanely fast.

This plugin fires at the very first stage of the WordPress loading process, so the offender is banned there and no further WordPress processing takes place.

= How do I ban offenders caught more than 5 or less than 5 times? =

Two ways to do this:

1. Define SPAM_KARMA_BLACKLIST_COUNT_TO_BAN in your `wp_config.php`, or edit the plugin directly.
1. Create a filter to `spam_karma_blacklist_ban_count` in your theme's `functions.php`. For example,

`
add_filter( 'spam_karma_blacklist_ban_count', 'my_ban_20_count' );
function my_ban_20_count( $count ) {
	return $count + 5 - 20; // Only ban IPs caught more than 20 times.
}
`

= How do I ban certain IPs permanently, or allow certain IPs permanently? =

Create a filter to `spam_karma_blacklist_ban_count` in your theme's `functions.php`.  For example,

`
add_filter( 'spam_karma_blacklist_ban_count', 'ban_Kevin' );
function ban_Kevin( $count ) {
	if ( is_this_kevin() )
		return SPAM_KARMA_BLACKLIST_COUNT_TO_BAN + 1; // ban him.
	return $count;
}
`

= How do I know if this plugin is working? =

A future version will have a reporting and testing screen.

== Changelog ==

= 1.0.1 =
* Creates a non-UNIQUE index in case creating a UNIQUE index fails.

= 1.0 =
* Unreleased
