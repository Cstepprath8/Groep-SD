<?php
header("Content-Type: application/json; charset=UTF-8");
session_start();

// Databaseverbinding
require_once __DIR__ . '/../dataBase/DB_Connect.php';
if (!$conn) {
    echo json_encode(["status"=>"error","message"=>"Databaseverbinding mislukt."]);
    exit;
}

// Helperfunctie om JSON terug te geven
function respond($status, $message, $redirect = null) {
    $response = ["status"=>$status,"message"=>$message];
    if ($redirect) $response["redirect"] = $redirect;
    echo json_encode($response);
    exit;
}

// Haal actie op
$action = $_POST['action'] ?? '';

switch($action) {

    // ==========================
    // LOGIN
    // ==========================
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if (!$username || !$password) respond("error","Vul gebruikersnaam en wachtwoord in.");

        $stmt = $conn->prepare("SELECT * FROM student WHERE email=? OR naam=?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Wachtwoorden'])) {
                $_SESSION['user_id'] = $user['StudentID'];
                $_SESSION['user_name'] = $user['naam'];
                $_SESSION['user_email'] = $user['email'];
                respond("success","Welkom terug, {$user['naam']}!","Dashboard.html");
            } else {
                respond("error","Onjuist wachtwoord.");
            }
        } else {
            respond("error","Gebruiker niet gevonden.");
        }
        break;

    // ==========================
    // REGISTER
    // ==========================
    case 'register':
        $naam = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if (!$naam || !$email || !$password) respond("error","Vul alle velden in.");

        // Controleer of email al bestaat
        $stmt = $conn->prepare("SELECT StudentID FROM student WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows>0) respond("error","Email bestaat al.");

        // Wachtwoord hashen en opslaan
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO student (naam,email,Wachtwoorden,role) VALUES (?,?,?,'member')");
        $stmt->bind_param("sss",$naam,$email,$hash);

        if ($stmt->execute()) respond("success","Account succesvol aangemaakt!");
        else respond("error","Er ging iets mis bij het registreren.");
        break;

    // ==========================
    // GOOGLE LOGIN
    // ==========================
    case 'google_login':
        $id_token = $_POST['id_token'] ?? '';
        if (!$id_token) respond("error","Geen Google token ontvangen.");

        $verify = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=".$id_token);
        $info = json_decode($verify,true);

        if (empty($info['email'])) respond("error","Ongeldig Google-token.");

        $email = $info['email'];
        $naam = $info['name'] ?? 'Onbekend';
        $dummyPassword = password_hash('google_login', PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT * FROM student WHERE email=?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows>0){
            $user = $res->fetch_assoc();
            $_SESSION['user_id']=$user['StudentID'];
            $_SESSION['user_name']=$user['naam'];
            $_SESSION['user_email']=$user['email'];
            respond("success","Welkom terug, {$user['naam']}!","Dashboard.html");
        } else {
            $stmt=$conn->prepare("INSERT INTO student (naam,email,Wachtwoorden,role) VALUES (?,?,?,'member')");
            $stmt->bind_param("sss",$naam,$email,$dummyPassword);
            if($stmt->execute()){
                $_SESSION['user_id']=$conn->insert_id;
                $_SESSION['user_name']=$naam;
                $_SESSION['user_email']=$email;
                respond("success","Account aangemaakt via Google!","Dashboard.html");
            } else respond("error","Kon Google-gebruiker niet opslaan.");
        }
        break;

    // ==========================
    // ONBEKENDE ACTIE
    // ==========================
    default:
        respond("error","Ongeldige actie.");
}

$conn->close();
