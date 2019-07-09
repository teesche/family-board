<?php

require_once '../inc/config.inc.php';

$weatherURL = 'https://api.darksky.net/forecast/' . $darkSkyKey . '/' . $lat . ',' . $lon;

$weather = file_get_contents($weatherURL);
echo $weather;
exit;