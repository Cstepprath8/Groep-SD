<?php
// db_connect.php

$servername = "localhost";  // Je servernaam (meestal localhost)
$username = "root";         // Je MySQL gebruikersnaam
$password = "Wachtwoord";   // Je MySQL wachtwoord
$dbname = "skillrader";       // Je database naam

// Maak verbinding met de database
$conn = new mysqli($servername, $username, $password, $dbname);

// Controleer de verbinding
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>