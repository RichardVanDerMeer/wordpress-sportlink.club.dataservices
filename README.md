# Sportlink KNVB Club.Dataservices Plugin

WordPress plugin voor het tonen van wedstrijdprogramma's, uitslagen, standen en meer vanuit de Sportlink Club.Dataservices API.

**Versie:** 1.2.0
**Auteur:** Richard van der Meer
**Licentie:** GPL v2 or later

## ✨ Nieuwe Features (v1.2.0)

### 🎯 API Interface Klasse

Een volledig nieuwe, gestructureerde PHP interface voor de Sportlink API:

- **Type-safe methoden** voor alle API endpoints
- **Autocomplete** in je IDE voor alle beschikbare functies
- **Duidelijke documentatie** met parameter uitleg
- **Error handling** ingebouwd
- **Kant-en-klare voorbeelden** die je direct kunt gebruiken

### 🛡️ Verbeterde Beveiliging

- Input sanitization op alle user input
- Output escaping in templates
- Nonce verificatie voor admin acties
- API parameter validatie met whitelisting

### ⚡ Prestatie Optimalisaties

- WordPress Transients API voor efficiënte caching
- Request-level caching voorkomt dubbele API calls
- Lazy loading van team data
- Geoptimaliseerde HTTP requests

### 🔒 Resilience Features

- Circuit breaker pattern (3-failure threshold)
- Stale cache fallback (tot 7 dagen)
- Timeout optimization (8 seconden)
- Dashboard widget voor API status monitoring

## 📦 Installatie

1. Upload de plugin map naar `/wp-content/plugins/`
2. Activeer de plugin via WordPress Admin
3. Ga naar Instellingen → Sportlink
4. Vul je Sportlink Client ID in
5. Stel cache tijd in (standaard: 30 minuten)
6. Sla de instellingen op

## 🚀 Snel Starten

### Methode 1: Bestaande Shortcodes (Backwards Compatible)

De oude shortcodes werken nog steeds:

```
[sportlink type="programma"]
[sportlink type="uitslagen"]
[sportlink type="stand" wedstrijd="123"]
```

### Methode 2: Nieuwe API Interface (Aanbevolen)

Gebruik de nieuwe API klasse voor meer flexibiliteit:

```php
// Maak API instance
$api = Sportlink_API::create_from_settings();

// Haal teams op
$teams = $api->get_teams([
    'geslacht' => 'MAN',
    'leeftijdscategorie' => 'SENIOREN'
]);

if (!is_wp_error($teams)) {
    foreach ($teams as $team) {
        echo $team['teamnaam'] . '<br>';
    }
}
```

### Methode 3: Quick Start Shortcodes

Kopieer kant-en-klare shortcodes uit `examples/quick-start-shortcodes.php`:

```
[sportlink_teams geslacht="MAN"]
[sportlink_programma teamcode="123" aantal="5"]
[sportlink_uitslagen teamcode="123"]
[sportlink_stand poulecode="456"]
[sportlink_clubinfo]
```

## 📚 Documentatie

### Voor Ontwikkelaars

- **[API Interface Documentatie](API-INTERFACE.md)** - Volledige API referentie
- **[Cheat Sheet](CHEATSHEET.md)** - Snelle referentie voor veelgebruikte calls
- **[Voorbeelden](examples/)** - Praktische code voorbeelden

### API Methoden (Hoogtepunten)

```php
// Teams & Competitie
$api->get_teams($args)
$api->get_team_indeling($teamcode, $lokaleteamcode)
$api->get_team_gegevens($teamcode, $lokaleteamcode)

// Programma & Uitslagen
$api->get_programma($args)
$api->get_uitslagen($args)
$api->get_afgelastingen($args)

// Wedstrijd Details
$api->get_wedstrijd_informatie($wedstrijdcode)
$api->get_wedstrijd_thuisteam($wedstrijdcode)
$api->get_wedstrijd_uitteam($wedstrijdcode)

// Standen
$api->get_poulestand($poulecode)
$api->get_poulelijst()

// Club Gegevens
$api->get_clubgegevens()
$api->get_bestuur()
$api->get_commissies()

// En 50+ andere methoden...
```

Zie [API-INTERFACE.md](API-INTERFACE.md) voor alle beschikbare methoden.

## 🎯 Voorbeelden

### Team Lijst met Filter

```php
$api = Sportlink_API::create_from_settings();

$teams = $api->get_teams([
    'geslacht' => 'VROUW',
    'leeftijdscategorie' => 'SENIOREN',
    'spelsoort' => 'VELD'
]);

if (!is_wp_error($teams)) {
    echo '<ul>';
    foreach ($teams as $team) {
        echo '<li>' . esc_html($team['teamnaam']) . '</li>';
    }
    echo '</ul>';
}
```

### Komende 5 Wedstrijden

```php
$api = Sportlink_API::create_from_settings();

$programma = $api->get_programma([
    'aantalregels' => 5,
    'aantaldagen' => 30,
    'teamcode' => 12345
]);

if (!is_wp_error($programma)) {
    foreach ($programma as $wedstrijd) {
        echo $wedstrijd['datum'] . ' - ' . $wedstrijd['wedstrijd'] . '<br>';
    }
}
```

### Poule Stand

```php
$api = Sportlink_API::create_from_settings();

$stand = $api->get_poulestand(67890);

if (!is_wp_error($stand)) {
    echo '<table>';
    foreach ($stand as $team) {
        $class = ($team['eigenteam'] ?? false) ? ' class="eigenteam"' : '';
        echo '<tr' . $class . '>';
        echo '<td>' . esc_html($team['positie']) . '</td>';
        echo '<td>' . esc_html($team['teamnaam']) . '</td>';
        echo '<td>' . esc_html($team['punten']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
```

Meer voorbeelden in de `examples/` map.

## ⚠️ Error Handling

**Belangrijk:** Check altijd op fouten!

```php
$data = $api->get_teams();

if (is_wp_error($data)) {
    // Log fout
    error_log('Sportlink Error: ' . $data->get_error_message());

    // Toon gebruiksvriendelijke melding
    echo '<p>Kon teams niet ophalen. Probeer het later opnieuw.</p>';
    return;
}

// Gebruik data
foreach ($data as $item) {
    // ...
}
```

## 🔧 Configuratie

### WordPress Admin

Ga naar **Instellingen → Sportlink**:

- **Client ID**: Je Sportlink API client ID (verplicht)
- **Cache Tijd**: Hoe lang data gecached wordt (standaard: 30 minuten)
- **Cache Leegmaken**: Knop om handmatig cache te legen

### Dashboard Widget

Bekijk de API status in je WordPress dashboard:

- ✓ Operationeel (groen)
- ⚡ Instabiel (geel)
- ⚠️ Niet beschikbaar (rood)

## 🎨 Templates

De plugin gebruikt Gamajo Template Loader voor flexibele templates.

### Template Bestanden

- `templates/fixtures.php` - Wedstrijd programma
- `templates/standings.php` - Volledige stand
- `templates/standings-small.php` - Compacte stand

### Custom Templates

Kopieer een template naar je theme:

```
/wp-content/themes/jouw-theme/sportlink/standings.php
```

De plugin gebruikt automatisch je custom template.

## 🔒 Beveiliging

De plugin implementeert WordPress security best practices:

- ✅ Input sanitization (sanitize_key, sanitize_text_field)
- ✅ Output escaping (esc_html, esc_attr, esc_url)
- ✅ Nonce verificatie voor admin acties
- ✅ API parameter whitelisting
- ✅ Capability checks (manage_options)

## ⚡ Performance

### Caching Strategie

1. **Transients Cache** (30 min): Verse data voor normale situatie
2. **Stale Cache** (7 dagen): Backup data bij API problemen
3. **Circuit Breaker**: Automatische fallback bij 3+ fouten
4. **Request Cache**: Voorkomt dubbele API calls binnen één pageview

### Cache Beheer

- **Automatisch**: Expired transients worden verwijderd
- **Handmatig**: Admin knop "Cache leegmaken"
- **Per API key**: Cache wordt geleegd bij API key wijziging

## 📋 Requirements

- **WordPress**: 5.0 of hoger
- **PHP**: 7.4 of hoger
- **Sportlink Account**: Met geldige Client ID

## 🆘 Troubleshooting

### "API niet geconfigureerd"

- Check of Client ID is ingevuld in Instellingen → Sportlink
- Controleer of de Client ID correct is

### "Kon data niet ophalen"

- Check het dashboard widget voor API status
- Kijk in de WordPress debug log voor details
- Test de API handmatig: `https://data.sportlink.com/clubgegevens?client_id=JOUW_ID`

### Data wordt niet ververst

- Klik op "Cache leegmaken" in de plugin instellingen
- Check of de cache tijd niet te hoog is ingesteld

### Circuit breaker actief

- De API is tijdelijk niet bereikbaar
- Er wordt automatisch oude cache gebruikt
- De circuit breaker reset na 5 minuten

## 📝 Changelog

### Version 1.2.0

- ✨ Nieuwe API Interface klasse toegevoegd
- 🛡️ Verbeterde beveiliging (input sanitization, output escaping)
- ⚡ Performance optimalisaties (transients, lazy loading)
- 🔒 Circuit breaker pattern toegevoegd
- 📊 Dashboard widget voor API monitoring
- 📚 Uitgebreide documentatie en voorbeelden

### Version 1.1.0

- Basis functionaliteit
- Shortcode support
- Template systeem

## 🤝 Bijdragen

Suggesties en pull requests zijn welkom!

## 📄 Licentie

GPL v2 or later

## 👤 Auteur

**Richard van der Meer**
Website: [richardvandermeer.nl](http://richardvandermeer.nl/)

## 🔗 Links

- [Sportlink API Documentatie](https://data.sportlink.com/)
- [WordPress Plugin Development](https://developer.wordpress.org/plugins/)
- [API Interface Docs](API-INTERFACE.md)
- [Cheat Sheet](CHEATSHEET.md)
