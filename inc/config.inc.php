<?php

/* Error Reporting */
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$weekdays = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sonday'];

define('URI', '/family/');

define('DB_HOST', 'localhost');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_BASE', '');

/* Get your Dark Sky Weather API key here: https://darksky.net/dev */
$darkSkyKey = '';

/* Coordinates of your Family Board for Weather Check. */
$lat = 53.555158;
$lon = 9.995136;
// Middle of the Binnenalster lake/river in Hamburg