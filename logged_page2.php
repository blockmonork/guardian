<?php
// ------- Guardian implementation - put those lines in every page you want to monitor --------
if (isset($guardian)) {
    // via require_page
    $guardian->monitore();
} else {
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
    <title>This is a logged page2!</title>
    <!-- bootstrap -->
    <link rel="stylesheet" href="<?php echo $d . $files['bootstrap_css']; ?>" type="text/css">
    <!-- bootstrap -->
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>This is a logged page 2!</h1>
                <p>
                    <a href="hello.php">back to Hello! page</a>
                </p>
                <hr>
                <form action="logged_page2.php" method="post">
                    <p>form action=self (get posts via $_POST )</p>
                    <input type="text" name="txt" size="30" placeholder="will be sanitized as text">
                    <br>
                    <input type="text" name="num" size="30" placeholder="will be sanitized as number">
                    <br>
                    <input type="text" name="dat" size="30" placeholder="will be sanitized as date">
                    <br>
                    <input type="submit" value="submit">
                </form>
                <?php
                if ($_POST) {
                    echo '<b>original: </b>';
                    foreach ($_POST as $k => $v) {
                        echo $_POST[$k] . ', ';
                    }
                    $txt = $guardian->sanitize_as($_POST['txt']);
                    $num = $guardian->sanitize_as($_POST['num'], 'number');
                    $dat = $guardian->sanitize_as($_POST['dat'], 'date');
                    echo "<p>posts. txt: $txt | int: $num | data: $dat</p>";
                } else {
                    echo '<i>no post</i>';
                    $teste = 'nome com <tag></tag> e \contra barra';
                    function funzinha($txt)
                    {
                        $teste = trim($txt);
                        $fr = [
                            '<' => '&lt;',
                            '>' => '&gt;',
                            '\\' => '&bsol;',
                            '"' => '&quot;',
                            "'" => '&#039;',
                        ];
                        foreach ($fr as $f => $r) {
                            $teste = str_replace($f, $r, $teste);
                        }
                        return $teste;
                    }
                    echo " resultado: " . funzinha($teste);
                }
                ?>
                <hr>
                <p>
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