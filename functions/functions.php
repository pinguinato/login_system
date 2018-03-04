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

function display_success_message($message){
  echo '<p class="bg-success">'.$message.'</p>';
}

function display_validation_errors($error_message){
    echo '<div class="alert alert-danger">'.$error_message.'</div>';
}

function display_login_errors($error_message){
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

      $sql = "SELECT id FROM users WHERE email = '".escape($email)."' AND validation_code = '".escape($validation_code)."'";
      $result = query($sql);
      confirm($result);
      if(row_count($result) == 1){
          // varibili solo per far capire il passaggio
          $the_email = escape($email);
          $the_validation_code = escape($validation_code);

          $sql2 = "UPDATE users SET active = 1, validation_code = 0 WHERE email = '".$the_email."' AND validation_code = '".$the_validation_code."' ";
          $result2 = query($sql2);
          confirm($result2);

          set_message("<p class='bg-success'>Il tuo account è stato attivato con successo.</p>");
          redirect("login.php"); // reindirizzamento alla pagina di login
      }else{
        set_message("<p class='bg-danger'>Il tuo account non è stato attivato.</p>");
        redirect("login.php"); // reindirizzamento alla pagina di login
      }
    }
  }
}


///////////////////////////////////////////////////
//////////VALIDATE USER LOGIN /////////////////////
///////////////////////////////////////////////////

function validate_user_login(){
  $errors = [];
  $min = 3;
  $max = 20;

  if($_SERVER['REQUEST_METHOD'] == 'POST'){

    //echo "FUNZIONA";

    //recupero email e Password
    $email = clean($_POST['email']);
    $password = clean($_POST['password']);
    // remeber me
    $remember = isset($_POST['remember']);

    // controllo email vuota
    if(empty($email)){
        $errors[] = "Il campo email non può essere vuoto.";
    }
    // controllo Password
    if(empty($password)){
        $errors[] = "Il campo password non può essere vuoto.";
    }

    // verifica se l'array degli errori ha qualcosa
    if(!empty($errors)){

      // ci sono degli errori
      foreach($errors as $error){
          // richiamo funzione che stampa gli errori
          display_login_errors($error);
      }


    }else{

      // loggati
      echo "NON CI SONO ERRORI, TUTTO OK!";

      if(user_login($email,$password,$remember)){
        redirect("admin.php");
      }else{
        display_login_errors("Le tue credenziali non sono corrette!");
      }

    }

  }
}

////////////////////////////////////////////////////////
////////////// USER LOGIN FUNCTION /////////////////////
////////////////////////////////////////////////////////

function user_login($email,$password,$remember){
    $sql = "SELECT password, id FROM users WHERE email = '".escape($email)."'";
    $result = query($sql);
    if(row_count($result) == 1){
      $row = fetch_data($result);
      $db_password = $row['password'];
      // adesso bisogna verificare il campo password perché è crittato
      // decodifica della password
      if(md5($password) === $db_password ){

        // se il parametro remember è settato su ON allora setto un cookie per far funzionare il remeber me
        if($remember == 'on'){
          // setto un cookie che durerà per 60 secondi
          setcookie('email',$email,time() + 86400);
        }
        // salviamo la mail in una sessione
        $_SESSION['email'] = $email;

        return true;
      }else{
        return false; // OK
      }
    }else{
      return false; // OK
    }
}

////////////////////////////////////////////////////////
/////////////// LOGIN FUNCTION /////////////////////////
////////////////////////////////////////////////////////

function logged_in(){
  // verifica solo che sia settata l'email per la sessione e ritorna vero
  if( isset($_SESSION['email']) || isset($_COOKIE['email']) ){
    return true;
  }else{
    return false;
  }
}

////////////////////////////////////////////////////////
/////////////// recupero della password /////////////////////////
////////////////////////////////////////////////////////


function recover_password(){
  if($_SERVER['REQUEST_METHOD'] == "POST"){
    //var_dump($_SESSION['token']);
    //var_dump($_POST['token']);
    // usiamo di nuovo i ltoken token_generator
    if( isset($_SESSION['token']) && ($_POST['token'] === $_SESSION['token']) ){
      $email = escape($_POST['email']);
      //var_dump($email);
      //var_dump($_POST);
      //echo "FUNZIONA!!";
      if(email_exists($email)){
        $validation_code = md5($email . microtime());
        setcookie('temp_access_code',$validation_code,time()+60);
        $sql = "UPDATE users SET validation_code = '".escape($validation_code)."' WHERE email = '".escape($email)."'";
        $result = query($sql);
        confirm($result);
        $subject = "Recupero della password";
        $message = "Per favore clicca il link qui sotto per recuperare la tua password {$validation_code}:

          http://192.168.33.10/corsophp-diaz/LOGIN/MIO-LOGIN/code.php?email=$email&code=$validation_code

        ";
        $headers = "From noreply@192.168.33.10";
          if(send_email($email,$subject,$message,$headers)){
              set_message("<p class='bg-success'>Per favore controlla la tua email.</p>");
              redirect("index.php");
          }else{
            echo display_validation_errors("Email non inviata");
          }
      }else{
        echo display_validation_errors("Questa email non esiste");
      }
    }else{
      // se il token non viene settato torna alla index
      redirect("index.php");
    }
  }
}


////////////////////////////////////////////////////////
/////////////// code validation ////////////////////////
////////////////////////////////////////////////////////

function validate_code(){
  if(isset($_COOKIE['temp_access_code'])){

    if($_SERVER['REQUEST_METHOD'] == "GET"){

      if(isset($_GET['email']) && isset($_GET['code']) ){

        // TODO: fai qualcosa per la validazione del codice


      }
    }


  }else{
    set_message("<p class='bg-danger'>Il tuo cookie di validazione è scaduto.</p>");
    redirect("recover.php");
  }
}



?>
