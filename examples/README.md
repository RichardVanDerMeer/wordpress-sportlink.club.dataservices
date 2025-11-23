# Sportlink API Voorbeelden

Deze map bevat praktische voorbeelden voor het gebruik van de Sportlink API interface.

## 📁 Bestanden

### `quick-start-shortcodes.php`

**Meteen aan de slag!**

Kant-en-klare WordPress shortcodes die je direct kunt gebruiken:

- `[sportlink_teams]` - Teams lijst
- `[sportlink_programma]` - Wedstrijd programma
- `[sportlink_uitslagen]` - Recente uitslagen
- `[sportlink_stand]` - Poule stand
- `[sportlink_clubinfo]` - Club informatie

**Gebruik:** Kopieer de functies naar je `functions.php` of maak een custom plugin.

### `api-usage-examples.php`

**Uitgebreide voorbeelden**

Bevat 8 uitgebreide voorbeelden met toelichting:

1. Basis teams ophalen
2. Programma met filters
3. Poule stand weergeven
4. Wedstrijd details pagina
5. Club gegevens
6. Custom widgets
7. Filter formulieren met dropdowns
8. Robuuste error handling

**Let op:** Dit is een documentatie bestand, niet direct te gebruiken. Kopieer de code die je nodig hebt.

## 🚀 Snel Starten

### Stap 1: Configureer de plugin

1. Ga naar WordPress Admin → Instellingen → Sportlink
2. Vul je Sportlink Client ID in
3. Sla op

### Stap 2: Gebruik de API

```php
// Maak een API instance
$api = Sportlink_API::create_from_settings();

if (!$api) {
    echo 'API niet geconfigureerd';
    return;
}

// Haal teams op
$teams = $api->get_teams();

if (is_wp_error($teams)) {
    echo 'Fout: ' . $teams->get_error_message();
    return;
}

// Toon teams
foreach ($teams as $team) {
    echo $team['teamnaam'] . '<br>';
}
```

### Stap 3: Voeg shortcode toe

Kopieer een shortcode uit `quick-start-shortcodes.php` naar je `functions.php`:

```php
function sportlink_teams_shortcode($atts) {
    $api = Sportlink_API::create_from_settings();
    // ... rest van de code
}
add_shortcode('sportlink_teams', 'sportlink_teams_shortcode');
```

Gebruik in je post/page:

```
[sportlink_teams geslacht="MAN"]
```

## 📖 Documentatie

- **API Interface documentatie**: `../API-INTERFACE.md`
- **Cheat Sheet**: `../CHEATSHEET.md`
- **API Specificatie**: `../api-specification.json` (root)

## 💡 Tips

### Error Handling (ALTIJD doen!)

```php
$data = $api->get_teams();
if (is_wp_error($data)) {
    // Handle error
    return;
}
```

### Output Escaping (ALTIJD doen!)

```php
echo esc_html($team['teamnaam']);
echo '<a href="' . esc_url($url) . '">' . esc_html($text) . '</a>';
```

### Input Sanitization (ALTIJD doen!)

```php
$teamcode = isset($_GET['team']) ? intval($_GET['team']) : null;
$naam = isset($_POST['naam']) ? sanitize_text_field($_POST['naam']) : '';
```

## 🎯 Meest Gebruikte Functies

```php
// Teams
$api->get_teams()
$api->get_team_indeling($teamcode, $lokaleteamcode)

// Wedstrijden
$api->get_programma(['teamcode' => 123])
$api->get_uitslagen(['teamcode' => 123])
$api->get_wedstrijd_informatie($wedstrijdcode)

// Standen
$api->get_poulestand($poulecode)
$api->get_poulelijst()

// Club
$api->get_clubgegevens()
```

## ❓ Veelgestelde Vragen

**Q: Hoe vind ik mijn poulecode?**
A: Gebruik `$api->get_poulelijst()` om alle poules te zien.

**Q: Hoe vind ik mijn teamcode?**
A: Gebruik `$api->get_teams()` om alle teams met codes te zien.

**Q: Waarom zie ik geen data?**
A: Check:

1. Is de API geconfigureerd? (Admin → Instellingen → Sportlink)
2. Is de Client ID correct?
3. Check de error met `is_wp_error()`
4. Check het dashboard widget voor API status

**Q: Hoe lang wordt data gecached?**
A: 30 minuten standaard (instelbaar in admin). Bij API fouten wordt tot 7 dagen oude cache gebruikt.

**Q: Kan ik de cache handmatig legen?**
A: Ja, in Admin → Instellingen → Sportlink → "Cache leegmaken"

## 🔧 Aanpassingen

### Custom cache tijd

```php
$api = new Sportlink_API('jouw-client-id', 60); // 60 minuten
```

### Teams filteren

```php
$api->get_teams([
    'geslacht' => 'VROUW',
    'leeftijdscategorie' => 'SENIOREN',
    'spelsoort' => 'VELD'
]);
```

### Programma aanpassen

```php
$api->get_programma([
    'aantalregels' => 10,     // Max 10 wedstrijden
    'aantaldagen' => 30,      // 30 dagen vooruit
    'thuis' => 'JA',          // Alleen thuiswedstrijden
    'uit' => 'NEE'
]);
```

## 🎨 Styling

De shortcodes genereren standaard HTML. Voeg eigen CSS toe:

```css
.sportlink-stand tr.eigenteam {
    background-color: #fff3cd;
    font-weight: bold;
}

.sportlink-programma table {
    width: 100%;
    border-collapse: collapse;
}
```

## 🆘 Hulp nodig?

1. Check de volledige documentatie in `API-INTERFACE.md`
2. Bekijk de cheat sheet in `CHEATSHEET.md`
3. Gebruik de WordPress debug log voor foutmeldingen
4. Check het dashboard widget voor API status

## 📝 Licentie

GPL v2 or later
