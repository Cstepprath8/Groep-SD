<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="google-signin-client_id" content="160241223713-t43f2iqbegqi2aec0j2v0r8mnbl16enk.apps.googleusercontent.com">
    <title>Login Pagina</title>
    <link rel="icon" type="image/x-icon" href="../Foto/Flavicon Skillradar 32x32.png">
    <!-- Fonts importeren -->
    <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Horizon&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=ADLaM+Display&family=Archivo+Black&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../CSS/Login.css" />
    <link rel="stylesheet" href="../CSS/nav.css" />
</head>
<body>
<img id="wijzer" src="../Foto/SkillRader_Logo.png" alt="Wijzer" />

<div class="nav">
    <a href="../Homepagina/index.html" class="title-link">
        <img src="../Foto/SkillRader_Logo.png" alt="Logo" class="logo" />
        <h1>The SkillRadar</h1>
    </a>
    <button onclick="window.location.href='../Homepagina/index.html'">⭠ Terug</button>
</div>

<div class="login-container">
    <h2 id="form-title">Inloggen</h2>
    <div id="message"></div>

    <!-- Inloggen -->
    <div id="login-form" style="display: block;">
        <form>
            <input type="text" id="login-username" placeholder="Gebruikersnaam of email" required />
            <input type="password" id="login-password" placeholder="Wachtwoord" required />
            <button type="button" onclick="login()">Login</button>
            <span class="toggle-link" onclick="toggleForm()">Nog geen account? Maak er een aan</span>
            <div id="g_id_signin"></div>
        </form>
    </div>

    <!-- Registratie -->
    <div id="register-form" style="display: none;">
        <form>
            <input type="text" id="register-username" placeholder="Gebruikersnaam" required />
            <input type="email" id="register-email" placeholder="Email" required />
            <input type="password" id="register-password" placeholder="Wachtwoord" required />
            <button type="button" onclick="register()">Account aanmaken</button>
            <span class="toggle-link" onclick="toggleForm()">Heb je al een account? Log dan in</span>
            <div id="g_id_signin_register"></div>
        </form>
    </div>
</div>

<div class="footer-image">
    <img src="../Foto/GildeDevOps.png" alt="Footer Logo" />
</div>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
    // Toggle tussen forms
    function toggleForm() {
        const loginForm = document.getElementById("login-form");
        const registerForm = document.getElementById("register-form");
        const message = document.getElementById("message");
        message.textContent = "";
        message.className = "";
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

    // Login functie
    function login() {
        const username = document.getElementById("login-username").value.trim();
        const password = document.getElementById("login-password").value.trim();
        const message = document.getElementById("message");

        fetch("auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=login&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
        })
        .then(res => res.json())
        .then(data => {
            message.textContent = data.message;
            message.className = data.status === "success" ? "success" : "error";
            if (data.status === "success") {
                setTimeout(() => { window.location.href = data.redirect; }, 1500);
            }
        });
    }

    // Registratie functie
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

        fetch("auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=register&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
        })
        .then(res => res.json())
        .then(data => {
            message.textContent = data.message;
            message.className = data.status === "success" ? "success" : "error";
            if (data.status === "success") {
                setTimeout(() => { toggleForm(); }, 2000);
            }
        });
    }

    // Google-login
    window.onload = () => {
        google.accounts.id.initialize({
            client_id: '160241223713-t43f2iqbegqi2aec0j2v0r8mnbl16enk.apps.googleusercontent.com',
            callback: handleCredentialResponse
        });

        google.accounts.id.renderButton(
            document.getElementById('g_id_signin'),
            { theme: "outline", size: "large", width: 270 }
        );

        google.accounts.id.renderButton(
            document.getElementById('g_id_signin_register'),
            { theme: "outline", size: "large", width: 270 }
        );

        google.accounts.id.prompt(); // automatisch prompt
    };

    function handleCredentialResponse(response) {
        const message = document.getElementById("message");
        fetch("auth.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=google_login&id_token=${encodeURIComponent(response.credential)}`
        })
        .then(res => res.json())
        .then(data => {
            message.textContent = data.message;
            message.className = data.status === "success" ? "success" : "error";
            if (data.status === "success") {
                setTimeout(() => { window.location.href = data.redirect; }, 1500);
            }
        });
    }
</script>

<div class="footer-image">
    <img src="../Foto/GildeDevOps.png" alt="Footer Logo" />
</div>

</body>
</html>
