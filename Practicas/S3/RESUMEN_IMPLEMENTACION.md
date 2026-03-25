# Memòria de la Pràctica S3 - Sistema de reserves d'hotel

## 1. Objectiu de la pràctica
L'objectiu del projecte és desenvolupar una aplicació web de reserves d'hotel amb una part pública (portada) i una part funcional (formulari i confirmació), utilitzant PHP, MySQL, Bootstrap i validació de dades tant al client com al servidor.

Aquesta memòria descriu el funcionament del projecte des del punt de vista de l'usuari i de la lògica de negoci, de manera que es pugui entendre el sistema sense necessitat de revisar el codi font.

---

## 2. Característiques principals del lloc web

### 2.1. Estructura general
El lloc web està organitzat en tres pantalles principals:

1. `index.html` (portada)
2. `client.php` (formulari de reserva)
3. `server.php` (processament i confirmació)

### 2.2. Disseny i usabilitat
El lloc utilitza Bootstrap per garantir una interfície responsive i coherent en dispositius d'escriptori i mòbils.

Característiques d'interfície:

1. Barra de navegació comuna entre pàgines.
2. Jerarquia visual clara (títols, seccions, botons d'acció).
3. Missatges d'error i d'èxit diferenciats visualment.
4. Navegació de retorn per repetir una reserva o tornar a la portada.

### 2.3. Integració amb base de dades
L'aplicació treballa amb la base de dades `world` (MySQL), concretament amb:

1. Taula `cities` (ja existent a `world.sql`) per obtenir ciutats de destinació.
2. Taula `clients_reserves` (creada automàticament si no existeix) per guardar reserves.
3. Taula `hotels` (part opcional) per mostrar hotels disponibles al formulari.

---

## 3. Funcionalitats implementades

### 3.1. Funcionalitats de la portada (`index.html`)

1. Presentació comercial del servei de reserves.
2. Enllaços directes a la pàgina de reserva.
3. Bloc de propietats destacades (targetes amb imatge i descripció).
4. Informació de valor per a l'usuari (preus, cancel·lació, atenció, etc.).
5. Enllaços externs de cerca (Google i Wikipedia).

### 3.2. Funcionalitats del formulari (`client.php`)

El formulari permet introduir totes les dades necessàries per fer una reserva:

1. Nom complet.
2. Correu electrònic.
3. Telèfon.
4. Ciutat de destinació (carregada des de base de dades).
5. Data d'entrada.
6. Data de sortida.
7. Nombre de persones.
8. Tipus d'habitació (Individual, Doble, Suite).
9. Comentaris opcionals.

A més, implementa:

1. Validació client-side en temps real (feedback immediat).
2. Restricció de dates per evitar reserves en passat.
3. Enviament de dades amb mètode `POST` a `server.php`.
4. Visualització opcional d'una taula d'hotels amb serveis i preu per nit.

### 3.3. Funcionalitats de processament (`server.php`)

Quan l'usuari envia el formulari:

1. Es comprova que la petició arribi per `POST`.
2. Es validen totes les dades al servidor (segona capa de control).
3. Es comprova que la ciutat seleccionada existeix realment a `cities`.
4. Es calculen les nits de l'estada.
5. Es calcula el preu total segons tipus d'habitació i nits.
6. Es genera un codi únic de reserva.
7. S'intenta guardar la reserva a `clients_reserves`.
8. Es mostra una pantalla de confirmació amb el resum.
9. Es mostren també les darreres reserves registrades (consulta SQL de lectura).

Si hi ha errors:

1. Es presenta una llista clara d'errors de validació.
2. Es permet tornar al formulari per corregir les dades.

---

## 4. Flux de funcionament de l'aplicació

1. L'usuari entra a la portada (`index.html`).
2. Clica "Reservar" i accedeix a `client.php`.
3. Omple el formulari.
4. El navegador valida camps bàsics abans d'enviar.
5. El formulari s'envia per `POST` a `server.php`.
6. El servidor torna a validar i saneja les dades.
7. Si tot és correcte, calcula i desa la reserva.
8. Es mostra confirmació amb codi de reserva i informació completa.

Aquest flux garanteix una experiència guiada per a l'usuari i una capa de control robusta al backend.

---

## 5. Adaptació del codi d'exemple de teoria a l'aplicació web

A continuació s'explica com s'han adaptat els patrons treballats a les transparències de teoria a un cas real de reserva d'hotels.

### 5.1. De formulari genèric a formulari de negoci

Del model teòric de formulari HTML s'ha passat a un formulari orientat a un procés real de reserva, afegint camps propis del domini:

1. Dates d'entrada/sortida.
2. Nombre de persones.
3. Tipus d'habitació.
4. Comentaris de client.

### 5.2. De validació bàsica a validació en dues capes

A la teoria es mostra l'ús de REGEXP i validacions bàsiques. En aquesta pràctica s'ha aplicat en dues capes:

1. Capa client (JavaScript): millora l'experiència d'usuari.
2. Capa servidor (PHP): garanteix seguretat i consistència.

Això segueix la bona pràctica explicada a classe: no confiar mai exclusivament en la validació del navegador.

### 5.3. De tractament simple de dades a persistència real

En lloc de només mostrar dades enviades, s'ha ampliat el patró teòric perquè:

1. Es consulti informació real de ciutats des de MySQL.
2. Es guardin reserves en una taula pròpia.
3. Es recuperin reserves recents per mostrar resultats.

Aquesta adaptació converteix un exercici de formulari en una aplicació amb persistència i traçabilitat.

### 5.4. De comprovacions a lògica de negoci

S'ha afegit lògica que no és només validació sintàctica:

1. Càlcul de nits segons les dates.
2. Càlcul de preu segons categoria d'habitació.
3. Generació de codi de reserva únic.

Això reflecteix l'evolució des d'un exemple acadèmic cap a una funcionalitat pròxima a un sistema real.

### 5.5. De pàgina aïllada a experiència completa

Els exemples de teoria acostumen a estar centrats en una sola pàgina o concepte. En aquesta pràctica s'ha connectat tot en un flux complet:

1. Landing inicial.
2. Formulari.
3. Processament.
4. Confirmació/gestió d'errors.

El resultat és una experiència de punta a punta, amb coherència visual i funcional.

### 5.6. Fragments de codi destacats del projecte

Per reforçar la comprensió, s'inclouen alguns fragments reals i representatius.

#### Fragment 1: Enviament del formulari per POST (client.php)

Aquest fragment mostra com es connecta el formulari amb la pàgina de processament.

```html
<form id="formReserva" action="server.php" method="POST" novalidate>
	<!-- Camps del formulari -->
</form>
```

#### Fragment 2: Validació amb expressions regulars al client (client.php)

Abans d'enviar, el navegador valida format de nom, email i telèfon.

```javascript
var regexNom = /^[A-Za-zÀ-ÿ\s]{3,50}$/;
var regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
var regexTelefon = /^(\+34|0034)?[\s]?[6-9][0-9]{8}$/;
```

#### Fragment 3: Verificació de mètode i recepció de dades (server.php)

El servidor només processa si la petició arriba correctament per POST.

```php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$nom = $_POST["nom"];
	$email = $_POST["email"];
	$telefon = $_POST["telefon"];
}
```

#### Fragment 4: Inserció segura amb sentència preparada (server.php)

La reserva es desa a base de dades amb una inserció preparada per millorar la robustesa.

```php
$insertSql = "INSERT INTO clients_reserves
	(reservation_code, client_name, email, phone, city_id, city_name, checkin_date, checkout_date, nights, guests, room_type, comments, total_price)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmtInsert = $conn->prepare($insertSql);
```

#### Fragment 5: Generació de codi únic de reserva (server.php)

Cada reserva rep un identificador propi per a consultes futures.

```php
$codiReserva = "RES-" . strtoupper(substr(md5(uniqid()), 0, 8));
```

---

## 6. Seguretat i robustesa aplicades

S'han incorporat mesures per augmentar la qualitat del projecte:

1. Sanitització d'entrades (`trim`, `stripslashes`, `htmlspecialchars`).
2. Validació de mètode HTTP (`POST`).
3. Validació forta de ciutat contra base de dades (no només text lliure).
4. Ús de sentències preparades en insercions SQL.
5. Missatges de diagnòstic quan falla la connexió a BD.

Aquestes decisions redueixen riscos típics d'aplicacions web (dades incorrectes, XSS, errors d'integritat).

---

## 7. Decisions de disseny rellevants

1. Separació d'estils en fitxers CSS específics (`styles.css`, `client.css`, `server.css`).
2. Reutilització de components Bootstrap per accelerar maquetació i mantenir coherència.
3. Estructura modular en tres pantalles per separar responsabilitats.
4. Feedback clar a l'usuari en cada estat (error, èxit, advertència).

---

## 8. Conclusió
La pràctica compleix els requisits demanats i els amplia amb funcionalitats opcionals i millores de robustesa.

En concret:

1. S'han implementat les dues pàgines PHP requerides i el flux complet de reserva.
2. S'han aplicat formularis complets amb controls variats i validació amb REGEXP.
3. S'ha utilitzat correctament l'enviament i recepció de dades amb `POST`.
4. S'ha integrat la base de dades per consultar ciutats i desar reserves.
5. S'ha adaptat el codi d'exemple de teoria a una aplicació real i funcional.

Per tant, el projecte no només demostra els conceptes de la sessió, sinó que també mostra una aplicació pràctica amb lògica de negoci, persistència de dades i criteris bàsics de qualitat web.
