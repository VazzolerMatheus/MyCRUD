<?php
session_start();
require_once 'pdo.php';

if(!isset($_SESSION['email'])){
  die ("Not logged in");
}

if (isset($_POST['cancel'])){
  header('Location: index.php');
  return;
}


if ( ! isset($_GET['id']) ) {
  $_SESSION['failmessage'] = "Missing user_id";
  header('Location: index.php');
  return;
} else {

$stmt = $pdo->prepare('SELECT first_name, last_name, email, headline, summary  FROM Profile WHERE profile_id = :pid');
$stmt->execute(array(':pid' => $_GET['id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $row === false ) {
    $_SESSION['failmessage'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
 }

 $profiles = $pdo->prepare('SELECT position_id, rank, year, description FROM Position
                            WHERE profile_id = :pid
                            ORDER BY rank');
 $profiles->execute(array(':pid' => $_GET['id']));
 $row2 = $profiles->fetchAll();

 $edu = $pdo->prepare('SELECT  Profile.profile_id, Institution.name, Education.year, Education.rank
                      FROM Education inner join Institution INNER JOIN Profile
                      ON Profile.profile_id = Education.profile_id AND Institution.institution_id = Education.institution_id
                      WHERE Profile.profile_id = :pid
                      ORDER BY Education.rank');
 $edu->execute(array(':pid' => $_GET['id']));
 $eduRow = $edu->fetchAll();

 // echo'<pre>';
 // print_r($eduRow);
 // echo'</pre>';
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
  <h1>Profile information</h1>

<p>First name: <b> <?php echo(htmlentities($row['first_name'])); ?> </b></p>
<p>Last name: <b> <?php echo(htmlentities($row['last_name'])); ?> </b></p>
<p>Headline: <b> <?php echo(htmlentities($row['email'])); ?> </b></p>
<p>Email: <b> <?php echo(htmlentities($row['headline'])); ?> </b></p>
<p>Summary: <b> <?php echo(htmlentities($row['summary'])); ?> </b></p>

<?php

// Education View
if(!$eduRow == false){
  echo '<p> Education: </p>';
}
foreach ($eduRow as $key) {
echo ('<p>'.htmlentities($key['year']).':'.htmlentities($key['name']).'</p>');
}


// Position View
if(!$row2 == false){
  echo '<p> Position: </p>';
}
foreach ($row2 as $key) {
echo ('<p>'.htmlentities($key['year']).':'.htmlentities($key['description']).'</p>');
}

 ?>


<a href="index.php">Done</a>

</div>
</body>
</html>
