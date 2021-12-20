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
                    <input type="text" name="fake_pass" id="fake_pass" size="30" placeholder="fake pass">
                    <br>
                    <input type="text" name="hid_pass" id="hid_pass" size="30" placeholder="get fake_pass">
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
        var fkP = {
            el_target:'',
            el_fake:'',
            chars:[],
            symbol:'*',
            _g:function(i){
                return document.getElementById(i);
            },
            setup : function(el_fake, el_target) {
                this.el_target = this._g(el_target);
                this.el_fake = this._g(el_fake);
                // hide the target element
                //this.el_target.style.display = 'none';
                this.el_fake.addEventListener('keydown', this.fk);
                this.el_fake.addEventListener('keyup', this.fk);
                return this;
            },
            fk:function(){
                var f = fkP;
                var txt = f.el_fake.value;
                var temp = '';
                var len = txt.length;
                if (len < f.chars.length && f.chars.length > 0){
                    f.chars.pop();
                }
                for ( let i = 0; i < len; i++ ) {
                    let c = txt.charAt(i);
                    if ( f.chars[i] == undefined && c != f.symbol) {
                        f.chars.push(c);                    
                    }else if ( c != f.chars[i] && c != f.symbol ){
                        f.chars[i] = c;
                    }else{
                        const index = f.chars.indexOf(c);
                        if (index > -1) {
                            f.chars.splice(index, 1);
                        }
                    }
                    temp += f.symbol;
                }
                f.el_target.value = f.chars.join('');
                f.el_fake.value = temp;
            },
        };
        (function(){
            fkP.setup('fake_pass', 'hid_pass');
        })();
    </script>
</body>

</html>