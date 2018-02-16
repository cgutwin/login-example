<?php

//start session if none exists
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

$dbserver = "localhost";
$dblogin = "root";
$dbpw = "root";
//had issue finding original db name, some wierd stuff going on, note for later problems
$dbname = "eggsdb";

$methodType = $_SERVER["REQUEST_METHOD"];
$data = array("status" => "fail", "resp" => "$methodType");

/*
  Must be POST
  Must be AJAX
  Must not have empty fields
*/
if ($methodType === "POST") {
  //Check if AJAX call
  if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
    $name_first = $_POST["name_first"];
    $name_last = $_POST["name_last"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $usertype = $_POST["usertype"];
    /*
      Test if values are blank.
      All values are NOT_NULL marked in table.
    */
    if(isset($name_first) && !empty($name_first)
        && isset($name_last) && !empty($name_last)
        && isset($email) && !empty($email)
        && isset($password) && !empty($password)
        && isset($usertype) && !empty($usertype)) {
      /*
        Hash the password using BCRYPT algo, no options.
        Salt is generated randomly in password_hash function.
      */
      $hashedpw = password_hash($password, PASSWORD_BCRYPT);

      if ($usertype === "farmer") {
        $usertype = 1;
      }
      else {
        $usertype = 0;
      }
      //Submit to DB
      try {
        $conn = new PDO("mysql:host=$dbserver;dbname=$dbname", $dblogin, $dbpw);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "INSERT INTO users (Name_First, Name_Last, Email, Password, UserType)
                  VALUES (:name_first, :name_last, :email, :hashedpw, :usertype)";
        $statement = $conn->prepare($query);
        //bind values to ajax post information
        $statement->bindValue(':name_first', $name_first);
        $statement->bindValue(':name_last', $name_last);
        $statement->bindValue(':email', $email);
        $statement->bindValue(':hashedpw', $hashedpw);
        $statement->bindValue(':usertype', $usertype);

        $statement->execute();

        $data = array("status" => "pass",
                      "resp"=>"added to database $dbname");
      } catch(PDOException $e) {
        //return pdo error message if an error
        $error = $e->getMessage();
        $data = array("status" => "fail",
                      "resp" => "PDO Error: " . $error);
      }
    }
    else {
      $data = array("status" => "fail",
                    "resp" => "Field Missing");
    }
  }
  else {
    $data = array("status" => "fail",
                  "resp" => "Request not AJAX.");
  }
}
else {
  $data = array("status" => "fail",
                "resp" => "Request not POST.");
}

/*
  Must be GET
  Must be AJAX
*/
if ($methodType === "GET") {
  if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
    $email = $_GET["email"];
    $password = $_GET["password"];

    try {
      $conn = new PDO("mysql:host=$dbserver;dbname=$dbname", $dblogin, $dbpw);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      //username and score are added into table
      $query = "SELECT Name_First, Name_Last, Email, Password, UserType
                FROM users
                WHERE email = :email";
      $statement = $conn->prepare($query);
      //bind values to ajax post information
      $statement->bindValue(':email', $email);
      $statement->execute();

      while($row = $statement->fetch()) {
        $r_name_first = $row["Name_First"];
        $r_name_last = $row["Name_Last"];
        $r_email = $row["Email"];
        $r_password = $row["Password"];
        $r_usertype = $row["UserType"];

        if (password_verify($password, $r_password)) {
          // int instead of bool, php 0 returns "".
          $verifiedpw = 1;

          //set session information
          $_SESSION["name_first"] = $r_name_first;
          $_SESSION["name_last"] = $r_name_last;
          $_SESSION["email"] = $r_email;
          $_SESSION["usertype"] = $r_usertype;
          $sid = session_id();

          $data = array("status" => "pass",
                        "sessionid" => "$sid",
                        "verified"=>"$verifiedpw");
        }
        else {
          $verifiedpw = 0;
          $data = array("status" => "fail",
                        "resp"=>"incorrect password",
                        "verified"=>"$verifiedpw");
        }
      }

    } catch(PDOException $e) {
      //return pdo error message if an error
      $error = $e->getMessage();
      $data = array("status" => "fail",
                    "resp" => "PDO Error: " . $error);
    }
  }
  else {
    $data = array("status" => "fail",
                  "resp" => "Request not AJAX.");
  }
}
else {
  $data = array("status" => "fail",
                "resp" => "Request not GET.");
}


echo json_encode($data, JSON_FORCE_OBJECT);
