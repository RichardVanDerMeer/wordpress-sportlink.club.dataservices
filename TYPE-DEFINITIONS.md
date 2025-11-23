# Sportlink API Type Definitions

Dit document beschrijft hoe je de type definities kunt gebruiken voor betere IDE ondersteuning en autocomplete.

## Overzicht

De plugin bevat nu gedetailleerde type definities voor alle API responses in:
- `includes/class-sportlink-types.php` - PHPDoc type definities
- `includes/class-sportlink-api.php` - Uitgebreide method documentatie
- `sportlink.club.dataservices.php` - SportlinkClient met type hints

## Gebruik met IDE Autocomplete

### Voorbeeld 1: Club Informatie

```php
$api = Sportlink_API::create_from_settings();
$club_data = $api->get_clubgegevens();

if (!is_wp_error($club_data)) {
    // De API retourneert twee objecten: gegevens en bezoekadres

    // Club gegevens
    echo $club_data['gegevens']->clubnaam;        // string - Club naam
    echo $club_data['gegevens']->email;           // string - Email
    echo $club_data['gegevens']->telefoonnummer;  // string - Telefoonnummer
    echo $club_data['gegevens']->website;         // string - Website
    echo $club_data['gegevens']->oprichtingsdatum; // string - Oprichtingsdatum (Y-m-d)
    echo $club_data['gegevens']->thuisshirtkleur; // string - Thuis shirt kleur
    echo $club_data['gegevens']->thuisbroekkleur; // string - Thuis broek kleur
    echo $club_data['gegevens']->thuissokkenkleur; // string - Thuis sokken kleur
    echo $club_data['gegevens']->kleinlogo;       // string - Logo (base64)
    echo $club_data['gegevens']->straatnaam;      // string - Straatnaam postadres
    echo $club_data['gegevens']->huisnummer;      // string - Huisnummer postadres
    echo $club_data['gegevens']->postcode;        // string - Postcode postadres
    echo $club_data['gegevens']->plaats;          // string - Plaats postadres

    // Bezoekadres
    echo $club_data['bezoekadres']->naam;         // string - Naam locatie
    echo $club_data['bezoekadres']->straatnaam;   // string - Straatnaam
    echo $club_data['bezoekadres']->huisnummer;   // string - Huisnummer
    echo $club_data['bezoekadres']->nummertoevoeging; // string - Toevoeging
    echo $club_data['bezoekadres']->postcode;     // string - Postcode
    echo $club_data['bezoekadres']->plaats;       // string - Plaats
}
```

### Voorbeeld 2: Teams

```php
$api = Sportlink_API::create_from_settings();
$teams = $api->get_teams([
    'geslacht' => 'MAN',
    'leeftijdscategorie' => 'SENIOREN'
]);

if (!is_wp_error($teams)) {
    foreach ($teams as $team) {
        // Beschikbare properties per team:
        echo $team['teamcode'];           // string - Team code
        echo $team['teamnaam'];           // string - Team naam
        echo $team['teamsoort'];          // string - Team soort
        echo $team['geslacht'];           // string - Geslacht
        echo $team['leeftijdscategorie']; // string - Leeftijdscategorie
        echo $team['spelsoort'];          // string - Spelsoort (VELD/ZAAL)
        echo $team['competitiesoort'];    // string - Competitie soort
        echo $team['speeldag'];           // string - Speeldag
        echo $team['accommodatienaam'];   // string - Accommodatie
        echo $team['poulecode'];          // string - Poule code
        echo $team['poulenaam'];          // string - Poule naam
        echo $team['competitienaam'];     // string - Competitie naam
    }
}
```

### Voorbeeld 3: Programma/Wedstrijden

```php
$api = Sportlink_API::create_from_settings();
$programma = $api->get_programma([
    'teamcode' => 12345,
    'aantaldagen' => 14
]);

if (!is_wp_error($programma)) {
    foreach ($programma as $wedstrijd) {
        // Beschikbare properties per wedstrijd:
        echo $wedstrijd['wedstrijdcode'];   // string - Wedstrijd code
        echo $wedstrijd['wedstrijdnummer']; // string - Wedstrijd nummer
        echo $wedstrijd['datum'];           // string - Datum (Y-m-d)
        echo $wedstrijd['tijd'];            // string - Tijd (H:i:s)
        echo $wedstrijd['kaledatum'];       // string - Geformatteerde datum
        echo $wedstrijd['weekdag'];         // string - Weekdag
        echo $wedstrijd['thuisteam'];       // string - Thuis team
        echo $wedstrijd['thuisteamid'];     // string - Thuis team code
        echo $wedstrijd['uitteam'];         // string - Uit team
        echo $wedstrijd['uitteamid'];       // string - Uit team code
        echo $wedstrijd['accommodatie'];    // string - Accommodatie naam
        echo $wedstrijd['plaats'];          // string - Plaats
        echo $wedstrijd['uitslag'];         // string - Uitslag (indien gespeeld)
        echo $wedstrijd['scheidsrechter'];  // string - Scheidsrechter
        echo $wedstrijd['poulecode'];       // string - Poule code
        echo $wedstrijd['competitienaam'];  // string - Competitie naam
        echo $wedstrijd['bijzonderheden'];  // string - Bijzonderheden
    }
}
```

### Voorbeeld 4: Poule Stand

```php
$api = Sportlink_API::create_from_settings();
$stand = $api->get_poulestand(67890);

if (!is_wp_error($stand)) {
    foreach ($stand as $team) {
        // Beschikbare properties per team in stand:
        echo $team['positie'];          // int - Positie
        echo $team['teamcode'];         // string - Team code
        echo $team['teamnaam'];         // string - Team naam
        echo $team['gespeeld'];         // int - Wedstrijden gespeeld
        echo $team['gewonnen'];         // int - Gewonnen
        echo $team['gelijk'];           // int - Gelijk
        echo $team['verloren'];         // int - Verloren
        echo $team['doelpuntenvoor'];   // int - Doelpunten voor
        echo $team['doelpuntentegen'];  // int - Doelpunten tegen
        echo $team['doelsaldo'];        // int - Doelsaldo
        echo $team['punten'];           // int - Punten
        $is_eigen = $team['eigenteam']; // bool - Is eigen team
        echo $team['trend'];            // string - Vorm trend
    }
}
```

## Alle Beschikbare Types

Zie `includes/class-sportlink-types.php` voor alle type definities:

### Club & Organisatie
- `Sportlink_Type_ClubInfo` - Club gegevens
- `Sportlink_Type_BoardMember` - Bestuurslid
- `Sportlink_Type_Committee` - Commissie
- `Sportlink_Type_CommitteeMember` - Commissie lid

### Teams & Spelers
- `Sportlink_Type_Team` - Team informatie
- `Sportlink_Type_TeamMember` - Team lid/speler
- `Sportlink_Type_Poule` - Poule informatie

### Wedstrijden
- `Sportlink_Type_Match` - Basis wedstrijd informatie
- `Sportlink_Type_MatchDetail` - Gedetailleerde wedstrijd informatie
- `Sportlink_Type_MatchParticipant` - Wedstrijd deelnemer
- `Sportlink_Type_Goal` - Doelpunt
- `Sportlink_Type_Card` - Kaart (geel/rood)
- `Sportlink_Type_Official` - Scheidsrechter/official
- `Sportlink_Type_HistoricalResult` - Historisch resultaat

### Standen
- `Sportlink_Type_Standing` - Team in stand
- `Sportlink_Type_PeriodStanding` - Periode stand

### Faciliteiten
- `Sportlink_Type_Accommodation` - Accommodatie
- `Sportlink_Type_Training` - Training

### Vrijwilligers & Activiteiten
- `Sportlink_Type_VolunteerTask` - Vrijwilligerstaak
- `Sportlink_Type_Volunteer` - Vrijwilliger
- `Sportlink_Type_ClubActivity` - Verenigingsactiviteit

### Statistieken
- `Sportlink_Type_MatchStatistics` - Wedstrijd statistieken

## Type Casting in Code

Als je zeker wilt zijn van de types, kun je type hints gebruiken:

```php
/**
 * @param array<Sportlink_Type_Team> $teams
 */
function display_teams($teams) {
    foreach ($teams as $team) {
        // IDE weet nu dat $team een Sportlink_Type_Team is
        echo $team['teamnaam'];
    }
}

$api = Sportlink_API::create_from_settings();
$teams = $api->get_teams();

if (!is_wp_error($teams)) {
    display_teams($teams);
}
```

## PHPDoc in je eigen code

Gebruik PHPDoc om je IDE te helpen:

```php
// In je template of functie:

/** @var array<array{teamcode: string, teamnaam: string, punten: int}> $teams */
$teams = $api->get_teams();

// Of met een specifiek type:

/** @var array<Sportlink_Type_Match> $programma */
$programma = $api->get_programma();
```

## API Specificatie

Voor de volledige API specificatie met alle mogelijke velden en waarden, zie:
- `examples/api.txt` - Volledige JSON specificatie van alle endpoints

## Tips voor IDE Ondersteuning

### VS Code
Installeer: **PHP Intelephense** extensie voor betere autocomplete

### PhpStorm
PhpStorm ondersteunt PHPDoc out-of-the-box

### Andere Editors
Zoek naar PHP Language Server of PHPDoc support plugins

## Validatie

Gebruik altijd `is_wp_error()` om fouten te checken:

```php
$data = $api->get_teams();

if (is_wp_error($data)) {
    // Handle error
    error_log('Sportlink Error: ' . $data->get_error_message());
    return;
}

// Gebruik data - je weet nu zeker dat het geen error is
foreach ($data as $team) {
    // ...
}
```

## Meer Informatie

- Zie `API-INTERFACE.md` voor volledige API documentatie
- Zie `CHEATSHEET.md` voor snelle referentie
- Zie `examples/` voor praktische code voorbeelden
