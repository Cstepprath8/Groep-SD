<?php 
include(__DIR__ . '/../DataBase/db_connect.php');

// AJAX login
if(isset($_POST['action']) && $_POST['action'] === 'login') {
    $usernameOrEmail = $_POST['username'];
    $password = $_POST['password'];

    //connect tot de juiste DB tabel.
    $stmt = $conn->prepare("SELECT * FROM student WHERE email=? OR naam=?");
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    //fout error als het niet lukt.
    if($result->num_rows === 0){
        echo json_encode(["status"=>"error", "message"=>"Gebruiker niet gevonden"]);
        exit;
    }
    //haalt de inlog gegevens in de tabel.
    $user = $result->fetch_assoc();
    if(password_verify($password, $user['Wachtwoorden'])){
        $_SESSION['user_id'] = $user['StudentID'];
        $_SESSION['user_name'] = $user['naam'];
        echo json_encode(["status"=>"success", "message"=>"Inloggen gelukt"]);
    } else {
        echo json_encode(["status"=>"error", "message"=>"Wachtwoord onjuist"]);
    }
    exit;
}

  // AJAX registratie
  if(isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $email = $_POST['email'];

  // Controleer of het een geldig e-mailadres is
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "Voer een geldig e-mailadres in."]);
    exit;
  } 

    // Check email
    $stmt = $conn->prepare("SELECT * FROM student WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        echo json_encode(["status"=>"error", "message"=>"Email bestaat al"]);
        exit;
    }
    //zorgt ervoor dat het wachtwoord met hash opgehaald kan worden en verivieerd kan worden.
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO student (naam,email,Wachtwoorden) VALUES (?,?,?)");
    $stmt->bind_param("sss", $username, $email, $hashedPassword);
    if($stmt->execute()){
        echo json_encode(["status"=>"success", "message"=>"Account aangemaakt"]);
    } else {
        echo json_encode(["status"=>"error", "message"=>"Er is iets misgegaan"]);
    }
    exit;
}


?>



<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Pagina</title>

  <!-- Fonts importeren -->
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Horizon&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Archivo+Black&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../CSS/Login.css" />
  <link rel="stylesheet" href="../CSS/nav.css" />

</head>
<body>
  <img id="wijzer" src="../Foto/SkillRader_Logo.png" alt="Wijzer" />

    <div class="nav">
     <a href="../Homepagina/index.html" class="title-link">
      <img src="../Foto/SkillRader_Logo.png" alt="Logo" class="logo">
      <h1>The SkillRader</h1>
     </a>
    <button onclick="window.location.href='../Homepagina/index.html'">⭠ Terug</button>
  </div>

  <div class="login-container">
    <h2 id="form-title">Inloggen</h2>
    <div id="message"></div>

    <!-- Inloggen -->
    <div id="login-form">
      <form>
        <input type="text" id="login-username" placeholder="Gebruikersnaam of email"  required/>
        <input type="password" id="login-password" placeholder="Wachtwoord"  required/>
        <button type="button" onclick="login()">Login</button>
        <span class="toggle-link" onclick="toggleForm()">Nog geen account? Maak er een aan</span>
      </form>
    </div>

    <!-- Registratie -->
    <div id="register-form" style="display: none;">
      <form>
        <input type="text" id="register-username" placeholder="Gebruikersnaam" required/>
        <input type="email" id="register-email" placeholder="Email" required />
       <input type="password" id="register-password" placeholder="Wachtwoord"  required/>
        <button type="button" onclick="register()">Account aanmaken</button>
        <span class="toggle-link" onclick="toggleForm()">Heb je al een account? Log dan in</span>
      </form>
    </div>
  </div>

<script>
  //toggled tussen de forms
function toggleForm() {
    const loginForm = document.getElementById("login-form");
    const registerForm = document.getElementById("register-form");
    const message = document.getElementById("message");

    message.textContent = "";
    message.className = "";
    //laat je switchen tussen login en registratie
    if (loginForm.style.display === "none") {
        loginForm.style.display = "block";
        registerForm.style.display = "none";
        document.getElementById("form-title").textContent = "Inloggen";
    } else {
        loginForm.style.display = "none";
        registerForm.style.display = "block";
        document.getElementById("form-title").textContent = "Account aanmaken";
    }
}

// ---bestaande login() en register() functies hier ---
function login() {
  const username = document.getElementById("login-username").value.trim();
  const password = document.getElementById("login-password").value.trim();
  const message = document.getElementById("message");

  fetch("Login.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: `action=login&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
  })
  .then(res => res.json())
  .then(data => {
    message.textContent = data.message;
    message.className = data.status === "success" ? "success" : "error";

    if (data.status === "success") {
      setTimeout(() => {
        window.location.href = "homepage.html";
      }, 1500);
    }
  });
}

function register() {
  const username = document.getElementById("register-username").value.trim();
  const email = document.getElementById("register-email").value.trim();
  const password = document.getElementById("register-password").value.trim();
  const message = document.getElementById("message");

  if (!username || !email || !password) {
    message.textContent = "Vul alle velden in.";
    message.className = "error";
    return;
  }

  fetch("Login.php", {
    method: "POST",
    headers: {"Content-Type": "application/x-www-form-urlencoded"},
    body: `action=register&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
  })
  .then(res => res.json())
  .then(data => {
    message.textContent = data.message;
    message.className = data.status === "success" ? "success" : "error";
    // laat je terug gaan naar de login pagina
    if (data.status === "success") {
      setTimeout(() => {
        toggleForm(); 
      }, 2000);
    }
  });
}
</script>


  <div class="footer-image">
    <img src="../Foto/GildeDevOps.png" alt="Footer Logo">
  </div>

</body>
</html>