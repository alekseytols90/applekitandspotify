<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

define('BASE_DIR', dirname(__FILE__));
define('SITE_URL', 'http://localhost/applemusickit/');

// apple music credentials
$teamId = '6F2D68F76K';
$keyId = 'MPHLMKD673';
$privateKey = file_get_contents(BASE_DIR . '/.AuthKey_MPHLMKD673.p8');

// spotify credentials
define('SPOTIFY_CLIENT_ID', '681cf57c37844d289131cd12abfab9c5');
define('SPOTIFY_CLIENT_SECRET', '38be4904c0c64a94b6ae252b15372e08');
define('SPOTIFY_CLIENT_CALLBACK', SITE_URL . 'spotify-auth.php');


$appleMusicKit = new \App\AppleMusicKit($teamId, $keyId, $privateKey);
