<?php
session_start();
require_once 'pdo.php';

//Condition CANCEL button
if (isset($_POST['cancel'])){
  header('Location: index.php');
  return;
}


$salt = 'XyZzy12*_';

if (isset($_POST['email']) && isset($_POST['pass'])){

  //User password input + the Salt
  $check = hash('md5', $salt.$_POST['pass']);

  $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');

  $stmt->execute(array(
    ':em' => $_POST['email'],
    ':pw' => $check
  ));

  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if($row !== false){
    $_SESSION['email'] = $row['name'];
    $_SESSION['user_id'] = $row['user_id'];
    header('Location: index.php');

  } else {
    $_SESSION['failmessage'] = 'Incorrect password';
    header('Location: login.php');
    return;
    }
}


 ?>

<!DOCTYPE html>
<html>
<head>
<title>Matheus Kitamukai Vazzoler</title>
<!-- bootstrap.php - this is HTML -->

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet"
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7"
    crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet"
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"
    integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r"
    crossorigin="anonymous">

</head>
<body>
<div class="container">

<h1>Please Log in</h1>

  <!-- FLASH message -->
<?php

if(isset($_SESSION['failmessage'])){
  echo('<p style="color:red">'.$_SESSION['failmessage'].'</p>');
  unset($_SESSION['failmessage']);
}
 ?>

<form method="POST" action="login.php">
<label for="email">Email</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>

<p>For a password hint, view source and find an account and password hint in the HTML comments.</p>

<script>
function doValidate() {
    console.log('Validating...');
    try {
        addr = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}
</script>
</div>
</body>
