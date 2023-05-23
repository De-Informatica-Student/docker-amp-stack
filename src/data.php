<?php
    // Definieer de informatie van de database voor gebruik
    $server = "dbserver";
    $username = "root";
    $password = "root";

    // Maak een PDO object, dit staat voor PHP Data Objects
    // Deze objecten worden gebruikt om verbinding te maken met de database
    $db = new PDO("mysql:host=$server;dbname=AwesomeDB", $username, $password);

    // We geven aan dat we foutmelding willen ontvangen van de database en de verbinding
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Controleer of er nieuwe data is gegeven
    if (isset($_POST["addition"])) {
        // Voeg de data toe aan de database
        $sql = "INSERT INTO Data (Info) VALUES ('" . $_POST["info"] . "');";
        $db->query($sql);
    }

    // Controleer of er data moet worden verwijderd
    if (isset($_POST["delete"])) {
        // Verwijder de data uit de database
        $sql = "DELETE FROM Data WHERE Id = '" . $_POST["id"] . "';";
        $db->query($sql);
    }
?>

<html>
    <body>
        <form action="data.php" method="post">
            <input type="text" name="info" id="info">
            <input type="submit" name="addition" value="Registreer Data">
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Acties</th>
                </tr>
            </thead>

            <tbody>
                <?php
                    // Haal alle data op uit de database
                    $sql = 'SELECT * FROM Data;';

                    // Ga over alle items heen en print ze naar de tabel
                    foreach ($db->query($sql) as $row) { ?>
                        <tr>
                        <td><?php echo $row['Id']; ?></td>
                        <td><?php echo $row['Info']; ?></td>
                        <td>
                            <form action="data.php" method="post">
                                <input type="hidden" name="id" value="<?php echo $row['Id'] ?>">
                                <input type="submit" name="delete" value="Verwijderen">
                            </form>
                        </td>
                        </tr>
                    <?php }
                ?>
            </tbody>
        </table>
    </body>
</html>