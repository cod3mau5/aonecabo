<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'aonecabo_web');

/** MySQL database username */
define('DB_USER', 'aonecabo_web');

/** MySQL database password */
define('DB_PASSWORD', 'Amarillo77');

/** MySQL hostname */
define('DB_HOST', 'mysql.aonecabodeluxetransportation.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'ubQN{,kP~6?,-y3HZErB_lw%6k#iT4jJ^`[m[eZ$c3H.ZPa9/Zn4|At.Y.L+jhh*');
define('SECURE_AUTH_KEY',  '<EM}jz#y7^Mo}YI!(FMVYHgy_l_P;vip(+{s)3C!c@Of<vk)*;<hM~p2gl!/PzY0');
define('LOGGED_IN_KEY',    'jCaH3OipBh~uw<nzD>:~p,)nDYOBei:L,X<Us;.JjE0yw8!U?~9;R$4G|s?,%DLY');
define('NONCE_KEY',        'B) yB;QW;#BsuBW(8_70qFCoY@GWr ^eBt<+BsZdDRj,O,+Sg9>+ypoqQ+exDm&s');
define('AUTH_SALT',        'yrVg)XsNCVX/2$`jjI7xs=2fY:=v1y_2 [B5Q f!nX^JH[<;@;uc@kf=~MA}j&J ');
define('SECURE_AUTH_SALT', 'C0}[Jm+zV7Y -MSkw6sY~L`Q(p8l9huNQU}AyxqM-8E(&7Mu)!f>+~:y%J;zttil');
define('LOGGED_IN_SALT',   'Cwu_0f7k2}~ZH H^n{J<igZ>vR3!Y),C>-Dk?t)-F6-L)z@G<G<Xn(=d2tVg^ATq');
define('NONCE_SALT',       'vk*brc.* 1tQ-cM%b$Tw_]SN~/ P^NTc2thJ# hk3/6T5pjKeb}>Ft:62ZOW{5Uh');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
