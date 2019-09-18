<?php
session_start();
require_once 'pdo.php';
require_once 'util.php';

if(!isset($_SESSION['email'])){
  die ("ACCESS DENIED");
}

if (isset($_POST['cancel'])){
  header('Location: index.php');
  return;
}


if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) &&  isset($_POST['summary']) && isset($_POST['id'])) {

  $msg = validateProfile();
  if(is_string($msg) ){
    $_SESSION['failmessage'] = $msg;
    header('Location: edit.php?id='.$_POST['id']);
    return;
  }

  $msg2 = validatePos();
  if(is_string($msg2) ){
    $_SESSION['failmessage'] = $msg2;
    header('Location: edit.php?id='.$_POST['id']);
    return;
  }

  $msg3 = validateEdu();
  if(is_string($msg3) ){
    $_SESSION['failmessage'] = $msg3;
    header('Location: edit.php?id='.$_POST['id']);
    return;
  }

  $stmt = $pdo->prepare('UPDATE Profile
                        SET user_id = :uid, first_name = :fn, last_name = :ln, email = :em, headline = :hl, summary = :sm
                            WHERE profile_id = :pid');
    $stmt->execute(array(
      ':pid' => $_POST['id'],
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':hl' => $_POST['headline'],
      ':sm' => $_POST['summary']
    ));


  // POSITION Delete then ADD
    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_POST['id']));
    $rank = 1;
    for($i=1; $i <= 9; $i++){

      if (!isset($_POST['year'.$i]) ) continue;
      if (!isset($_POST['desc'.$i]) ) continue;

      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES (:pif, :rk, :yr, :dp)');
      $stmt->execute(array(
        ':pif' => $_POST['id'],
        ':rk' => $rank,
        ':yr' => $year,
        ':dp' => $desc
      ));
      $rank++;
      }

  // EDUCATION Delete then ADD
    $stmt = $pdo->prepare('DELETE FROM Education WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_POST['id']));


    $rank = 1;
    for($i=1; $i <= 9; $i++){
      if (!isset($_POST['edu_year'.$i]) ) continue;
      if (!isset($_POST['edu_school'.$i]) ) continue;

      $yearedu = $_POST['edu_year'.$i];
      $school = $_POST['edu_school'.$i];

      $stmt = $pdo->prepare('SELECT institution_id from Institution WHERE name = :name');
      $stmt->execute(array(':name' => $school));
      $schoolid = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($schoolid === false){
        $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:names)');
        $stmt->execute(array(':names' => $school));
        $schoolid = $pdo->lastInsertId();
      } else {
        $schoolid = $schoolid['institution_id'];
      }

      $stmt = $pdo->prepare('INSERT INTO Education (profile_id, institution_id, rank, year) VALUES (:pif, :iid, :rk, :yr)');
      $stmt->execute(array(
        ':pif' => $_POST['id'],
        ':iid' => $schoolid,
        ':rk' => $rankedu,
        ':yr' => $yearedu
      ));
      $rankedu++;
    }


    $_SESSION['successmessage'] = 'Profile updated';
    header('Location: index.php');
    return;

}


$view = $pdo->prepare('SELECT * FROM Profile WHERE profile_id = :pid');
$view->execute(array(':pid' => $_GET['id'] ));
$row = $view->fetch(PDO::FETCH_ASSOC);
$_REQUEST['profile_id'] = $row['profile_id'];

$view_prof = $pdo->prepare('SELECT * FROM Position WHERE profile_id = :pid');
$view_prof->execute(array(':pid' => $_GET['id'] ));
$possitionRow = $view_prof->fetchAll();

$edu = $pdo->prepare('SELECT  Profile.profile_id, Institution.name, Education.year, Education.rank
                     FROM Education inner join Institution INNER JOIN Profile
                     ON Profile.profile_id = Education.profile_id AND Institution.institution_id = Education.institution_id
                     WHERE Profile.profile_id = :pid
                     ORDER BY Education.rank');
$edu->execute(array(':pid' => $_GET['id']));
$eduRow = $edu->fetchAll();

// print_r($possitionRow);
print_r($eduRow);
echo('the size is'.sizeof($eduRow) );



if ( $row === false ) {
    $_SESSION['failmessage'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
}



?>



<!DOCTYPE html>
<html>
<head>
<title>Matheus Kitamukai Vazzoler</title>
<?php include 'bootstrap.php'; ?>

</head>
<body>
<div class="container">
<h1>Editing Profile for <?php echo($_SESSION['email']); ?></h1>

  <!-- FLASH message -->
<?php
if(isset($_SESSION['failmessage'])){
  echo('<p style="color:red">'.$_SESSION['failmessage'].'</p>');
  unset($_SESSION['failmessage']);
}
 ?>
<form method="post">
<p>First Name:
<input type="text" name="first_name" size="60" value="<?php echo (htmlentities($row['first_name']) ); ?>"/></p>
<p>Last Name:
<input type="text" name="last_name" size="60" value="<?php echo (htmlentities($row['last_name']) ); ?>"/></p>
<p>Email:
<input type="text" name="email" size="30" value="<?php echo (htmlentities($row['email']) ); ?>"/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80" value="<?php echo (htmlentities($row['headline']) ); ?>"/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80"><?php echo (htmlentities($row['summary']) ); ?></textarea>

<p>
Education: <input type="submit" id="addEdu" value="+">
<div id="edu_fields">
  <?php
          if(sizeof($eduRow) >= 1){
            for ($j=0; $j < sizeof($eduRow); $j++){
              echo '<div id="edu'.($j+1).'">';
              echo '<p>';
              echo '"Year"';
              echo ('<input type="text" name="year'.($j+1).'" value ="'.htmlentities( $eduRow[$j]['year'] ).'">');
              echo ('<input type="button" value ="-" onclick="$('."'".'#edu'.($j+1)."'".').remove();return false;"> </p>');
              echo ('<p>School: <input type="text" size="80" name="edu_school'.($j+1).'" class="school" value="'.htmlentities( $eduRow[$j]['name']).'" ></p>');
              echo '</div>';
            }
          }
   ?>
</div>
</p>

<p>
Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">

  <!-- //Code to load previous Positions -->
  <?php

      if(sizeof($possitionRow) > 1){
        for ($j=0; $j < sizeof($possitionRow); $j++){
          echo '<div id="position'.($j+1).'">';
          echo '<p>';
          echo '"Year"';
          echo ('<input type="text" name="year'.($j+1).'" value ="'.htmlentities( $possitionRow[$j]['year'] ).'">');
          echo ('<input type="button" value ="-" onclick="$('."'".'#position'.($j+1)."'".').remove();return false;"> </p>');
          echo ('<textarea name="desc'.($j+1).'" rows="8" cols="80">');
          echo (htmlentities( $possitionRow[$j]['description'] ));
          echo ('</textarea>');
          echo '</div>';
        }
      }
   ?>

</div>
</p>
<p>
<input type="hidden" name="id" value="<?php echo ($_GET['id']); ?>"/>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>

<script type="text/javascript">
  countPos = <?php echo (sizeof($possitionRow)) ?>;
  countEdu = <?php echo (sizeof($eduRow)) ?>;
  $(document).ready(function(){
      window.console && console.log('Document ready called');
      $('#addPos').click(function(event){
          // http://api.jquery.com/event.preventdefault/
          event.preventDefault();
          if ( countPos >= 9 ) {
              alert("Maximum of nine position entries exceeded");
              return;
          }
          countPos++;
          window.console && console.log("Adding position "+countPos);
          $('#position_fields').append(
              '<div id="position'+countPos+'"> \
              <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
              <input type="button" value="-" \
              onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
              <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
              </div>');
      });
  });

  // ADD Education DOM
  $(document).ready(function(){
    $('#addEdu').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding Education "+countEdu);
        $('#edu_fields').append(
            '<div id="edu'+countEdu+'"> \
            <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#edu'+countEdu+'\').remove();return false;"></p> \
            <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" ></p>\
            </div>');

      $('.school').autocomplete({ source: "school.php" });
    });
  });
</script>


</div>
</body>
</html>
