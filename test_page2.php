<?php

if (isset($_GET['ajax'])) {
    echo "<p><b>Some ajax requests</b></p>";
    exit;
}
echo "<p>This is the test page2 included inside hello.php</p>";
$test1 = $test2 = '';
if (isset($_POST)) {
    $posts = $guardian->get_post(['test1', 'test2']);
    foreach ( $posts as $key => $value ) {
        echo " $key = $value <br> ";
    }
} else {
    echo "<p>No post data</p>";
}
?>
<hr>
<form action="index.php" method="post">
    <p>form action=index.php (get posts via guardian->get_post($name) || get_post([name1, nameN])</p>
    <input type="text" name="test1" placeholder="insert value">
    <br>
    <input type="text" name="test2" placeholder="insert value">
    <br>
    <input type="submit" value="submit">
</form>