# Apache, MySQL en PHP Stack met Docker

Voor dit voorbeeld gaan we kijken naar het maken van een Apache, MySQL en PHP stack met Docker.
Hiervoor beginnen we met dockerfiles om alle onderdelen te bekijken,
waarna we Docker Compose gaan gebruiken om alle onderdelen aan elkaar te plakken.
Het is belangrijk dat je voor dit voorbeeld bekend bent met de termen van docker:
Als dit nog niet het geval, kijk dan onderstaande video om hier bekend met te raken:
<https://youtu.be/R8UBnOHen2k>

De PHP code die gebruikt wordt tijdens dit voorbeeld kan worden gedownload vanuit de repository zelf, 
of via ```oefenbestanden.zip``` bestand in de hoofdmap van de repo.


## Dockerfile

Een dockerfile is een bestand dat docker vertelt hoe een image gemaakt moet worden.
Hierin staan alle stapen,
van base-image tot de mogelijkheid om de image te draaien in een container.
Een dockerfile heeft als bestandsnaam ```dockerfile``` zonder extensie.
Vervolgens kunnen we aan de slag gaan met het maken van de eerste image.

### PHP

Het eerste waar we een dockerfile voor gaan maken een simpele PHP pagina.
De eerste stap om dit te bereiken is aangeven dat we onze image baseren op php.
Een image is in de meeste gevallen gebaseerd op een andere image.
Dit zorgt ervoor dat je alle benodigdheden heb om direct aan de slag te kunnen.
Standaard images kun je vinden op dockerhub: <https://hub.docker.com/>

In een map naar keuze voor dit project,
begin je met het maken van een dockerfile.
In dit bestand geef je aan waarop je de image wilt baseren,
voor ons is dit php met apache, versie 8.1.
Daarnaast geef je aan waar de bestanden staan die je wilt kopiëren naar de image.
Ik heb ervoor gekozen om in mijn projectmap een submap aan te maken voor code,
deze map heb ik ```src``` genoemd.
De inhoud hiervan wordt gekopiëerd naar ```/var/www/html/``` in de image.

```dockerfile
FROM php:8.1-apache
COPY ./src/ /var/www/html/
```

In de ```src``` map van het project maak ik een bestand aan genaamd ```index.php```.
Dit is het eerste bestand dat wordt geladen op het moment dat de website wordt geopend.
In dit bestand komt niet veel inhoud,
voor nu is het doel om ervoor te zorgen dat het bestand daadwerkelijk wordt getoond.

```php
<?php echo phpinfo(); ?>
```

In de terminal gaan we een aantal acties uitvoeren.
Zorg er ten alle tijden voor dat je terminal werkt vanuit de projectmap.
De eerste stap is het bouwen van de image,
dit zet ons project om naar image door middel van ons dockerfile.
Vervolgens gaan we de image starten,
hier is de belangrijkste stap om de port 8080 van onze computer
te koppelen aan poort 80 van de container.
Anders komen we niet bij de website.
De volgende opdrachten ga je uitvoeren:

```shell
# Build project, dit maakt een image door middel van het dockerbestand
# Daarbij geven we de tag (naam) php-demo, zo kunnen we hem voortaan aanroepen
docker build -t php-demo .

# Start een container en draai onze image.
# De container krijgt de naam php-demo, om hem makkelijk te kunnen aanroepen.
# We starten de container in (d)etached modus, output wordt verborgen
# We zorgen ervoor dat we poort 8080 op onze computer, koppelen aan port 80 van de container
# We starten de image met de naam php-demo
docker run --name php-demo -d -p 8080:80 php-demo
```

Op dit punt kun je de browser openen en navigeren naar localhost:8080.
Op dat moment zou je een grote lap tekst te zien moeten krijgen met informatie over PHP.
Als dat is gelukt, dan heb je zojuist je eerste Docker container uitgevoerd.
Een statische website doet echter niet zo heel veel,
we gaan een database toevoegen.
Eerst stoppen we de container en verwijderen de restanten ervan.
We verwijderen de container meteen,
omdat er geen twee containers met dezelfde naam kunnen bestaan.

```shell
# Stop de container
docker stop php-demo

# Verwijder de container
docker container rm php-demo
```

#### Poorten

Een poort in informatica kunnen we vergelijken met een adres.
Als we een ip-adres kunnen zien als het adres van de computer,
dan is de poort de toevoeging achter het huisnummer.
De F in Steenstraat 68F.
Ze staan ons toe om specifieke services of servers te bereiken op een computer.
Zo draait MySQL standaard op poort 3306 en apache op poort 80.
De poort staat ons toe om een specifieke server te bereiken,
net zoals het ons toestaat een specifiek appartement te bereiken in woningcomplex.

### MySQL

MySQL wordt de database engine die we gaan gebruiken tijdens dit voorbeeld.
Hiervoor gaan we een nieuw dockerfile maken.
Het is mogelijk om meerdere opdrachten uit te voeren in één dockerfile,
persoonlijk vind ik het fijner om de logica van verschillende componenten gescheiden te houden.
Ook om een beetje het single-resposibility principe in gedachte gehouden.

Deze dockerfile geven we de naam ```dockerfile.mysql```.
We gebruiken deze naam omdat we geen twee bestanden dezelfde naam kunnen geven
en om onderscheid te maken tussen ons project en z'n afhankelijkheden.
Onze container voor MySQL is gebaseerd op mysql 8.0.
Hier zien we een nieuwe opdracht: ENV.
Dit zijn variabele die gebruikt worden om de container in te stellen.

```dockerfile
FROM mysql:8.0

# We stellen de omgevingsvariabelen in
# De eerste is het wachtwoord van de rootgebruiker, dit maken we root
ENV MYSQL_ROOT_PASSWORD=root

# Het tweede is de naam van de standaard database
# Deze database wordt automatisch gemaakt als de container start
ENV MYSQL_DATABASE=AwesomeDB
```

Om MySQL te gebruiken in PHP moeten we extra dingen installeren op de PHP image.
PDO wordt gebruikt om te verbinden met databases,
het staat voor PHP Data Objects.
Daarnaast hebben we installatie voor mysql nodig,
zo weet PDO hoe er verbonden moeten worden met de database.
De ```RUN``` opdracht wordt gebruikt om terminal commando's binnen de image uit te voeren.

```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY ./src/ /var/www/html/
```

Daarnaast hebben we een manier nodig om de database te bereiken.
Hiervoor maken we ```testconnection.php``` aan met de volgende code.
Gezien dit geen instructie PHP is,
gaan we niet verder op de code in dan de comments.

```php
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
```

We gaan nu beide containers starten.
We moeten hiervoor beide dockerfiles omzetten naar images,
en deze moeten apart van elkaar worden gestart.
Let hierbij op de MySQL niet verbonden wordt aan een poort.
Je zult zien dat je een foutmelding krijgt op het moment dat je de website opent.
Dit is goed, we gaan die fout in het volgende deel oplossen.

```shell
# Build en voer de image voor ons project uit
docker build -t php-demo .
docker run --name php-demo -d -p 8080:80 php-demo

# We moeten tijdens het builden aangeven dat we dockerfile.mysql gebruiken
# Daarna voeren we de image uit op een container
docker build -f dockerfile.mysql -t dbserver .
docker run --name dbserver -d dbserver
```

### Netwerk

Om het verbindingsprobleem op te lossen gaan we een virtueel netwerk maken.
Een virtueel netwerk kan containers aan elkaar verbinden.
Hiervoor stoppen we eerst de containers en verwijderen we hun restanten.
Vervolgens maken een netwerk aan, met als (d)river bride.
Dit zorgt ervoor dat de containers verbonden met hetzelfde netwerk kunnen communiceren.
Daarna voeren we de images uit terwijl ze verbonden zijn aan het netwerk.
Nu zou de database moeten werken.

```shell
docker stop php-demo
docker stop dbserver
docker system prune

docker network create -d bridge php-network

docker run --name php-demo --network php-network -d -p 8080:80 php-demo
docker run --name dbserver --network php-network -d dbserver
```

### Volumes

Volumes in Docker zijn een manier om mappen op je computer te koppelen aan containers.
Op deze manier blijven bestanden bewaard,
en worden wijzigingen doorgevoerd zonder de image opnieuw te hoefen builden en uitvoeren.
We gaan nu de interactie met de database maken.
Hiervoor gaan we een SQL bestand maken om een standaard tabel mee te maken.
Dit bestand komt in de ```src``` map en heet ```create_table.sql```.

```sql
-- Maak de tabel 'Data', maar alleen als hij niet bestaat
CREATE TABLE IF NOT EXISTS Data (
    -- Maak een primaire sleutel van Id en laat hem automatisch oplopen
    Id INT PRIMARY KEY AUTO_INCREMENT,

    -- Maak een tekst veld om data op te slaan
    Info TEXT NOT NULL
);
```

Daarnaast stellen we in het dockerfile voor de database aan
dat het nieuwe gemaakte SQL bestand gekopieerd moet worden.
Dit bestand gaat gebruikt worden om de eerste tabel te maken binnen de database.

```dockerfile
FROM mysql:8.0

ENV MYSQL_ROOT_PASSWORD=root
ENV MYSQL_DATABASE=AwesomeDB

COPY ./src/create_table.sql /var/create_table.sql
```

Daarnaast gaan we een nieuw PHP bestand aanmaken: ```data.php```.
Dit wordt een pagina waarmee we contact maken met de database.
Er is een heel simpel formulier dat ons toestaat data naar de database te sturen.
Vervolgens wordt de data getoond in een tabel met de mogelijkheid om dit te verwijderen.

```php
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
        $db->query($sql) ;
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
                    <td>Acties</td>
                </tr>
            </thead>

            <tbody>
                <?php
                    // Haal alle data op uit de database
                    $sql = 'SELECT * FROM Data;';

                    // Ga over alle items heen en print ze naar de tabel
                    foreach ($db->query($sql) as $row) { ?>
                        <tr>
                        <td><?php echo $row['Id']; ?></td>;
                        <td><?php echo $row['Info']; ?></td>;
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
```

De laatste stap is uitvoeren van de containers met een volume.
Een volume is het koppelen van een map op onze computer aan een map in de container.
Dit zorgt ervoor dat data wordt bewaard, in plaats van weggegooid.

Maak eerst een map aan genaamd ```data``` voor de bestanden van MySQL.
Vervolgens gaan we tijdens het starten van de containers de mappen koppelen.
We maken het netwerk opnieuw aan, en bouwen de nieuwe versie van de database image.
Vervolgens starten we de container en koppelen we de mappen eraan.
Dit gebeurd met de volgende syntax: ```[hostmap]:[containermap]```.
**Linux en Mac gebruikers** moeten ```${PWD}```
vervangen worden door ```$PWD```.
Deze variabele bevat de huidige map van de terminal.

```shell
docker stop php-demo
docker stop dbserver
docker system prune

docker network create -d bridge php-network

docker build -f dockerfile.mysql -t dbserver .

docker run --name php-demo --network php-network -d -p 8080:80 -v ${PWD}/src/:/var/www/html/ php-demo
docker run --name dbserver --network php-network -d -v ${PWD}/data/:/var/lib/mysql/ dbserver

docker exec dbserver /bin/bash -c 'mysql -u root -proot AwesomeDB < /var/create_table.sql'
```

## Docker Compose

Leuk en aardig allemaal, maar het is best veel werk om nu het project te laten draaien.
Docker compose biedt hier de oplossing voor.
We maken een bestand aan in onze hoofdmap genaamd ```docker-compose.yaml```.
Hierin definieren we precies wat we voorheen in de console deden.
We geven aan welk netwerk we willen maken en welke containers met welke opties.
Dit is exact hetzelfde als er in de console deden, maar dan makkelijker.

```yaml
# De versie van docker compose
version: '3'

# De netwerken die aangemaakt moeten worden
networks:
  # De naam van het netwerk
  php-network:
    # De manier waarop het netwerk verbind
    driver: bridge

# Alles services die nodig zijn binnen de container
services:
  # De naam van de service
  dbserver:
    # De image die gebruikt moet worden
    image: mysql:8.0
    
    # Omgevingsvariabelen
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=AwesomeDB

    # Het netwerk om mee te verbinden
    networks:
      - php-network

    # De volume
    volumes:
      - ./data/:/var/lib/mysql/

    # Maak de tabel aan als deze niet bestaat
    entrypoint: [ "/bin/bash", "-c", "mysql -u root -proot AwesomeDB < /var/create_table.sql"]

  # Onze app
  php-demo:
    # Wacht met het starten van de demo totdat MySQL is gestart
    depends_on:
      - dbserver

    # Gebruik het docker bestand in deze map
    build: ./

    # Stel de poorten in
    ports: 
      - 8080:80

    # Stel het netwerk in
    networks:
      - php-network

    # De volume
    volumes:
      - ./src/:/var/www/html/
```

Vanaf dit punt kun je ```dockerfile.mysql``` verwijderen.
Deze wordt vervangen door ```docker-compose.yaml```.
Je hebt nog wel steeds het basis ```dockerfile``` nodig,
dit zorgt er namelijk voor dat docker onze project kan omzetten naar een image,
en vervolgens naar een container.

Om het projet nu uit te voeren gebruiken we één enkele opdracht: ```docker compose up```.
Om het project later af te sluiten, gebruiken we ```docker compose down```.
Dat is alles wat we nu nog nodig hebben om het project te draaien.

```shell
# Zorg ervoor dat alles uit is
docker stop php-demo
docker stop dbserver
docker system prune

# Start de omgeving
docker docker compose up -d

# Stop de omgeving
docker compose down
```
