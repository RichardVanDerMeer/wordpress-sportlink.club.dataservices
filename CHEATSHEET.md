# Sportlink API Cheat Sheet

Snelle referentie voor de meest gebruikte API calls.

## 🚀 Quick Start

```php
$api = Sportlink_API::create_from_settings();
```

## 🏆 Teams & Competitie

```php
// Alle teams
$teams = $api->get_teams();

// Teams filteren
$teams = $api->get_teams([
    'geslacht' => 'MAN',              // MAN, VROUW, ALLES
    'leeftijdscategorie' => 'SENIOREN', // SENIOREN, JUNIOREN, ALLES
    'spelsoort' => 'VELD'             // VELD, ZAAL, ALLES
]);

// Team samenstelling
$spelers = $api->get_team_indeling($teamcode, $lokaleteamcode);

// Team details
$details = $api->get_team_gegevens($teamcode, $lokaleteamcode);
```

## 📅 Programma & Uitslagen

```php
// Komende wedstrijden
$programma = $api->get_programma([
    'aantalregels' => 10,
    'aantaldagen' => 30,
    'teamcode' => 12345
]);

// Recente uitslagen
$uitslagen = $api->get_uitslagen([
    'aantalregels' => 10,
    'weekoffset' => -1,
    'teamcode' => 12345
]);

// Afgelastingen
$afgelast = $api->get_afgelastingen();
```

## 🎯 Wedstrijd Details

```php
// Basis informatie
$info = $api->get_wedstrijd_informatie($wedstrijdcode);

// Opstelling
$thuisteam = $api->get_wedstrijd_thuisteam($wedstrijdcode);
$uitteam = $api->get_wedstrijd_uitteam($wedstrijdcode);

// Locatie
$accommodatie = $api->get_wedstrijd_accommodatie($wedstrijdcode);

// Scheidsrechters
$officials = $api->get_wedstrijd_officials($wedstrijdcode);

// Statistieken
$stats = $api->get_wedstrijd_statistieken($wedstrijdcode);
```

## 📊 Standen

```php
// Poule stand
$stand = $api->get_poulestand($poulecode);

// Periode stand
$periode = $api->get_periodestand($poulecode, 1); // periode 1

// Alle poules
$poules = $api->get_poulelijst();

// Poule programma
$programma = $api->get_poule_programma($poulecode);

// Poule uitslagen
$uitslagen = $api->get_pouleuitslagen($poulecode);
```

## 🏛️ Club Info

```php
// Club gegevens
$club = $api->get_clubgegevens();

// Bestuur
$bestuur = $api->get_bestuur();

// Commissies
$commissies = $api->get_commissies();
$leden = $api->get_commissie_leden($commissiecode);
```

## 📋 Keuzelijsten (Filters)

```php
$geslachten = $api->get_keuzelijst_geslacht();
$leeftijden = $api->get_keuzelijst_leeftijdscategorieen();
$spelsoorten = $api->get_keuzelijst_spelsoorten();
$competities = $api->get_keuzelijst_competitiesoorten();
$periodes = $api->get_keuzelijst_competitieperiode();
```

## ⚠️ Error Handling Pattern

```php
$data = $api->get_teams();

if (is_wp_error($data)) {
    error_log('Sportlink Error: ' . $data->get_error_message());
    return '<p>Kon data niet ophalen</p>';
}

// Data gebruiken
foreach ($data as $item) {
    // ...
}
```

## 🎨 Output Escaping

```php
echo esc_html($team['teamnaam']);
echo '<a href="' . esc_url($url) . '">' . esc_html($text) . '</a>';
echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr($alt) . '">';
```

## 🔍 Input Sanitization

```php
$teamcode = isset($_GET['team']) ? intval($_GET['team']) : null;
$naam = isset($_POST['naam']) ? sanitize_text_field($_POST['naam']) : '';
$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
```

## 📝 Veelgebruikte Filters

### Alleen thuiswedstrijden

```php
$api->get_programma([
    'thuis' => 'JA',
    'uit' => 'NEE'
]);
```

### Deze week

```php
$api->get_programma([
    'weekoffset' => 0,
    'aantaldagen' => 7
]);
```

### Volgende maand

```php
$api->get_programma([
    'weekoffset' => 1,
    'aantaldagen' => 30
]);
```

### Alleen senioren

```php
$api->get_teams([
    'leeftijdscategorie' => 'SENIOREN'
]);
```

## 🔑 Belangrijke Velden

### Teams
- `teamcode` - Unieke team identificatie
- `lokaleteamcode` - Lokale team code
- `teamnaam` - Naam van het team
- `poulecode` - Poule identificatie
- `klassepoule` - Klasse en poule

### Wedstrijden
- `wedstrijdcode` - Unieke wedstrijd ID
- `wedstrijddatum` - Datum/tijd
- `thuisteam` / `uitteam` - Team namen
- `thuisteamid` / `uitteamid` - Team IDs
- `thuisscore` / `uitscore` - Uitslag
- `accommodatie` - Locatie naam
- `status` - Wedstrijd status

### Stand
- `positie` - Rangschikking
- `teamnaam` - Team naam
- `gespeeldewedstrijden` - Gespeeld
- `gewonnen` / `gelijk` / `verloren` - W/G/V
- `doelpuntenvoor` / `doelpuntentegen` - Goals
- `doelsaldo` - Saldo
- `punten` - Punten totaal
- `eigenteam` - Boolean (voor highlighting)

## 💡 Tips

✅ Cache is automatisch (30 min + 7 dagen stale cache)
✅ Circuit breaker actief bij API fouten
✅ Altijd `is_wp_error()` checken
✅ Escape alle output
✅ Sanitize alle input
✅ Gebruik `create_from_settings()` voor standaard config

## 📚 Meer Info

- Zie `API-INTERFACE.md` voor volledige documentatie
- Zie `examples/api-usage-examples.php` voor uitgebreide voorbeelden
- Check WordPress admin dashboard voor API status
