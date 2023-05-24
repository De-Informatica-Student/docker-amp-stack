<?php
// Definieer de informatie van de database voor gebruik
$server = "dbserver";
$username = "root";
$password = "root";

// Probeer om deze code uit te voeren
try {
    // Maak een PDO object, dit staat voor PHP Data Objects
    // Deze objecten worden gebruikt om verbinding te maken met de database
    $db = new PDO("mysql:host=$server;dbname=AwesomeDB", $username, $password);

    // We geven aan dat we foutmelding willen ontvangen van de database en de verbinding
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Als deze niet worden gegeven, is de verbinding goed aangelegd
    echo "Connected successfully";
} 

// Als er een foutmelding wordt gegenereerd, dan wordt deze code uitgevoerd.
catch(PDOException $e) {
    // Geef aan dat de verbinding is mislukt en geef een melding
    echo "Connection failed: " . $e->getMessage();
}