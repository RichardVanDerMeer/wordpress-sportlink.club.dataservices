# Sportlink API Interface

Een gestructureerde PHP interface voor de Sportlink Club.Dataservices API, speciaal ontworpen voor WordPress ontwikkelaars.

## 📋 Inhoudsopgave

- [Installatie](#installatie)
- [Basis Gebruik](#basis-gebruik)
- [Beschikbare Methoden](#beschikbare-methoden)
- [Voorbeelden](#voorbeelden)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)

## 🚀 Installatie

De API interface is automatisch beschikbaar zodra de Sportlink plugin actief is. Geen extra installatie nodig.

## 💡 Basis Gebruik

### Eenvoudig starten

```php
// Maak een API instance met standaard instellingen
$api = Sportlink_API::create_from_settings();

if (!$api) {
    // API niet geconfigureerd
    return;
}

// Haal teams op
$teams = $api->get_teams();

// Check voor fouten
if (is_wp_error($teams)) {
    echo 'Fout: ' . $teams->get_error_message();
    return;
}

// Gebruik de data
foreach ($teams as $team) {
    echo $team['teamnaam'] . '<br>';
}
```

### Handmatige instantie

```php
// Als je geen WordPress settings wilt gebruiken
$api = new Sportlink_API('jouw-client-id', 30); // 30 minuten cache
```

## 📚 Beschikbare Methoden

### Teams & Competitie

| Methode               | Beschrijving               | Parameters                              |
| --------------------- | -------------------------- | --------------------------------------- |
| `get_teams()`         | Haal alle teams op         | `$args` (optioneel)                     |
| `get_team_indeling()` | Haal team samenstelling op | `$teamcode`, `$lokaleteamcode`, `$args` |
| `get_team_gegevens()` | Haal team details op       | `$teamcode`, `$lokaleteamcode`          |
| `get_team_sponsors()` | Haal team sponsors op      | `$teamcode`, `$lokaleteamcode`          |

### Programma & Uitslagen

| Methode               | Beschrijving                  | Parameters |
| --------------------- | ----------------------------- | ---------- |
| `get_programma()`     | Haal wedstrijd programma op   | `$args`    |
| `get_uitslagen()`     | Haal uitslagen op             | `$args`    |
| `get_afgelastingen()` | Haal afgelaste wedstrijden op | `$args`    |

### Wedstrijd Details

| Methode                                  | Beschrijving                      | Parameters                       |
| ---------------------------------------- | --------------------------------- | -------------------------------- |
| `get_wedstrijd_informatie()`             | Wedstrijd basis info              | `$wedstrijdcode`                 |
| `get_wedstrijd_deelnemers()`             | Spelers/begeleiding               | `$wedstrijdcode`                 |
| `get_wedstrijd_thuisteam()`              | Thuisteam samenstelling           | `$wedstrijdcode`, `$toonlidfoto` |
| `get_wedstrijd_uitteam()`                | Uitteam samenstelling             | `$wedstrijdcode`, `$toonlidfoto` |
| `get_wedstrijd_officials()`              | Scheidsrechters                   | `$wedstrijdcode`                 |
| `get_wedstrijd_accommodatie()`           | Locatie details                   | `$wedstrijdcode`                 |
| `get_wedstrijd_kleedkamers()`            | Kleedkamer info                   | `$wedstrijdcode`                 |
| `get_wedstrijd_statistieken()`           | Wedstrijd statistieken            | `$wedstrijdcode`                 |
| `get_wedstrijd_historische_resultaten()` | Historische onderlinge resultaten | `$wedstrijdcode`                 |

### Standen

| Methode                 | Beschrijving            | Parameters                     |
| ----------------------- | ----------------------- | ------------------------------ |
| `get_poulestand()`      | Haal poule stand op     | `$poulecode`, `$args`          |
| `get_periodestand()`    | Haal periode stand op   | `$poulecode`, `$periodenummer` |
| `get_poule_indeling()`  | Haal poule indeling op  | `$poulecode`                   |
| `get_poule_programma()` | Haal poule programma op | `$poulecode`, `$args`          |
| `get_pouleuitslagen()`  | Haal poule uitslagen op | `$poulecode`, `$args`          |
| `get_poulelijst()`      | Haal alle poules op     | -                              |
| `get_teampoulelijst()`  | Haal poules van team op | `$teamcode`, `$lokaleteamcode` |

### Club Gegevens

| Methode                   | Beschrijving              | Parameters                       |
| ------------------------- | ------------------------- | -------------------------------- |
| `get_clubgegevens()`      | Volledige club informatie | -                                |
| `get_clublogo()`          | Club logo                 | -                                |
| `get_bestuur()`           | Bestuur gegevens          | -                                |
| `get_commissies()`        | Alle commissies           | -                                |
| `get_commissie_details()` | Commissie details         | `$commissiecode`                 |
| `get_commissie_leden()`   | Commissie leden           | `$commissiecode`, `$toonlidfoto` |

### Verenigingsactiviteiten

| Methode                         | Beschrijving          | Parameters     |
| ------------------------------- | --------------------- | -------------- |
| `get_verenigingsactiviteiten()` | Kalender activiteiten | `$args`        |
| `get_verjaardagen()`            | Komende verjaardagen  | `$aantaldagen` |

### Vrijwilligers

| Methode                    | Beschrijving            | Parameters                        |
| -------------------------- | ----------------------- | --------------------------------- |
| `get_vrijwilligerstaken()` | Alle vrijwilligerstaken | -                                 |
| `get_vrijwilligers()`      | Vrijwilligers voor taak | `$vrijwilligerstaakcode`, `$args` |

### Scheidsrechters & Trainingen

| Methode                              | Beschrijving     | Parameters                     |
| ------------------------------------ | ---------------- | ------------------------------ |
| `get_scheidsrechtersaanstellingen()` | SR aanstellingen | `$args`                        |
| `get_trainingenlijst()`              | Alle trainingen  | -                              |
| `get_trainingdetails()`              | Training details | `$trainingid`, `$aantaldagen`  |
| `get_team_trainingenlijst()`         | Team trainingen  | `$teamcode`, `$lokaleteamcode` |

### Keuzelijsten

Alle keuzelijsten voor filters en dropdowns:

```php
$api->get_keuzelijst_competitiesoorten()
$api->get_keuzelijst_competitieperiode()
$api->get_keuzelijst_spelsoorten()
$api->get_keuzelijst_leeftijdscategorieen()
$api->get_keuzelijst_teamsoorten()
$api->get_keuzelijst_geslacht()
$api->get_keuzelijst_dagsoorten()
$api->get_keuzelijst_wedstrijdtypes()
$api->get_keuzelijst_sorteervolgordes()
$api->get_keuzelijst_boolean()
$api->get_keuzelijst_periodenummers($poulecode)
```

## 🎯 Voorbeelden

### Teams filteren op geslacht

```php
$api = Sportlink_API::create_from_settings();

$teams = $api->get_teams([
    'geslacht' => 'VROUW',
    'leeftijdscategorie' => 'SENIOREN'
]);

if (!is_wp_error($teams)) {
    foreach ($teams as $team) {
        echo $team['teamnaam'] . '<br>';
    }
}
```

### Volgende 5 wedstrijden

```php
$api = Sportlink_API::create_from_settings();

$programma = $api->get_programma([
    'aantalregels' => 5,
    'aantaldagen' => 30,
    'sorteervolgorde' => 'datum'
]);

if (!is_wp_error($programma)) {
    foreach ($programma as $wedstrijd) {
        echo $wedstrijd['datum'] . ' - ';
        echo $wedstrijd['thuisteam'] . ' vs ' . $wedstrijd['uitteam'];
        echo '<br>';
    }
}
```

### Poule stand met eigen team gemarkeerd

```php
$api = Sportlink_API::create_from_settings();

$stand = $api->get_poulestand(12345); // vervang met jouw poulecode

if (!is_wp_error($stand)) {
    echo '<table>';
    echo '<tr><th>Pos</th><th>Team</th><th>Pnt</th></tr>';

    foreach ($stand as $team) {
        $class = isset($team['eigenteam']) && $team['eigenteam'] ? ' class="eigenteam"' : '';
        echo '<tr' . $class . '>';
        echo '<td>' . $team['positie'] . '</td>';
        echo '<td>' . $team['teamnaam'] . '</td>';
        echo '<td>' . $team['punten'] . '</td>';
        echo '</tr>';
    }

    echo '</table>';
}
```

### Wedstrijd details pagina

```php
$api = Sportlink_API::create_from_settings();

$wedstrijdcode = 123456; // Haal uit URL parameter

// Haal verschillende data op
$info = $api->get_wedstrijd_informatie($wedstrijdcode);
$thuisteam = $api->get_wedstrijd_thuisteam($wedstrijdcode);
$uitteam = $api->get_wedstrijd_uitteam($wedstrijdcode);

if (!is_wp_error($info)) {
    // Toon wedstrijd info
    echo '<h2>' . $info['wedstrijdinformatie']['thuisteam'] . ' - ';
    echo $info['wedstrijdinformatie']['uitteam'] . '</h2>';

    echo '<p>Datum: ' . $info['wedstrijdinformatie']['wedstrijddatum'] . '</p>';
    echo '<p>Tijd: ' . $info['wedstrijdinformatie']['aanvangstijd'] . '</p>';

    // Uitslag indien bekend
    if (!empty($info['wedstrijdinformatie']['thuisscore'])) {
        echo '<h3>Uitslag: ' . $info['wedstrijdinformatie']['thuisscore'];
        echo ' - ' . $info['wedstrijdinformatie']['uitscore'] . '</h3>';
    }
}

// Toon thuisteam opstelling
if (!is_wp_error($thuisteam)) {
    echo '<h3>Thuisteam opstelling</h3>';
    echo '<ul>';
    foreach ($thuisteam as $speler) {
        echo '<li>' . $speler['naam'] . ' (' . $speler['rol'] . ')</li>';
    }
    echo '</ul>';
}
```

### Dropdown filter voor teams

```php
$api = Sportlink_API::create_from_settings();

// Haal keuzelijsten op
$geslachten = $api->get_keuzelijst_geslacht();
$leeftijdscategorieen = $api->get_keuzelijst_leeftijdscategorieen();

echo '<form method="get">';

// Geslacht dropdown
if (!is_wp_error($geslachten)) {
    echo '<select name="geslacht">';
    foreach ($geslachten as $item) {
        echo '<option value="' . $item['waarde'] . '">' . $item['omschrijving'] . '</option>';
    }
    echo '</select>';
}

// Leeftijd dropdown
if (!is_wp_error($leeftijdscategorieen)) {
    echo '<select name="leeftijd">';
    foreach ($leeftijdscategorieen as $item) {
        echo '<option value="' . $item['waarde'] . '">' . $item['omschrijving'] . '</option>';
    }
    echo '</select>';
}

echo '<button type="submit">Filter</button>';
echo '</form>';

// Toon gefilterde teams
if (isset($_GET['geslacht'])) {
    $teams = $api->get_teams([
        'geslacht' => sanitize_text_field($_GET['geslacht']),
        'leeftijdscategorie' => sanitize_text_field($_GET['leeftijd'])
    ]);

    // ... toon teams ...
}
```

## ⚠️ Error Handling

De API retourneert altijd een `WP_Error` object bij fouten. Check dit **ALTIJD**:

```php
$data = $api->get_teams();

if (is_wp_error($data)) {
    // Er is een fout opgetreden
    $error_message = $data->get_error_message();

    // Log voor debugging
    error_log('Sportlink API Error: ' . $error_message);

    // Toon gebruiksvriendelijke melding
    echo '<p class="error">Kon teams niet ophalen. Probeer het later opnieuw.</p>';
    return;
}

// Data is OK, gebruik het
foreach ($data as $item) {
    // ...
}
```

### Mogelijke fouten

- **API niet geconfigureerd**: Client ID ontbreekt
- **Network error**: Sportlink server niet bereikbaar
- **Invalid response**: Ongeldige API response
- **Cache error**: Probleem met WordPress transients
- **Circuit breaker open**: API heeft te veel fouten, fallback naar oude cache

## ✅ Best Practices

### 1. Altijd error checking

```php
$data = $api->get_teams();
if (is_wp_error($data)) {
    // Handle error
    return;
}
```

### 2. Escape alle output

```php
echo esc_html($team['teamnaam']);
echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr($alt) . '">';
```

### 3. Sanitize user input

```php
$teamcode = isset($_GET['teamcode']) ? intval($_GET['teamcode']) : null;
$naam = isset($_POST['naam']) ? sanitize_text_field($_POST['naam']) : '';
```

### 4. Gebruik default parameters

```php
$atts = shortcode_atts([
    'teamcode' => null,
    'aantaldagen' => 30
], $atts);
```

### 5. Cache is automatisch

Je hoeft niets extra's te doen. De API gebruikt automatisch:

- WordPress Transients (30 minuten standaard)
- Stale cache fallback (7 dagen)
- Circuit breaker pattern (bij API fouten)

### 6. Type hints gebruiken

```php
/**
 * @param Sportlink_API $api
 * @return array|WP_Error
 */
function get_my_teams(Sportlink_API $api) {
    return $api->get_teams();
}
```

### 7. Null checks

```php
$api = Sportlink_API::create_from_settings();
if (!$api) {
    return '<p>Sportlink niet geconfigureerd</p>';
}
```

## 🔧 Configuratie Parameters

### Teams ophalen

```php
$api->get_teams([
    'competitieperiode' => '2024-2025',    // Specifiek seizoen
    'teamsoort' => 'STANDAARD',            // ALLES, STANDAARD, ZAALVOETBAL
    'geslacht' => 'MAN',                   // ALLES, MAN, VROUW
    'spelsoort' => 'VELD',                 // ALLES, VELD, ZAAL
    'competitiesoort' => 'REGULIER',       // ALLES, REGULIER, BEKER
    'leeftijdscategorie' => 'SENIOREN',    // ALLES, SENIOREN, JUNIOREN
    'gebruiklokaleteamgegevens' => 'NEE'   // JA of NEE
]);
```

### Programma ophalen

```php
$api->get_programma([
    'teamcode' => 12345,                   // Specifiek team
    'lokaleteamcode' => 67890,             // Lokale team code
    'aantalregels' => 10,                  // Max aantal resultaten
    'aantaldagen' => 30,                   // Dagen vooruit
    'weekoffset' => 0,                     // Week offset (0 = huidige week)
    'eigenwedstrijden' => 'JA',            // JA of NEE
    'thuis' => 'JA',                       // Thuis wedstrijden
    'uit' => 'JA',                         // Uit wedstrijden
    'sorteervolgorde' => 'datum',          // datum, team, etc.
    'spelsoort' => 'ALLES',
    'competitiesoort' => 'ALLES',
    'dagsoort' => 'ALLES',                 // ZATERDAG, ZONDAG, etc.
    'leeftijdscategorie' => 'ALLES'
]);
```

## 📖 Meer Voorbeelden

Zie `examples/api-usage-examples.php` voor uitgebreide voorbeelden van:

- Shortcodes maken
- Widgets bouwen
- Custom queries
- Filter formulieren
- Robuuste error handling
- En meer!

## 🆘 Support

Voor vragen of problemen:

1. Check de API specificatie in de root map
2. Bekijk de voorbeelden in `examples/`
3. Controleer de WordPress debug log voor API fouten
4. Bekijk het Sportlink dashboard widget voor API status

## 📄 Licentie

GPL v2 or later
