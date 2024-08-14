<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress_citie' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'wJD:u. t4=U+N%vB+N&{;!ZWd-x.v5wmT`X4Fy^OFrnKv])ZI*L?tOOys Z>y%tn' );
define( 'SECURE_AUTH_KEY',  'W;]+Hpq-<wuk|k>(Vi@[v=?M)kUKG?_v^$7_:jzDj/|0!xw*d*mAS)%d,fv}%p2y' );
define( 'LOGGED_IN_KEY',    '=Ck5P.oUe]z{9TI_ WhJ6/v(gi<>SB;:UU4 8P}vKRMfj]3wuItqU?b+VAR7:,*J' );
define( 'NONCE_KEY',        '!Yo-WFJZz|vs5bxDr)O|l;]tVg&}ca#|7}Y<sn&g.hm</9 p;;)BZ+~r#prv[o.l' );
define( 'AUTH_SALT',        '.o/F)p{TR@#1?Dj3bb>)JYc19tF5|o6zGTz?F:et%G03IZ%6>ON/d8rds/Wd*=#F' );
define( 'SECURE_AUTH_SALT', '3ILbTz~3gEnWX<rKHm<EY99+yU&N%AD};0e4-#4oD]+]{Cz9oH.Q9Au6L;*tK}@p' );
define( 'LOGGED_IN_SALT',   'R%J {qZt(L9F7+aToZ{~2G6nh={~L~v$qJ?kclX_)+Dd|r^BlOxK>x6Dt~,#UQN0' );
define( 'NONCE_SALT',       ':O=29!B&W/WAF4+`Fh(-|G]2Q;<SKC&iaZG8G.Lb*7K_}Zr&_A7IO~22hij/!hY,' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
