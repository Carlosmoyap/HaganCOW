# RESUMEN D'IMPLEMENTACIÓ - PRÀCTICA S3
## Sistema de Reserves d'Hotel amb PHP

---

## ✅ VERIFICACIÓ DE REQUISITS

### **Requisit 1: Dues pàgines web PHP (8pts)**
✅ **COMPLERT**

#### **client.php** - Pàgina de Reserva
- Pàgina PHP que mostra el formulari per efectuar una reserva d'hotel
- Permet a l'usuari seleccionar la ciutat de destinació
- Inclou validació client-side amb JavaScript i expressions regulars
- Envia les dades per POST a server.php

#### **server.php** - Pàgina de Confirmació
- Pàgina PHP que rep i processa les dades de la reserva
- Valida les dades al servidor amb expressions regulars
- Mostra el resultat de la reserva amb tots els detalls
- Genera un codi únic de reserva
- Calcula el preu total segons tipus d'habitació i nits

---

### **Requisit 2: Formularis, botons i controls amb REGEXP (8pts)**
✅ **COMPLERT**

#### **Tipus de Controls Implementats:**
1. **Input text** - Nom complet del client
2. **Input email** - Correu electrònic
3. **Input tel** - Telèfon de contacte
4. **Select** - Ciutat de destinació (9 ciutats europees)
5. **Input date** - Data d'entrada i sortida
6. **Select** - Nombre de persones (1-5)
7. **Radio buttons** - Tipus d'habitació (Individual, Doble, Suite)
8. **Textarea** - Comentaris opcionals
9. **Button submit** - Botó per confirmar la reserva

#### **Validació amb Expressions Regulars (REGEXP):**

##### **Client-side (JavaScript):**
```javascript
// Nom: només lletres i espais, 3-50 caràcters
var regexNom = /^[A-Za-zÀ-ÿ\s]{3,50}$/;

// Email: format estàndard amb @ i domini
var regexEmail = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

// Telèfon: format espanyol 6XX-9XX amb/sense prefix +34
var regexTelefon = /^(\+34|0034)?[\s]?[6-9][0-9]{8}$/;
```

##### **Server-side (PHP):**
```php
// Les mateixes expressions regulars aplicades al servidor
$regexNom = "/^[A-Za-zÀ-ÿ\s]{3,50}$/";
$regexEmail = "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/";
$regexTelefon = "/^(\+34|0034)?[\s]?[6-9][0-9]{8}$/";

// Aplicació amb preg_match()
if (!preg_match($regexNom, $nom)) {
    $errors[] = "El nom només pot contenir lletres i espais";
}
```

#### **Validació en Temps Real:**
- Event `blur` detecta quan l'usuari deixa un camp
- Mostra missatges d'error específics per cada camp
- Afegeix classe CSS `.has-error` per resaltar camps incorrectes
- Validació final abans d'enviar el formulari

---

### **Requisit 3: Enviament i recepció de dades amb POST/GET (8pts)**
✅ **COMPLERT**

#### **Enviament amb POST (client.php):**
```html
<form id="formReserva" action="server.php" method="POST" novalidate>
    <!-- Camps del formulari -->
</form>
```

#### **Recepció amb POST (server.php):**
```php
// Verificar mètode d'enviament
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Accedir a les dades enviades
    $nom = $_POST["nom"];
    $email = $_POST["email"];
    $telefon = $_POST["telefon"];
    $ciutat = $_POST["ciutat"];
    // ... més camps
}
```

#### **Seguretat en el Processament:**
```php
// Funció de neteja i sanitització
function neteja_dades($dada) {
    $dada = trim($dada);              // Elimina espais
    $dada = stripslashes($dada);      // Elimina barres
    $dada = htmlspecialchars($dada);  // Prevé XSS
    return $dada;
}
```

---

## 📁 ESTRUCTURA DEL PROJECTE

```
S3/
├── index.html              # Pàgina principal amb enllaços
├── client.php             # Formulari de reserva
├── server.php             # Processament i confirmació
├── css/
│   ├── styles.css         # Estils generals
│   ├── client.css         # Estils de client.php
│   └── server.css         # Estils de server.php
├── bootstrap-3.3.7-dist/  # Framework Bootstrap
├── jquery-ui-1.12.1/      # jQuery per validació
└── images/                # Imatges de la web
```

---

## 🔄 FLUX DE L'APLICACIÓ

1. **index.html** → L'usuari accedeix a la pàgina principal
2. **Click "Reservar Ahora"** → Redirigeix a client.php
3. **client.php** → L'usuari completa el formulari
4. **Validació Client-side** → JavaScript valida amb REGEXP en temps real
5. **Submit (POST)** → Envia dades a server.php
6. **server.php** → Rep dades per POST
7. **Validació Server-side** → PHP valida amb preg_match() i REGEXP
8. **Processa** → Neteja dades, calcula preu, genera codi reserva
9. **Mostra Resultat** → Confirmació amb detalls o errors detectats

---

## 🛡️ SEGURETAT IMPLEMENTADA

### **Doble Validació:**
- **Client-side**: Validació JavaScript per feedback immediat
- **Server-side**: Validació PHP per seguretat real
- **Mai confiar només en validació client** (es pot saltar)

### **Sanitització de Dades:**
```php
trim()            // Elimina espais innecessaris
stripslashes()    // Prevé injecció de codi
htmlspecialchars() // Converteix < > " ' & a entitats HTML
```

### **Validació de Mètode HTTP:**
```php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Només processar si és POST
}
```

---

## 🎨 TECNOLOGIES UTILITZADES

### **Frontend:**
- HTML5 - Estructura semàntica
- CSS3 - Estils personalitzats
- Bootstrap 3.3.7 - Disseny responsive
- jQuery - Validació client-side i interactivitat

### **Backend:**
- PHP - Processament al servidor
- Expressions Regulars (REGEXP) - Validació de dades

### **Patrons de Disseny:**
- MVC simplificat (Vista: client.php, Controlador: server.php)
- Separació de concerns (HTML, CSS i PHP en fitxers separats)
- Progressive Enhancement (funciona sense JavaScript amb validació PHP)

---

## 📊 FUNCIONALITATS ADDICIONALS

### **Càlcul Automàtic:**
- Nombre de nits (diferència entre dates)
- Preu total (tipus habitació × nits)

### **Generació de Codi Únic:**
```php
$codiReserva = "RES-" . strtoupper(substr(md5(uniqid()), 0, 8));
// Exemple: RES-A3F7B2C9
```

### **Gestió d'Errors:**
- Array d'errors per acumular tots els problemes
- Missatges específics per cada tipus d'error
- Interfície visual diferenciada (errors en vermell, èxit en verd)

### **Navegació:**
- Barra de navegació consistent en totes les pàgines
- Enllaços per tornar al formulari o a la pàgina principal

---

## 🧪 COM PROVAR L'APLICACIÓ

### **Requisits:**
- Servidor Apache amb PHP (XAMPP, WAMP, etc.)
- Navegador web modern

### **Passos:**
1. Copiar la carpeta S3 a `C:\xampp\htdocs\`
2. Iniciar Apache des del panell de XAMPP
3. Accedir a `http://localhost/S3/index.html`
4. Click a "Reservar Ahora"
5. Completar el formulari
6. Enviar i veure la confirmació

### **Proves de Validació:**
- Intentar enviar camp nom amb números → Error
- Introduir email sense @ → Error
- Telèfon amb format incorrecte → Error
- Data sortida anterior a entrada → Error
- Dades correctes → Confirmació amb codi de reserva

---

## 📝 CONCLUSIONS

✅ Tots els requisits de la pràctica han estat implementats correctament:
1. Dues pàgines PHP (client.php i server.php)
2. Formularis amb múltiples controls i validació REGEXP
3. Enviament i recepció de dades amb POST

✅ Implementacions addicionals:
- Organització del codi amb comentaris detallats
- Separació de CSS en arxius independents
- Validació doble (client i servidor)
- Seguretat amb sanitització de dades
- Interfície responsive amb Bootstrap
- Generació de codi de reserva únic
- Càlcul automàtic de preus

✅ Compleix amb bones pràctiques de desenvolupament web:
- Codi net i comentat
- Estructura organitzada
- Seguretat implementada
- Experiència d'usuari optimitzada
