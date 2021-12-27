<?php
// ------- Guardian implementation - put those lines in every page you want to monitor --------
if ( isset($guardian) ){
    // via require_page
    $guardian->monitore();
}else{
    // via redirect
    require_once('Guardian.php');
    $guardian = new Core\Guardian();
    $guardian->monitore()->print_header();
}
// base_dir to include files
$d = $guardian->get('base_dir') . 'assets/';
$files = [
    'bootstrap_css' => 'bs45.css', 
    'jquery' => 'jq35.js', 
    'popper' => 'pp.js', 
    'bootstrap_js' => 'bs45.js', 
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello! This is a logged page!</title>
    <!-- bootstrap -->
    <link rel="stylesheet" href="<?php echo $d . $files['bootstrap_css']; ?>" type="text/css">
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
                    &nbsp;
                    <a href="logged_page2.php">go to page 2</a>
                </p>
                <?php
                
                echo '<h2>teste encode/decode printable string</h2>';
                $x = 'Teste Encode/Decode printable string';
                echo "encode $x:<br> ";

                $y = $guardian->encode_printable_string($x);
                echo $y;

                echo "<br>decode $y: <br>";
                $z = $guardian->decode_printable_string($y);
                echo "$z<br>";
                
                if (isset($_GET['name']) && !empty($_GET['name'])) {
                    $name = preg_replace('/W/', '', trim(substr($_GET['name'], 0, 20)));
                    echo '<p>' . $name . '</p>';
                }
                include('test_page2.php');
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div id="ajax_response"></div>
                    </div>
                </div>
                <hr>
                <p>
                    <a href="javascript:ajaxTest();" class="btn btn-primary">test ajax</a>
                    &nbsp;
                    <a href="index.php?logout=1" class="btn btn-outline-primary">Logout</a>
                    
                </p>
            </div>
        </div>
    </div>




    <!-- bootstrap -->
    <script type="text/JavaScript" src="<?php echo $d . $files['jquery']; ?>"></script>
    <script type="text/JavaScript" src="<?php echo $d . $files['popper']; ?>"></script>
    <script type="text/JavaScript" src="<?php echo $d . $files['bootstrap_js']; ?>"></script>
    <!-- bootstrap -->

    <script>
        function ajaxTest() {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("ajax_response").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "test_page2.php?ajax=1", true);
            xhttp.send();
            
        }
    </script>
</body>

</html>