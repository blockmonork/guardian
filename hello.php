<?php
// ------- Guardian implementation - put those lines in every page you want to monitor --------
use Core\Guardian;

require('Guardian.php');
if (!Guardian::is_logged_in()) {
	Guardian::redirect('index.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
         <title>Hello! This is a logged page!</title>
    <!-- bootstrap -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <!-- bootstrap -->
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>Hello World!</h1>
                <p>This is a logged page!</p>
                <p>
                    <a href="hello.php?name=test">just a test</a>
                </p>
                <?php
                    if(isset($_GET['name']) && !empty($_GET['name'])){
                        $name = preg_replace('/W/', '', trim(substr($_GET['name'], 0, 20)));
                        echo '<p>' . $name. '</p>';
                    }
                ?>
                <p>
                    <a href="index.php?logout=1">Logout</a>
                </p>
            </div>
        </div>
    </div>

    

    
<!-- bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<!-- bootstrap -->
</body>
</html>