<?php
ob_start();
session_start();
include("db.php");
include("functions.php");
// test funzionamento della connessione
if($connection){
    echo "Siamo connessi al database";
    //echo "CIAO CIAO " . ob_get_contents();
}else{
    die("Non sono riuscito a cannettermi");
}
?>