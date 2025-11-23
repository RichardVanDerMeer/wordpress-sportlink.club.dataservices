<?php

/**
 * Sportlink API Type Definitions
 *
 * This file contains PHPDoc type definitions for all Sportlink API responses.
 * Based on the official Sportlink API specification.
 *
 * @package Sportlink_KNVB
 * @version 1.2.0
 */

/**
 * Club Information (clubgegevens)
 *
 * The clubgegevens endpoint returns 2 output objects: gegevens and bezoekadres
 *
 * @property object $gegevens Club details object with properties:
 *   - clubnaam: string - Club name
 *   - clubcode: string - Club code
 *   - relatiecode: string - Relation code
 *   - informatie: string - Information text
 *   - privacystatementclub: string - Privacy statement URL
 *   - oprichtingsdatum: string - Founding date (Y-m-d format)
 *   - oprichtingsdatetime: string - Founding datetime
 *   - straatnaam: string - Street name
 *   - huisnummer: string - House number
 *   - nummertoevoeging: string - House number addition
 *   - postcode: string - Postal code
 *   - plaats: string - City/town
 *   - telefoonnummer: string - Phone number
 *   - fax: string - Fax number
 *   - email: string - Email address
 *   - website: string - Website URL
 *   - facebook: string - Facebook URL
 *   - instagram: string - Instagram URL
 *   - twitter: string - Twitter URL
 *   - youtube: string - YouTube URL
 *   - banknummer: string - Bank account number
 *   - tennamevan: string - Account holder name
 *   - tennamevanplaats: string - Account holder city
 *   - naamsecretaris: string - Secretary name
 *   - kvknummer: string - Chamber of Commerce number
 *   - thuisshirtkleur: string - Home shirt color
 *   - thuisbroekkleur: string - Home shorts color
 *   - thuissokkenkleur: string - Home socks color
 *   - uitshirtkleur: string - Away shirt color
 *   - uitbroekkleur: string - Away shorts color
 *   - uitsokkenkleur: string - Away socks color
 *   - logo: string - Large logo (base64 encoded binary)
 *   - kleinlogo: string - Small logo (base64 encoded binary)
 * @property object $bezoekadres Visiting address object with properties:
 *   - naam: string - Name of location
 *   - straatnaam: string - Street name
 *   - huisnummer: string - House number
 *   - nummertoevoeging: string - House number addition
 *   - postcode: string - Postal code
 *   - plaats: string - City/town
 *   - route: map - Route information
 */
class Sportlink_Type_ClubInfo {}

/**
 * Team Information (teams)
 *
 * @property int $teamcode Team code
 * @property int $lokaleteamcode Local team code
 * @property int $poulecode Poule code
 * @property string $teamnaam Team name
 * @property string $competitienaam Competition name
 * @property string $klasse Class
 * @property string $poule Poule name
 * @property string $klassepoule Class and poule combined
 * @property string $spelsoort Game type (VELD/ZAAL)
 * @property string $competitiesoort Competition type
 * @property string $geslacht Gender (man/vrouw)
 * @property string $teamsoort Team type
 * @property string $leeftijdscategorie Age category
 * @property string $kalespelsoort Calendar game type
 * @property string $speeldag Play day
 * @property string $speeldagteam Play day team
 * @property int|null $leeftijdscategorieid Age category ID (custom field, not from API)
 */
class Sportlink_Type_Team {}

/**
 * Match/Fixture Information (programma/uitslagen)
 *
 * @property string $wedstrijddatum Match datetime (datetime format)
 * @property int $wedstrijdcode Match code
 * @property int $wedstrijdnummer Match number
 * @property string $teamnaam Team name
 * @property string $thuisteamclubrelatiecode Home team club relation code
 * @property string $uitteamclubrelatiecode Away team club relation code
 * @property int $thuisteamid Home team code
 * @property string $thuisteam Home team name
 * @property string $thuisteamlogo Home team logo (base64)
 * @property int $uitteamid Away team code
 * @property string $uitteam Away team name
 * @property string $uitteamlogo Away team logo (base64)
 * @property int $teamvolgorde Team order
 * @property string $competitiesoort Competition type
 * @property string $competitie Competition name
 * @property string $klasse Class
 * @property string $poule Poule name
 * @property string $klassepoule Class and poule combined
 * @property string $kaledatum Calendar date formatted
 * @property string $datum Date (Y-m-d format)
 * @property string $vertrektijd Departure time
 * @property string $verzameltijd Assembly time
 * @property string $aanvangstijd Start time
 * @property string $wedstrijd Match description
 * @property string $status Match status
 * @property string $scheidsrechters Referees (plural)
 * @property string $scheidsrechter Referee name
 * @property string $accommodatie Accommodation name
 * @property string $veld Field name
 * @property string $locatie Location
 * @property string $plaats City/town
 * @property string $rijders Drivers
 * @property string $kleedkamerthuisteam Dressing room home team
 * @property string $kleedkameruitteam Dressing room away team
 * @property string $kleedkamerscheidsrechter Dressing room referee
 * @property string|null $uitslag Result (e.g., "2-1", only in uitslagen)
 * @property int|null $leeftijdscategorieid Age category ID (custom field, not from API)
 */
class Sportlink_Type_Match {}

/**
 * Match Detail Information (wedstrijd-informatie)
 *
 * @property object $wedstrijdinformatie Match information object
 * @property int $wedstrijdinformatie->wedstrijdnummer Match number
 * @property int $wedstrijdinformatie->wedstijdnummerintern Internal match number
 * @property string $wedstrijdinformatie->veldnaam Field name
 * @property string $wedstrijdinformatie->veldlocatie Field location
 * @property string $wedstrijdinformatie->vertrektijd Departure time
 * @property string $wedstrijdinformatie->rijder Driver
 * @property string $wedstrijdinformatie->thuisscore Home score total
 * @property string $wedstrijdinformatie->thuisscore-regulier Home score regular time
 * @property string $wedstrijdinformatie->thuisscore-nv Home score after extra time
 * @property string $wedstrijdinformatie->thuisscore-s Home score penalties
 * @property string $wedstrijdinformatie->uitscore Away score total
 * @property string $wedstrijdinformatie->uitscore-regulier Away score regular time
 * @property string $wedstrijdinformatie->uitscore-nv Away score after extra time
 * @property string $wedstrijdinformatie->uitscore-s Away score penalties
 * @property string $wedstrijdinformatie->klasse Class
 * @property string $wedstrijdinformatie->wedstrijdtype Match type
 * @property string $wedstrijdinformatie->competitietype Competition type
 * @property string $wedstrijdinformatie->categorie Category
 * @property string $wedstrijdinformatie->wedstrijddatetime Match datetime
 * @property string $wedstrijdinformatie->wedstrijddatum Match date (Y-m-d)
 * @property string $wedstrijdinformatie->wedstrijddatumopgemaakt Match date formatted
 * @property string $wedstrijdinformatie->aanvangstijd Start time
 * @property string $wedstrijdinformatie->aanvangstijdopgemaakt Start time formatted
 * @property string $wedstrijdinformatie->duur Duration
 * @property string $wedstrijdinformatie->speltype Game type
 * @property string $wedstrijdinformatie->aanduiding Indication
 * @property int $wedstrijdinformatie->poulecode Poule code
 * @property string $wedstrijdinformatie->poule Poule name
 * @property int $wedstrijdinformatie->thuisteamid Home team ID
 * @property string $wedstrijdinformatie->thuisteam Home team name
 * @property int $wedstrijdinformatie->uitteamid Away team ID
 * @property string $wedstrijdinformatie->uitteam Away team name
 * @property string $wedstrijdinformatie->opmerkingen Notes
 * @property array $doelpunten Goals scored (array of goal objects)
 * @property array $kaarten Cards given (array of card objects)
 * @property array $wissels Substitutions (array of substitution objects)
 */
class Sportlink_Type_MatchDetail {}

/**
 * Team Standing (poulestand entry)
 *
 * @property int $positie Position in table
 * @property string $teamnaam Team name
 * @property string $clubrelatiecode Club relation code
 * @property string $clublogo Club logo (base64)
 * @property int $gespeeldewedstrijden Matches played
 * @property int $gewonnen Matches won
 * @property int $gelijk Matches drawn
 * @property int $verloren Matches lost
 * @property int $doelpuntenvoor Goals scored
 * @property int $doelpuntentegen Goals conceded
 * @property int $doelsaldo Goal difference
 * @property int $verliespunten Points deducted
 * @property int $punten Points
 * @property bool $eigenteam Is own team
 */
class Sportlink_Type_Standing {}

/**
 * Poule Information (poulelijst/teampoulelijst)
 *
 * @property int $poulecode Poule code
 * @property string $publiekepoulecode Public poule code
 * @property int $teamcode Team code
 * @property string $poulenaam Poule name
 * @property string $pouletype Poule type
 * @property string $competitienaam Competition name
 * @property string $leeftijdscategorie Age category
 * @property string $geslacht Gender
 * @property string $spelsoort Game type
 */
class Sportlink_Type_Poule {}

/**
 * Team Member (team-indeling entry)
 *
 * @property string $relatiecode Person/member relation code
 * @property string $naam Full name
 * @property string $voornaam First name
 * @property string $achternaam Last name
 * @property string $tussenvoegsel Middle name/prefix
 * @property string $geslacht Gender
 * @property string $rol Role name
 * @property string $functie Function description
 * @property string $einddatum End date
 * @property string $email Email address
 * @property string $email2 Secondary email address
 * @property string $telefoon Phone number
 * @property string $telefoon2 Secondary phone number
 * @property string $mobiel Mobile number
 * @property string $foto Photo (base64 encoded binary)
 */
class Sportlink_Type_TeamMember {}

/**
 * Match Participant (wedstrijd-deelnemers entry)
 *
 * @property string $persooncode Person code
 * @property string $naam Name
 * @property string $rugnummer Shirt number
 * @property string $functie Function/role
 * @property bool $geblesseerd Injured
 * @property bool $geschorst Suspended
 * @property bool $basisspeler Starting player
 */
class Sportlink_Type_MatchParticipant {}

/**
 * Goal Information
 *
 * @property string $minuut Minute of goal
 * @property string $persooncode Person code
 * @property string $naam Player name
 * @property string $team Team (THUIS/UIT)
 * @property string $soort Goal type (e.g., penalty, own goal)
 */
class Sportlink_Type_Goal {}

/**
 * Card Information
 *
 * @property string $minuut Minute of card
 * @property string $persooncode Person code
 * @property string $naam Player name
 * @property string $team Team (THUIS/UIT)
 * @property string $kaartsoort Card type (GEEL/ROOD)
 */
class Sportlink_Type_Card {}

/**
 * Historical Match Result (wedstrijd-historische-resultaten entry)
 *
 * @property string $datum Date
 * @property string $thuisteam Home team
 * @property string $uitteam Away team
 * @property string $uitslag Result
 * @property string $competitie Competition name
 */
class Sportlink_Type_HistoricalResult {}

/**
 * Training Information
 *
 * @property string $trainingid Training ID
 * @property string $teamcode Team code
 * @property string $teamnaam Team name
 * @property string $datum Date
 * @property string $starttijd Start time
 * @property string $eindtijd End time
 * @property string $accommodatie Accommodation
 * @property string $opmerkingen Notes
 */
class Sportlink_Type_Training {}

/**
 * Volunteer Task
 *
 * @property string $vrijwilligerstaakcode Task code
 * @property string $vrijwilligerstaak Task name
 * @property string $omschrijving Description
 * @property int $aantalnodig Number needed
 * @property int $aantalingevuld Number filled
 */
class Sportlink_Type_VolunteerTask {}

/**
 * Volunteer
 *
 * @property string $persooncode Person code
 * @property string $naam Name
 * @property string $emailadres Email address
 * @property string $telefoonnummer Phone number
 * @property string $vrijwilligerstaak Task name
 */
class Sportlink_Type_Volunteer {}

/**
 * Club Activity (verenigingsactiviteiten entry)
 *
 * @property string $kalendernaam Calendar name
 * @property string $kalendersoort Calendar type
 * @property string $activiteit Activity name/title
 * @property string $omschrijving Description
 * @property string $begindatum Start date (Y-m-d)
 * @property string $begindatetime Start datetime
 * @property string $begindag Start day name
 * @property string $begintijd Start time
 * @property string $einddatum End date (Y-m-d)
 * @property string $einddatetime End datetime
 * @property string $einddag End day name
 * @property string $eindtijd End time
 * @property string $locatie Location
 * @property string $inschrijvenverplicht Registration required (ja/nee)
 * @property string $inschrijvenvanafdatum Registration from date
 * @property string $inschrijventotdatum Registration until date
 * @property string $aantalinschrijvingen Number of registrations
 * @property string $maxaantalinschrijvingen Maximum registrations
 */
class Sportlink_Type_ClubActivity {}

/**
 * Board Information (bestuur)
 *
 * The bestuur endpoint returns 3 separate output objects: voorzitter, secretaris, penningmeester
 *
 * @property object $voorzitter Chairman information
 * @property string $voorzitter->naam Name
 * @property string $voorzitter->email Email address
 * @property string $voorzitter->email2 Secondary email address
 * @property object $secretaris Secretary information
 * @property string $secretaris->naam Name
 * @property string $secretaris->email Email address
 * @property string $secretaris->email2 Secondary email address
 * @property object $penningmeester Treasurer information
 * @property string $penningmeester->naam Name
 * @property string $penningmeester->email Email address
 * @property string $penningmeester->email2 Secondary email address
 */
class Sportlink_Type_Board {}

/**
 * Committee (commissies entry)
 *
 * @property string $commissiecode Committee code
 * @property string $commissienaam Committee name
 * @property string $omschrijving Description
 * @property string $foto Photo (base64 encoded binary)
 * @property string $opmerkingen Notes
 * @property string $telefoon Phone number
 * @property string $mobiel Mobile number
 * @property string $email Email address
 */
class Sportlink_Type_Committee {}

/**
 * Committee Member (commissie-leden entry)
 *
 * @property string $lid Member name
 * @property string $rolid Role ID
 * @property string $rol Role name
 * @property string $email Email address
 * @property string $email2 Secondary email address
 * @property string $telefoon Phone number
 * @property string $telefoon2 Secondary phone number
 * @property string $mobiel Mobile number
 * @property string $startdatum Start date (Y-m-d)
 * @property string $startdatetime Start datetime
 * @property string $einddatum End date (Y-m-d)
 * @property string $einddatetime End datetime
 * @property string $informatie Information
 * @property string $adres Address
 * @property string $plaats City
 * @property string $foto Photo (base64 encoded binary)
 */
class Sportlink_Type_CommitteeMember {}

/**
 * Match Accommodation Information (wedstrijd-accommodatie)
 *
 * @property object $wedstrijd Match accommodation object
 * @property string $wedstrijd->thuisteam Home team name
 * @property string $wedstrijd->accommodatienaam Accommodation name
 * @property string $wedstrijd->adres Street address
 * @property string $wedstrijd->plaats City
 * @property string $wedstrijd->telefoonnummer Phone number
 * @property string $wedstrijd->route Route (map data)
 */
class Sportlink_Type_Accommodation {}

/**
 * Period Standing (periodestand entry)
 *
 * @property int $periodenummer Period number
 * @property int $positie Position
 * @property string $teamnaam Team name
 * @property int $gespeeld Matches played
 * @property int $gewonnen Matches won
 * @property int $gelijk Matches drawn
 * @property int $verloren Matches lost
 * @property int $punten Points
 * @property int $doelpuntenvoor Goals for
 * @property int $doelpuntentegen Goals against
 */
class Sportlink_Type_PeriodStanding {}

/**
 * Match Statistics (wedstrijd-statistieken)
 *
 * @property object $thuisteam Home team stats
 * @property object $uitteam Away team stats
 * @property int $balbezit Ball possession percentage
 * @property int $doelpogingen Goal attempts
 * @property int $schotenopdoel Shots on target
 * @property int $hoekschoppen Corner kicks
 * @property int $overtredingen Fouls
 */
class Sportlink_Type_MatchStatistics {}

/**
 * Official (wedstrijd-officials entry)
 *
 * @property string $officialnaam Official name
 * @property string $officialomschrijving Official function/description
 * @property string $relatiecode Relation code
 */
class Sportlink_Type_Official {}
