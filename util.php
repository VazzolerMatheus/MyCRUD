<?php
// Flash Message
function flashMessage(){
  if(isset($_SESSION['successmessage'])){
    echo('<p style="color:green">'.$_SESSION['successmessage'].'</p>');
    unset($_SESSION['successmessage']);
  }

  if(isset($_SESSION['failmessage'])){
    echo('<p style="color:red">'.$_SESSION['failmessage'].'</p>');
    unset($_SESSION['failmessage']);
  }
}

function validateProfile(){
  if(strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1){
    return 'All fields are required';

  } elseif (strpos($_POST['email'], '@') === false) {
    return 'Email address must contain @';
  }

  return true;
}


function validatePos() {
  for($i=1; $i<=9; $i++) {
    if ( ! isset($_POST['year'.$i]) ) continue;
    if ( ! isset($_POST['desc'.$i]) ) continue;

    $year = $_POST['year'.$i];
    $desc = $_POST['desc'.$i];

    if ( strlen($year) == 0 || strlen($desc) == 0 ) {
      return "All fields are required";
    }

    if ( ! is_numeric($year) ) {
      return "Position year must be numeric";
    }
  }
  return true;
}

function validateEdu() {
  for($j=1; $j<=9; $j++) {
    if ( ! isset($_POST['edu_year'.$j]) ) continue;
    if ( ! isset($_POST['edu_school'.$j]) ) continue;

    $year = $_POST['edu_year'.$j];
    $school = $_POST['edu_school'.$j];

    if ( strlen($year) == 0 || strlen($school) == 0 ) {
      return "All fields are required";
    }

    if ( ! is_numeric($year) ) {
      return "Education year must be numeric";
    }
  }
  return true;
}

 ?>
