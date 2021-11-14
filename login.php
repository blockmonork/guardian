<?php header('Content-type:text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <!-- bootstrap -->

    <!-- bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    <!-- bootstrap -->  
    <style>
        .containerLg {
            margin-top: 100px;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            background-color: #efefef;
        }
    </style>  
</head>

<body onload='document.querySelector("#user").focus();'>



    <div class="row">
        <div class="offset-4 col-md-3">
            <div class="form-group">
                <div class="containerLg">
                    <form action="index.php" method="post" onsubmit="return checkLogin();">
                        <label for="user">user</label>
                        <input type="text" id="user" name="user" class="form-control" maxlength="20" required autocomplete="username" value="">
                        <label for="pass">pass</label>
                        <input type="password" id="pass" name="pass" class="form-control" maxlength="20" required autocomplete="new-password" value="">
                        <input type="submit" value="login" class="mt-4 btn btn-primary form-control">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <p id="result"></p>

    <script>
        // copilot trainning
        // create javascript function to strong check values for user and password
        function checkLogin() {
            var user = document.getElementById('user').value;
            var pass = document.getElementById('pass').value;
            if (user.length < 4 || user.length > 20) {
                alert('user must be between 5 and 20 characters');
                return false;
            }
            if (pass.length < 8 || pass.length > 20) {
                alert('pass must be between 5 and 20 characters');
                return false;
            }
            // checkPass before return 
            return checkPass();
        }
        // function to analyze if a password has complexity
        function checkPass() {
            var pass = document.getElementById('pass').value;
            var upper = /[A-Z]/;
            var lower = /[a-z]/;
            var number = /[0-9]/;
            var special = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/;
            if (pass.length < 8) {
                alert('password must be at least 8 characters');
                return false;
            }
            if (!upper.test(pass)) {
                alert('password must contain at least one uppercase letter');
                return false;
            }
            if (!lower.test(pass)) {
                alert('password must contain at least one lowercase letter');
                return false;
            }
            if (!number.test(pass)) {
                alert('password must contain at least one number');
                return false;
            }
            if (!special.test(pass)) {
                alert('password must contain at least one special character');
                return false;
            }
            return true;
        }
    </script>


</body>

</html>