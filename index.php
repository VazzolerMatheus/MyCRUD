<?php
session_start();
require_once 'pdo.php';
require_once 'util.php';

$stmt = $pdo->prepare('SELECT profile_id, user_id, first_name, last_name , headline FROM Profile');
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Matheus Kitamukai Vazzoler</title>
  <?php include 'bootstrap.php' ?>
</head>

<body>
<div class="container">
<h1>Matheus Vazzoler's Resume Registry</h1>

<?php flashMessage(); ?>

<?php

if (isset($_SESSION['email'])){
  echo(
    '<a href="logout.php">Logout</a>'
  );

} else {
  echo(
    '<p><a href="login.php">Please log in</a></p>
  ');
}

echo('<table border="1">');
echo('
<tr>
<th>Name</th>
<th>Headline</th>
');
//If the user is logged in, we will be able to see the Action row
  if(isset($_SESSION['email'])){
    echo('<th>Action</th>');
  }
echo('</tr>');


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  echo('<tr>');
  echo('<td> <a href="view.php?id='.htmlentities($row['profile_id']).'">'.htmlentities($row['first_name']).' '.htmlentities($row['last_name']).'</a> </td>');
  echo('<td>'.htmlentities($row['headline']).'</td>');

  if(isset($_SESSION['email'])){
    echo('<td> <a href="edit.php?id='.htmlentities($row['profile_id']).'">Edit</a>');
    echo(' ');
    echo('<a href="delete.php?id='.htmlentities($row['profile_id']).'">Delete</a></td>');

  }
}

echo("</tr>\n");
echo("</table>\n");?>

<?php if(isset($_SESSION['email'])){ print('<p><a href="add.php">Add New Entry</a></p>'); }?>
</div>
</body>
