<?php

require dirname(__FILE__) . '/bootstrap.php';

require BASE_DIR . '/includes/header.php';
?>
    <div class="card">
        <div class="card-header">
            Search Song
        </div>
        <div class="card-body">


            <form class="form-group" action="<?php echo SITE_URL . '/search.php' ?>" method="GET">
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group  mb-2">
                            <label for="song" class="sr-only">Song</label>
                            <input type="text" class="form-control input-lg" id="song" name="song"
                                   placeholder="Search song title here.." value="">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary mb-2">Search</button>
                    </div>
                </div>
            </form>

        </div>
    </div>
<?php
require BASE_DIR . '/includes/footer.php';