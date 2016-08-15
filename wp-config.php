<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'massagewp');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');
define( 'WP_DEBUG', false ); 
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '^J)EO:>?Wt+sh!eQ/udXifRk7M4lQtS+e[i#|J-C?32%l6]TUd_<U%l+)K*>HY>}');
define('SECURE_AUTH_KEY',  'A%CB+|B8ea+*UVjl$]Hg+`6hWtH:!T9mHZGb-wfyv.&Bdp17R))rpuH10hp#:sF:');
define('LOGGED_IN_KEY',    'W)06hH82W2 lIE6+D,M1THi)w=VgNB^Ng[R:#oUTRoIT=fhFuQeG6vw/pQJ`n|&>');
define('NONCE_KEY',        '[5h9njFzrOd(7AD0+`V&}607#jj3@,S+wiVkfzsc@T}(4y~kKXu1q2?^92h(!Mi3');
define('AUTH_SALT',        'x8ipIK-<pf+xWpXP+sECJ<Vv3NIni+E>${z~Ziq_#?st&1X%!>P/scjG0k98|t`6');
define('SECURE_AUTH_SALT', 'gc*.d~RQWFwo&=La$R)%AQF8tWoR3c$4_/r^w*P Da=d|FlaeCUGy#(QyRW2X-|=');
define('LOGGED_IN_SALT',   '_T>&T+:juS5!+4D2T9ttpnAnN{Ji|6%DEe|/>,M#@!0aa<&#CR]gzjQk_O j0oR-');
define('NONCE_SALT',       '?>~TgaIYGsMSJwosTInaD VCg,N3o)`?-Yn@c=Q>-d#,PQ*hjj|:C@zn;m|k2cWg');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
