<?php

include("functions/init.php");
session_destroy();

if(isset($_COOKIE['email'])){
  unset($_COOKIE['email']);
  setcookie('email',$email,time()-86400);
}

redirect("login.php");
