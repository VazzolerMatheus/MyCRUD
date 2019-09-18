<?php

session_start();
require_once 'pdo.php';
require_once 'util.php';

//not logged in
if(!isset($_SESSION['email']) ){
  die ("ACCESS DENIED");
}

//cancel button is clicked
if (isset($_POST['cancel']) ){
  header('Location: index.php');
  return;
}

if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) & isset($_POST['summary']) ){


// VALIDATION OF DATA BEFORE RUNNING ANYTHING
    $msg = validateProfile();
    if(is_string($msg) ){
      $_SESSION['failmessage'] = $msg;
      header('Location: add.php');
      return;
    }

    $msg2 = validatePos();
    if(is_string($msg2) ){
      $_SESSION['failmessage'] = $msg2;
      header('Location: add.php');
      return;
    }

    $msg3 = validateEdu();
    if(is_string($msg3) ){
      $_SESSION['failmessage'] = $msg3;
      header('Location: add.php');
      return;
    }

    $stmt = $pdo->prepare('INSERT INTO Profile (user_id, first_name, last_name, email, headline, summary) VALUES (:uid, :fn, :ln, :em, :hl, :sm)' );

    $stmt->execute(array(
      ':uid' => $_SESSION['user_id'],
      ':fn' => $_POST['first_name'],
      ':ln' => $_POST['last_name'],
      ':em' => $_POST['email'],
      ':hl' => $_POST['headline'],
      ':sm' => $_POST['summary'])
      );


    $profile_id = $pdo->lastInsertId();

    $rank = 1;
    for($i=1; $i <= 9; $i++){
      if (!isset($_POST['year'.$i]) ) continue;
      if (!isset($_POST['desc'.$i]) ) continue;

      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      $stmt = $pdo->prepare('INSERT INTO Position (profile_id, rank, year, description) VALUES (:pif, :rk, :yr, :dp)');
      $stmt->execute(array(
        ':pif' => $profile_id,
        ':rk' => $rank,
        ':yr' => $year,
        ':dp' => $desc
      ));
      $rank++;
    }

    //Institution ADD $profile_id
    $rankedu = 1;
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
        ':pif' => $profile_id,
        ':iid' => $schoolid,
        ':rk' => $rankedu,
        ':yr' => $yearedu
      ));
      $rankedu++;
    }



    $_SESSION['successmessage'] = 'Profile added';
    header('Location: index.php');
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
    <h1>Adding Profile for</h1>
    <?php flashMessage(); ?>

    <form method="post">
      <p>First Name:
      <input type="text" name="first_name" size="60"/></p>
      <p>Last Name:
      <input type="text" name="last_name" size="60"/></p>
      <p>Email:
      <input type="text" name="email" size="30"/></p>
      <p>Headline:<br/>
      <input type="text" name="headline" size="80"/></p>
      <p>Summary:<br/>
      <textarea name="summary" rows="8" cols="80"></textarea>

        <p>
        Education: <input type="submit" id="addEdu" value="+">
        <div id="edu_fields">
        </div>
        </p>

        <p>
        Position: <input type="submit" id="addPos" value="+">
        <div id="position_fields">
        </div>
        </p>

      <p>
      <input type="submit" value="Add">
      <input type="submit" name="cancel" value="Cancel">
      </p>
    </form>

    <script type="text/javascript">
      countPos = 0;
      countEdu = 0;

      // ADD postions DOM
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
