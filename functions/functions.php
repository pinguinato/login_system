<?php

//echo "functions.php";


////////////////////////////////
/// FUNZIONI HELPER ////////////
////////////////////////////////


//funzione che pulisce una stringa da caratteri che non vanno bene
function clean($string){
    return htmlentities($string);
}

//funzione che effettua il redirect con l'uso di header php
function redirect($location){
    return header("Location: {$location}");
}
// messaggi di sessione
function set_message($message){
    if(!empty($message)){
        $_SESSION['message'] = $message;
    }else{
        $message = "";
    }
}
// funzione che mostra questi messaggi di sessione
function display_message(){
    if(isset($_SESSION['message'])){
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
}
// funzione che genera un token
function token_generator(){
    $token = $_SESSION['token'] = md5(uniqid(mt_rand(),true));
    return $token;
}

// funzione che controlla se una mail esiste già nel db
function email_exists($email){
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = query($sql);
    if(row_count($result) == 1){
        return true;
    }else{
        return false;
    }
}

// TODO: restituisce un errore controllare

// funzione che verifica l'esistenza di un username duplicato
function username_exists($username){
    $sql = "SELECT id FROM users WHERE username = '$username'";
    //var_dump($sql); die;
    $result = query($sql);
    //var_dump($result); die();
    if(row_count($result) == 1){
        return true;
    }else{
        return false;
    }
}

//manda una mail
function send_email($email,$subject,$msg,$header){
  return mail($email,$subject,$msg,$header);
}


////////////////////////////////////////
///// FUNZIONI DI VALIDAZIONE //////////
////////////////////////////////////////

// validazione lato server

function display_validation_errors($error_message){
    echo '<div class="alert alert-danger">'.$error_message.'</div>';
}


function validate_user_registration(){
    //minima lunghezza di una stringa
    $min = 3;
    // massima lunghezza di una stringa
    $max = 20;
    // array per salvare gli errori
    $max_email = 30;
    $errors = [];

    if($_SERVER['REQUEST_METHOD'] === "POST"){
        $first_name = clean($_POST['first_name']);
        $last_name = clean($_POST['last_name']);
        $username = clean($_POST['username']);
        $email = clean($_POST['email']);
        $password = clean($_POST['password']);
        $confirm_password = clean($_POST['confirm_password']);



        // verifica sul nome
        if(strlen($first_name) < $min){
            $errors[] = "Il nome deve essere lungo almeno {$min} caratteri";
        }
        if(strlen($first_name) > $max){
            $errors[] = "Il nome non deve essere lungo più di {$max} caratteri";
        }
        if(empty($first_name)){
            $errors[] = "Il nome può essere vuoto";
        }
        // verifica sul cognome
        if(strlen($last_name) < $min){
            $errors[] = "Il cognome deve essere lungo almeno {$min} caratteri";
        }
        if(strlen($last_name) > $max){
            $errors[] = "Il cognome non deve essere lungo più di {$max} caratteri";
        }
        if(empty($last_name)){
            $errors[] = "Il cognome non può essere vuoto";
        }
        // verifica sullo username
        if(strlen($username) < $min){
            $errors[] = "Lo username deve essere lungo almeno {$min} caratteri";
        }
        if(strlen($username) > $max){
            $errors[] = "Lo username non deve essere lungo più di {$max} caratteri";
        }
        if(empty($username)){
            $errors[] = "Lo username può essere vuoto";
        }
        // verifica se esiste già lo username
        if(username_exists($username)){
            $errors[] = "Questo username è già utilizzato";
        }
        // verifica se esiste già la mail
        if(email_exists($email)){
            $errors[] = "Un utente con questa email esiste già";
        }
        if(strlen($email) < $min){
            $errors[] = "La tua email non deve essere inferiore a {$min} caratteri";
        }
        //verifica della email
        if(strlen($email) > $max_email){
            $errors[] = "La tua email non deve essere lungo più di {$max_email} caratteri";
        }
        // verifica della password
        if($password !== $confirm_password){
            $errors[] = "La passworrd è diversa dalla conferma della password!";
        }
        //


        // casi di errore
        if(!empty($errors)){
            foreach($errors as $error){
                // richiamo funzione che stampa gli errori
                display_validation_errors($error);
            }
        }else{



          // se non ci sono errori di validazione registra
          if(register_user($first_name,$last_name,$username,$email,$password)){
            //echo "user registered";

            set_message('<p class="bg-success text-center">Controlla la tua email per il messaggio di attivazione.</p>');
            redirect("index.php");
          }else{
            set_message('<p class="bg-danger text-center">Non posso registrare lo User</p>');
            redirect("index.php");
          }
        }
    }
} // end valdate user registration


function register_user($first_name,$last_name,$username,$email,$password){
  //escaping dei dati
  $first_name = escape($first_name);
  $last_name = escape($last_name);
  $username = escape($username);
  $email = escape($email);
  $password = escape($password);
  //check email_exists
  if(email_exists($email)){
    return false;
  }else if(username_exists($username)){
    return false;
  }else{
    $password = md5($password);
    $validation_code = md5($username . microtime());
    $sql = "INSERT INTO users(first_name,last_name,username,email,password,validation_code,active)";
    $sql .= " VALUES ('$first_name','$last_name','$username','$email','$password','$validation_code',0)";
    //var_dump($sql);
    $result = query($sql);
    confirm($result);
    $subject = "Attivazione account";
    $message = "Per favore clicca qui sotto per attivare il tuo account:
      http://192.168.33.10/corsophp-diaz/LOGIN/MIO-LOGIN/activate.php?email=$email&code=$validation_code
    ";
    $header = "From: noreply@ilmiosito.it";
    send_email($email,$subject,$message,$header);
    return true;
  }
}

////////////////////////////////////////////////
///////// ACTIVATE USER FUNCTION ///////////////
////////////////////////////////////////////////

function activate_user(){
  if($_SERVER['REQUEST_METHOD'] == "GET"){
    if(isset($_GET['email'])){
      $email = clean($_GET['email']);
      $validation_code = clean($_GET['code']);
      $sql = "SELECT id FROM users WHERE email = '".escape($_GET['email'])."' AND validation_code = '".escape($_GET['validation_code'])."'";
      $result = query($sql);
      confirm($result);
      if(row_count($result) == 1){
          echo "<p class='bg-success'>You account has been activated!</p>";
      }

      //echo $email = clean($_GET['email']);
      //echo $validation_code = clean($_GET['code']);
    }
  }
}


?>
