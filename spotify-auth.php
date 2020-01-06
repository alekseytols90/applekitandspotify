<?php

require dirname(__FILE__) . '/bootstrap.php';

$session = new SpotifyWebAPI\Session(
        SPOTIFY_CLIENT_ID,
SPOTIFY_CLIENT_SECRET,
SPOTIFY_CLIENT_CALLBACK
);

$api = new SpotifyWebAPI\SpotifyWebAPI();

if (isset($_GET['signout']) && $_GET['signout'] == 1) {
    $_SESSION["accessToken"] = $_SESSION["refreshToken"] = '';
    ?>
    <html>
    <head><title>Authentication</title></head>
    <body>
    <script>
        setTimeout(function () {
            window.opener.location.reload();
            window.location.href = 'https://www.spotify.com/logout/';
        }, 1000);
    </script>
    </body>
    </html>
    <?php
} elseif (isset($_GET['code'])) {
    $session->requestAccessToken($_GET['code']);
    $api->setAccessToken($session->getAccessToken());
    $accessToken = $session->getAccessToken();
    $refreshToken = $session->getRefreshToken();

    $_SESSION["accessToken"] = $accessToken;
    $_SESSION["refreshToken"] = $refreshToken;

    ?>
    <html>
    <head><title>Authentication</title></head>
    <body>
    <script>
        setTimeout(function () {
            window.opener.location.reload();
            window.close();
        }, 1000);
    </script>
    </body>
    </html>
    <?php

} else {
    $options = [
        'scope' => [
            "streaming",
            "user-read-email",
            "user-read-private",
        ],
    ];

    header('Location: ' . $session->getAuthorizeUrl($options));
    die();
}