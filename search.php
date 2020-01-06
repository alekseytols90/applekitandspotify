<?php

require dirname(__FILE__) . '/bootstrap.php';


$search_term = isset($_GET['song']) ? trim($_GET['song']) : '';
$search_result = $appleMusicKit->search($search_term);
// see complete response here
// echo '<pre>',print_r($search_result,true),'</pre>';exit;

require BASE_DIR . '/includes/header.php';
?>


    <div class="card">
        <div class="card-header">
            Search Results
        </div>
        <div class="card-body">

            <?php
            if (!empty($search_result->results->songs->data)):
                ?>
                <ul class="list-group">
                    <?php foreach ($search_result->results->songs->data as $song): ?>
                        <?php //echo '<pre>',print_r($song,true),'</pre>'; ?>
                        <li class="list-group-item">
                            <img src="<?php echo str_replace(['{w}','{h}'],[100,100],$song->attributes->artwork->url);  ?>" alt="" class="pull-right"/><br>
                            <?php
                            echo 'Song: ' . $song->attributes->name . '<br>';
                            echo 'Artist: ' . $song->attributes->artistName . '<br>';
                            echo 'Album: ' . $song->attributes->albumName . '<br>';
                            echo 'ISRC: ' . $song->attributes->isrc . '<br>';
                            echo "<a href='".SITE_URL."/play-music.php?type=apple&song={$song->attributes->isrc}' target='_blank'>Play With Apple</a> | <a href='".SITE_URL."/play-music.php?type=spotify&song={$song->attributes->isrc}' target='_blank'>Play with Spotify</a><br>";
                            ?>
                            <hr>
                            Sample:<br>
                            <audio controls="controls">
                                <source src="<?php echo $song->attributes->previews[0]->url ?>" type="audio/mpeg" />
                                Your browser does not support the audio element.
                            </audio>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php
            else:
                ?>
            No song found by title: <?php echo $search_term ?>
            <?php
            endif;
            ?>
        </div>
    </div>

<?php
require BASE_DIR . '/includes/footer.php';