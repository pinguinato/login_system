<?php

//echo "db.php";

define('HOSTNAME','127.0.0.1');
define('DB_NAME','login');
define('DB_USER','root');
define('USER_PASS','root');

$hostname = HOSTNAME;
$user = DB_USER;
$password = USER_PASS;
$dbname = DB_NAME;

// CONNESSIONE AL DB
$connection = mysqli_connect($hostname,$user,$password,$dbname);

///////////////////////////////
//// FUNZIONALITA DEL DATABASE
///////////////////////////////


// conta le righe di una tabella del database
function row_count($result){
    global $connection;
    return mysqli_num_rows($result);
}

// funzione per la pulizia escaping dei dati
function escape($string){
    global $connection;
    //var_dump($string);
    //var_dump(mysql_real_escape_string($connection,$string));
    //die;
    return mysqli_real_escape_string($connection,$string);
}
// ogni volta che faccio una query sul db viene chiamato questo metodo
function query($query){
    global $connection;
    return mysqli_query($connection,$query);
}
// funzione di conferma che la query è corretta
function confirm($result){
    global $connection;
    if(!$result){
        die("QUERY FAILED!");
    }
}
function fetch_data($result){
    global $connection;
    return mysqli_fetch_array($result);
}


?>
