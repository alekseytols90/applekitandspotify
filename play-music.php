<?php

require dirname(__FILE__) . '/bootstrap.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$song = isset($_GET['song']) ? trim($_GET['song']) : '';
$song_uri = '';

//var_dump($_SESSION["accessToken"]);exit;

$spotify_auth_code = '';
if($type == 'spotify' && !empty($_SESSION["accessToken"])) {
    $api = new SpotifyWebAPI\SpotifyWebAPI();
    $api->setAccessToken($_SESSION["accessToken"]);
    try {
        $me =$api->me();
    } catch (\Exception $e) {
        echo $e->getMessage();
    }



    if(!empty($me)) {
        $spotify_auth_code = $_SESSION["accessToken"];
        $song_info = $api->search("isrc:{$song}", 'track');
        //print_r($song_info);exit;
        $song_uri = $song_info->tracks->items[0]->uri;

    }


}

require BASE_DIR . '/includes/header.php';
?>

<?php if ($type == 'apple'): ?>
    <div class="card">
        <div class="card-header">
            Apple Music
        </div>
        <div class="card-body">

            <a href="#" id="apple-sign-in" class="d-none">Sign in to Apple Music</a>
            <a href="#" id="apple-sign-out" class="d-none">Sign out of Apple Music</a>
            <hr>

            <div class="d-none" id="message"></div>
            <div class="d-none" id="player-controls">
                <a href="#" class="" id="music-play">Play</a>
                <a href="#" class="d-none" id="music-pause">Pause</a>
                <a href="#" class="" id="music-stop">Stop</a>

                <time id="apple-music-current-playback-duration"></time>
                <time id="apple-music-current-playback-time"></time>
                <div id="progress-parent" style="width: 100%">
                <div id="apple-music-current-playback-progress"></div>
                </div>
            </div>

        </div>
    </div>
<?php elseif ($type == 'spotify'): ?>
    <div class="card">
        <div class="card-header">
            Spotify
        </div>
        <div class="card-body">

            <a href="#" id="spotify-sign-in" class="d-none">Sign in to Spotify</a>
            <a href="#" id="spotify-sign-out" class="d-none">Sign out of Spotify Music</a>
            <hr>

            <div class="d-none" id="spotify-message"></div>
            <div class="d-none" id="spotify-player-controls">
                <a href="#" class="" id="spotify-music-play">Play</a>
                <a href="#" class="d-none" id="spotify-music-pause">Pause</a>  |
                <a href="#" class="" id="spotify-music-forward-seconds">Forward 5 seconds</a>  |
                <a href="#" class="" id="spotify-music-backward-seconds">Rewind 5 seconds</a>

<hr>
                <div href="#" class="" id="spotify-music-status"></div>
                <div href="#" class="" id="spotify-music-current-duration"></div>
            </div>

        </div>
    </div>
<?php else: ?>
    <div class="card">
    <div class="card-header">
        Play Music
    </div>
    <div class="card-body">
        Please go back and select a valid player.
    </div>
<?php endif; ?>

<?php
function footer_content(\App\AppleMusicKit $appleMusicKit)
{
    global $type, $spotify_auth_code, $song_uri;

    if ($type == 'apple') {
        ?>
        <script src="https://js-cdn.music.apple.com/musickit/v1/musickit.js"></script>
        <script>

        $(document).ready(function() {
              $( function() {
                var progressbar = $('#apple-music-current-playback-progress');

                function progress() {
                   
                  var val = $('#apple-music-current-playback-progress').text();
                  progressbar.css('height', '30px');
                  progressbar.css('background', '#ff0000');
                  progressbar.css('width', val);

                  if ( val.replace('%','') < 99 ) {
                    setTimeout( progress, 80 );
                  }
                }

                setTimeout( progress, 2000 );
              } );
        });


            document.addEventListener('musickitloaded', function () {
                // MusicKit global is now defined
                MusicKit.configure({
                    developerToken: '<?php echo $appleMusicKit->getToken(); ?>',
                    app: {
                        name: 'My Music App',
                        build: '1978.4.1'
                    },
                    declarativeMarkup: true
                });

                let music = MusicKit.getInstance();

                $('#apple-sign-in').removeClass('d-none').show();
                $('#apple-sign-in').on('click', function () {
                    // This ensures user authorization before calling play():
                    music.authorize().then(function () {
                        $('#apple-sign-in').hide();
                        $('#apple-sign-out').removeClass('d-none').show().on('click', function () {
                            music.unauthorize().then(function () {
                                $('#apple-sign-out, #player-controls').hide();
                                $('#apple-sign-in').removeClass('d-none').show();
                            });
                        });
                        music.api.songs({filter: {isrc: '<?php echo $_GET['song'] ?>'}}).then(function (songs) {
                            music.setQueue({url: songs[0].attributes.url}).then(function (queue) {
                                // Queue is instantiated and set on music player.
                                // Playback Controls
                                $('#player-controls').removeClass('d-none');
                                $('#music-play').on('click', function () {
                                    music.play();
                                    $(this).hide();
                                    $('#music-pause').removeClass('d-none').show();
                                });
                                $('#music-pause').on('click', function () {
                                    music.pause();
                                    $(this).hide();
                                    $('#music-play').show();
                                });
                                $('#music-stop').on('click', function () {
                                    music.stop();
                                });
                            });
                        });
                    }, function () {
                        //console.log('Could not log in');
                        $('#message').text('You need to subscribe to Apple Music to play music.').removeClass('d-none').show();
                    });

                    music.addEventListener('playbackProgressDidChange', function (event) {
                        // this event will give you progress status of the playing song
                        //console.log(event);
                    });
                }).trigger('click');
            });
        </script>
    <?php
    } elseif ($type == 'spotify') {
    ?>
      <script src="https://sdk.scdn.co/spotify-player.js"></script>
      <script>
      window.onSpotifyWebPlaybackSDKReady = () => {
                    console.log('ere');
                  const token = '<?php echo $spotify_auth_code; ?>';
                  console.log(token);
                  const player = new Spotify.Player({
                    name: 'Web Playback SDK Quick Start Player',
                    getOAuthToken: cb => { cb(token); }
                  });

                  // Error handling
                  player.addListener('initialization_error', ({ message }) => { console.error(message); });
                  player.addListener('authentication_error', ({ message }) => { console.error(message); });
                  player.addListener('account_error', ({ message }) => { console.error(message); });
                  player.addListener('playback_error', ({ message }) => { console.error(message); });

                  // Playback status updates
                  player.addListener('player_state_changed', state => { console.log(state); });

                  // Ready
                  player.addListener('ready', ({ device_id }) => {
                    console.log('Ready with Device ID', device_id);
                  });

                  // Not Ready
                  player.addListener('not_ready', ({ device_id }) => {
                    console.log('Device ID has gone offline', device_id);
                  });

                  // Connect to the player!
                  player.connect();

                  player.addListener('ready', ({ device_id }) => {
                      console.log('The Web Playback SDK is ready to play music!');
                      console.log('Device ID', device_id);




                      const play = ({
                          spotify_uri,
                          playerInstance: {
                            _options: {
                              getOAuthToken,
                              id
                            }
                          }
                      }) => {
                          getOAuthToken(access_token => {
                            fetch(`https://api.spotify.com/v1/me/player/play?device_id=${id}`, {
                              method: 'PUT',
                              body: JSON.stringify({ uris: [spotify_uri] }),
                              headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${access_token}`
                              },
                            });
                          });
                        };


                      $('#spotify-player-controls').removeClass('d-none').show();

                      var is_music_played = false;
                      $('#spotify-music-play').on('click', function() {
                          $('#spotify-music-pause').removeClass('d-none').show();
                          $(this).hide();
                          if(is_music_played == false) {
                              play({
                                  playerInstance: player,
                                  spotify_uri: '<?php echo $song_uri ?>',
                                });
                              is_music_played = true;
                          } else {
                              player.togglePlay().then(() => {
                              console.log('Toggled playback!');
                            });
                          }






                      });

                      $('#spotify-music-pause').on('click', function() {
                          player.togglePlay().then(() => {
                              $(this).hide();
                              $('#spotify-music-play').show();
                              console.log('Toggled playback!');

                            });
                      });

                      $('#spotify-music-forward-seconds').on('click', function() {


                          player.getCurrentState().then(state => {
                                  if (!state) {
                                    console.error('User is not playing music through the Web Playback SDK');
                                    return;
                                  }

                                  player.seek(parseInt(state.position) + 5 * 1000).then(() => {
                                      console.log('forward position!');
                                    });

                            });

                      });

                      $('#spotify-music-backward-seconds').on('click', function() {


                          player.getCurrentState().then(state => {
                                  if (!state) {
                                    console.error('User is not playing music through the Web Playback SDK');
                                    return;
                                  }

                                  player.seek(parseInt(state.position) - 5 * 1000).then(() => {
                                      console.log('forward position!');
                                    });

                            });

                      });

                 });

                  player.addListener('player_state_changed', ({
                          position,
                          duration,
                          track_window: { current_track }
                        }) => {
                          console.log('Currently Playing', current_track);
                          console.log('Position in Song', position);
                          console.log('Duration of Song', duration);

                          String.prototype.toHHMMSS = function () {
                                var sec_num = parseInt(this, 10); // don't forget the second param
                                var hours   = Math.floor(sec_num / 3600);
                                var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
                                var seconds = sec_num - (hours * 3600) - (minutes * 60);

                                if (hours   < 10) {hours   = "0"+hours;}
                                if (minutes < 10) {minutes = "0"+minutes;}
                                if (seconds < 10) {seconds = "0"+seconds;}

                                var final_time = hours+':'+minutes+':'+seconds;
                                if(hours === '00') final_time = minutes+':'+seconds;
                                return final_time;
                            }

                          $('#spotify-music-status').html(
                              'Artist: '+ current_track.artists[0].name + '<br>' +
                              'Duration: ' + (duration / 1000).toString().toHHMMSS() + '<br>'
                              );



                          var update_timer = function() {
                              player.getCurrentState().then(state => {
                                  if (!state) {
                                    console.error('User is not playing music through the Web Playback SDK');
                                    return;
                                  }

                                  console.log('setting timer');
                                  $('#spotify-music-current-duration').html(
                                     'Position: ' + (state.position / 1000).toString().toHHMMSS()
                                    );

                                  if(state.paused === false) {
                                      setTimeout(update_timer, 1000);

                                  }

                            });
                          };

                          update_timer();

                        });


                };

        $(function() {
            var is_authenticated = '<?php echo !empty($spotify_auth_code) ? 'true':'false'; ?>';

            if(is_authenticated == 'false') {
               $('#spotify-sign-in').removeClass('d-none').show().on('click', function() {
                   PopupCenter('<?php echo SITE_URL ?>/spotify-auth.php', 'Authentication', 500, 540);
               })
               .trigger('click');
            } else {
                // sign out link
                $('#spotify-sign-out').removeClass('d-none').show().on('click', function() {
                   PopupCenter('<?php echo SITE_URL ?>/spotify-auth.php?signout=1', 'Authentication', 500, 540);
               });
            }

        });

        function PopupCenter(url, title, w, h) {
            // Fixes dual-screen position                         Most browsers      Firefox
            var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
            var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

            var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
            var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

            var systemZoom = width / window.screen.availWidth;
            var left = (width - w) / 2 / systemZoom + dualScreenLeft
            var top = (height - h) / 2 / systemZoom + dualScreenTop
            var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w / systemZoom + ', height=' + h / systemZoom + ', top=' + top + ', left=' + left);

            // Puts focus on the newWindow
            if (window.focus) newWindow.focus();
        }
        </script>
        <?php
    }
}

require BASE_DIR . '/includes/footer.php';