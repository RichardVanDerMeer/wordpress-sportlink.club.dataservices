<?php

/**
 * Sportlink API Interface
 *
 * Deze klasse biedt een gestructureerde interface voor de Sportlink Club.Dataservices API.
 * Gebaseerd op de officiële API specificatie.
 *
 * Voor gedetailleerde informatie over return types, zie:
 * - class-sportlink-types.php voor alle type definities
 * - api.txt voor de volledige API specificatie
 *
 * Alle methoden retourneren ofwel een array met data, of een WP_Error object bij fouten.
 * Check altijd met is_wp_error() voor je de data gebruikt.
 *
 * @package Sportlink
 * @version 1.2.0
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class Sportlink_API
{
	/**
	 * @var SportlinkClient Sportlink client instance
	 */
	private $client;

	/**
	 * Constructor
	 *
	 * @param string $client_id Sportlink Client ID
	 * @param int $cache_time Cache tijd in minuten (standaard: 30)
	 */
	public function __construct($client_id, $cache_time = 30)
	{
		$this->client = new SportlinkClient($client_id, $cache_time);
	}

	/**
	 * Voer een API request uit
	 *
	 * @param string $endpoint API endpoint naam
	 * @param array $params Query parameters
	 * @return array|WP_Error API response of WP_Error bij fout
	 */
	private function request($endpoint, $params = [])
	{
		try {
			// Gebruik de SportlinkClient doRequest methode
			// Deze heeft alle caching, error handling en circuit breaker logica
			$result = $this->client->doRequest($endpoint, true, $params);

			// Convert stdClass to array voor consistente interface
			if (is_object($result)) {
				return json_decode(json_encode($result), true);
			}

			return $result;
		} catch (Exception $e) {
			return new WP_Error('sportlink_api_error', $e->getMessage());
		}
	}

	// ===================================================================
	// TEAMS & COMPETITIE
	// ===================================================================

	/**
	 * Haal teams op
	 *
	 * Returns een array van team objecten met de volgende properties:
	 * - teamcode: string - Unieke team code
	 * - teamnaam: string - Team naam
	 * - teamsoort: string - Type team
	 * - geslacht: string - Geslacht (man/vrouw)
	 * - leeftijdscategorie: string - Leeftijdscategorie
	 * - spelsoort: string - Spelsoort (VELD/ZAAL)
	 * - competitiesoort: string - Type competitie
	 * - speeldag: string - Vaste speeldag
	 * - accommodatiecode: string - Code van accommodatie
	 * - accommodatienaam: string - Naam van accommodatie
	 * - poulecode: string - Poule code
	 * - poulenaam: string - Poule naam
	 * - competitienaam: string - Naam van competitie
	 *
	 * @param array $args {
	 *     Optionele parameters
	 *
	 *     @type string $competitieperiode    Competitieperiode (bijv. "2024-2025")
	 *     @type string $teamsoort            Teamsoort: "ALLES", "STANDAARD", "ZAALVOETBAL" etc.
	 *     @type string $geslacht             Geslacht: "ALLES", "MAN", "VROUW"
	 *     @type string $spelsoort            Spelsoort: "ALLES", "VELD", "ZAAL"
	 *     @type string $competitiesoort      Competitiesoort: "ALLES", "REGULIER", "BEKER"
	 *     @type string $leeftijdscategorie   Leeftijdscategorie: "ALLES", "SENIOREN", "JUNIOREN"
	 *     @type string $gebruiklokaleteamgegevens "JA" of "NEE" (standaard: "NEE")
	 * }
	 * @return array|WP_Error Array van team objecten (zie Sportlink_Type_Team) of WP_Error bij fout
	 * @see Sportlink_Type_Team voor volledige type definitie
	 */
	public function get_teams($args = [])
	{
		$defaults = [
			'competitieperiode' => '',
			'teamsoort' => 'ALLES',
			'geslacht' => 'ALLES',
			'spelsoort' => 'ALLES',
			'competitiesoort' => 'ALLES',
			'leeftijdscategorie' => 'ALLES',
			'gebruiklokaleteamgegevens' => 'NEE'
		];

		$params = wp_parse_args($args, $defaults);
		return $this->request('teams', $params);
	}

	/**
	 * Haal team indeling op
	 *
	 * @param int $teamcode Team code (verplicht)
	 * @param int $lokaleteamcode Lokale team code (verplicht)
	 * @param array $args {
	 *     @type string $teampersoonrol "ALLES", "SPELER", "TRAINER" etc.
	 *     @type string $toonlidfoto    "JA" of "NEE" (standaard: "NEE")
	 * }
	 * @return array|WP_Error Team leden of fout
	 */
	public function get_team_indeling($teamcode, $lokaleteamcode, $args = [])
	{
		$defaults = [
			'teampersoonrol' => 'ALLES',
			'toonlidfoto' => 'NEE'
		];

		$params = array_merge([
			'teamcode' => $teamcode,
			'lokaleteamcode' => $lokaleteamcode
		], wp_parse_args($args, $defaults));

		return $this->request('team-indeling', $params);
	}

	/**
	 * Haal team gegevens op
	 *
	 * @param int $teamcode Team code
	 * @param int $lokaleteamcode Lokale team code
	 * @return array|WP_Error Team gegevens of fout
	 */
	public function get_team_gegevens($teamcode, $lokaleteamcode)
	{
		return $this->request('team-gegevens', [
			'teamcode' => $teamcode,
			'lokaleteamcode' => $lokaleteamcode
		]);
	}

	/**
	 * Haal team sponsors op
	 *
	 * @param int $teamcode Team code
	 * @param int $lokaleteamcode Lokale team code
	 * @return array|WP_Error Team sponsors of fout
	 */
	public function get_team_sponsors($teamcode, $lokaleteamcode)
	{
		return $this->request('team-sponsors', [
			'teamcode' => $teamcode,
			'lokaleteamcode' => $lokaleteamcode
		]);
	}

	// ===================================================================
	// PROGRAMMA & UITSLAGEN
	// ===================================================================

	/**
	 * Haal wedstrijd programma op
	 *
	 * Returns een array van wedstrijd objecten met de volgende properties:
	 * - wedstrijdcode: string - Unieke wedstrijd code
	 * - wedstrijdnummer: string - Wedstrijd nummer
	 * - datum: string - Datum (Y-m-d format)
	 * - tijd: string - Aanvangstijd (H:i:s format)
	 * - kaledatum: string - Geformatteerde datum
	 * - weekdag: string - Dag van de week
	 * - thuisteam: string - Naam thuisteam
	 * - thuisteamid: string - Code thuisteam
	 * - uitteam: string - Naam uitteam
	 * - uitteamid: string - Code uitteam
	 * - accommodatiecode: string - Code accommodatie
	 * - accommodatie: string - Naam accommodatie
	 * - plaats: string - Plaats
	 * - uitslag: string - Uitslag indien gespeeld
	 * - scheidsrechter: string - Naam scheidsrechter
	 * - poulecode: string - Poule code
	 * - poulenaam: string - Poule naam
	 * - competitienaam: string - Competitie naam
	 * - bijzonderheden: string - Bijzonderheden/opmerkingen
	 * - status: string - Match status
	 *
	 * @param array $args {
	 *     @type int    $lokaleteamcode      Lokale team code
	 *     @type int    $teamcode            Team code
	 *     @type string $sorteervolgorde     "datum", "team" etc.
	 *     @type string $gebruiklokaleteamgegevens "JA" of "NEE"
	 *     @type int    $aantalregels        Maximum aantal regels (standaard: 100)
	 *     @type int    $aantaldagen         Maximum aantal dagen vooruit (standaard: 30)
	 *     @type int    $weekoffset          Weeknummer vanaf nu (standaard: 0)
	 *     @type string $eigenwedstrijden    "JA" of "NEE" (standaard: "JA")
	 *     @type string $thuis               "JA" of "NEE" (standaard: "JA")
	 *     @type string $uit                 "JA" of "NEE" (standaard: "JA")
	 *     @type string $spelsoort           "ALLES", "VELD", "ZAAL"
	 *     @type string $competitiesoort     "ALLES", "REGULIER", "BEKER"
	 *     @type string $dagsoort            "ALLES", "ZATERDAG", "ZONDAG"
	 *     @type string $leeftijdscategorie  "ALLES", "SENIOREN", "JUNIOREN"
	 *     @type string $wedstrijdtype       Wedstrijd type filter
	 * }
	 * @return array|WP_Error Array van wedstrijd objecten (zie Sportlink_Type_Match) of WP_Error bij fout
	 * @see Sportlink_Type_Match voor volledige type definitie
	 */
	public function get_programma($args = [])
	{
		$defaults = [
			'sorteervolgorde' => 'datum',
			'gebruiklokaleteamgegevens' => 'NEE',
			'aantalregels' => 100,
			'aantaldagen' => 30,
			'weekoffset' => 0,
			'eigenwedstrijden' => 'JA',
			'thuis' => 'JA',
			'uit' => 'JA',
			'spelsoort' => 'ALLES',
			'competitiesoort' => 'ALLES',
			'dagsoort' => 'ALLES',
			'leeftijdscategorie' => 'ALLES'
		];

		$params = wp_parse_args($args, $defaults);
		return $this->request('programma', $params);
	}

	/**
	 * Haal uitslagen op
	 *
	 * @param array $args Zie get_programma() voor parameters
	 * @return array|WP_Error Uitslagen of fout
	 */
	public function get_uitslagen($args = [])
	{
		$defaults = [
			'aantalregels' => 100,
			'weekoffset' => -1,
			'aantaldagen' => 7,
			'gebruiklokaleteamgegevens' => 'NEE',
			'sorteervolgorde' => 'datum',
			'eigenwedstrijden' => 'JA',
			'thuis' => 'JA',
			'uit' => 'JA',
			'spelsoort' => 'ALLES',
			'leeftijdscategorie' => 'ALLES',
			'competitiesoort' => 'ALLES',
			'dagsoort' => 'ALLES'
		];

		$params = wp_parse_args($args, $defaults);
		return $this->request('uitslagen', $params);
	}

	/**
	 * Haal afgelastingen op
	 *
	 * @param array $args {
	 *     @type int    $aantaldagen         Aantal dagen vooruit (standaard: 30)
	 *     @type int    $aantalregels        Maximum aantal regels (standaard: 20)
	 *     @type int    $weekoffset          Week offset (standaard: 0)
	 *     @type string $gebruiklokaleteamgegevens "JA" of "NEE"
	 *     @type string $sorteervolgorde     Sorteervolgorde
	 * }
	 * @return array|WP_Error Afgelaste wedstrijden of fout
	 */
	public function get_afgelastingen($args = [])
	{
		$defaults = [
			'aantaldagen' => 30,
			'aantalregels' => 20,
			'weekoffset' => 0,
			'gebruiklokaleteamgegevens' => 'NEE',
			'sorteervolgorde' => 'datum'
		];

		$params = wp_parse_args($args, $defaults);
		return $this->request('afgelastingen', $params);
	}

	// ===================================================================
	// WEDSTRIJD DETAILS
	// ===================================================================

	/**
	 * Haal wedstrijd informatie op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @return array|WP_Error Wedstrijd informatie of fout
	 */
	public function get_wedstrijd_informatie($wedstrijdcode)
	{
		return $this->request('wedstrijd-informatie', [
			'wedstrijdcode' => $wedstrijdcode
		]);
	}

	/**
	 * Haal wedstrijd deelnemers op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @return array|WP_Error Wedstrijd deelnemers of fout
	 */
	public function get_wedstrijd_deelnemers($wedstrijdcode)
	{
		return $this->request('wedstrijd-deelnemers', [
			'wedstrijdcode' => $wedstrijdcode
		]);
	}

	/**
	 * Haal wedstrijd thuisteam op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @param bool $toonlidfoto Toon lid foto (standaard: false)
	 * @return array|WP_Error Thuisteam spelers of fout
	 */
	public function get_wedstrijd_thuisteam($wedstrijdcode, $toonlidfoto = false)
	{
		return $this->request('wedstrijd-thuisteam', [
			'wedstrijdcode' => $wedstrijdcode,
			'toonlidfoto' => $toonlidfoto ? 'JA' : 'NEE'
		]);
	}

	/**
	 * Haal wedstrijd uitteam op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @param bool $toonlidfoto Toon lid foto (standaard: false)
	 * @return array|WP_Error Uitteam spelers of fout
	 */
	public function get_wedstrijd_uitteam($wedstrijdcode, $toonlidfoto = false)
	{
		return $this->request('wedstrijd-uitteam', [
			'wedstrijdcode' => $wedstrijdcode,
			'toonlidfoto' => $toonlidfoto ? 'JA' : 'NEE'
		]);
	}

	/**
	 * Haal wedstrijd officials op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @return array|WP_Error Wedstrijd officials of fout
	 */
	public function get_wedstrijd_officials($wedstrijdcode)
	{
		return $this->request('wedstrijd-officials', [
			'wedstrijdcode' => $wedstrijdcode
		]);
	}

	/**
	 * Haal wedstrijd accommodatie op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @return array|WP_Error Accommodatie informatie of fout
	 */
	public function get_wedstrijd_accommodatie($wedstrijdcode)
	{
		return $this->request('wedstrijd-accommodatie', [
			'wedstrijdcode' => $wedstrijdcode
		]);
	}

	/**
	 * Haal wedstrijd kleedkamers op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @return array|WP_Error Kleedkamer informatie of fout
	 */
	public function get_wedstrijd_kleedkamers($wedstrijdcode)
	{
		return $this->request('wedstrijd-kleedkamers', [
			'wedstrijdcode' => $wedstrijdcode
		]);
	}

	/**
	 * Haal wedstrijd statistieken op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @return array|WP_Error Wedstrijd statistieken of fout
	 */
	public function get_wedstrijd_statistieken($wedstrijdcode)
	{
		return $this->request('wedstrijd-statistieken', [
			'wedstrijdcode' => $wedstrijdcode
		]);
	}

	/**
	 * Haal historische resultaten voor een wedstrijd op
	 *
	 * @param int $wedstrijdcode Wedstrijd code
	 * @return array|WP_Error Historische resultaten of fout
	 */
	public function get_wedstrijd_historische_resultaten($wedstrijdcode)
	{
		return $this->request('wedstrijd-historische-resultaten', [
			'wedstrijdcode' => $wedstrijdcode
		]);
	}

	// ===================================================================
	// STANDEN
	// ===================================================================

	/**
	 * Haal poule stand op
	 *
	 * @param int $poulecode Poule code
	 * @param array $args {
	 *     @type string $gebruiklokaleteamgegevens "JA" of "NEE"
	 * }
	 * @return array|WP_Error Poule stand of fout
	 */
	public function get_poulestand($poulecode, $args = [])
	{
		$defaults = [
			'gebruiklokaleteamgegevens' => 'NEE'
		];

		$params = array_merge([
			'poulecode' => $poulecode
		], wp_parse_args($args, $defaults));

		return $this->request('poulestand', $params);
	}

	/**
	 * Haal periode stand op
	 *
	 * @param int $poulecode Poule code
	 * @param int $periodenummer Periode nummer (standaard: -1 = huidige)
	 * @return array|WP_Error Periode stand of fout
	 */
	public function get_periodestand($poulecode, $periodenummer = -1)
	{
		return $this->request('periodestand', [
			'poulecode' => $poulecode,
			'periodenummer' => $periodenummer
		]);
	}

	/**
	 * Haal poule indeling op
	 *
	 * @param int $poulecode Poule code
	 * @return array|WP_Error Poule indeling of fout
	 */
	public function get_poule_indeling($poulecode)
	{
		return $this->request('poule-indeling', [
			'poulecode' => $poulecode
		]);
	}

	/**
	 * Haal poule programma op
	 *
	 * @param int $poulecode Poule code
	 * @param array $args {
	 *     @type int    $aantaldagen         Aantal dagen vooruit (standaard: 30)
	 *     @type int    $weekoffset          Week offset (standaard: 0)
	 *     @type string $eigenwedstrijden    "JA" of "NEE"
	 *     @type string $gebruiklokaleteamgegevens "JA" of "NEE"
	 * }
	 * @return array|WP_Error Poule programma of fout
	 */
	public function get_poule_programma($poulecode, $args = [])
	{
		$defaults = [
			'aantaldagen' => 30,
			'weekoffset' => 0,
			'eigenwedstrijden' => 'JA',
			'gebruiklokaleteamgegevens' => 'NEE'
		];

		$params = array_merge([
			'poulecode' => $poulecode
		], wp_parse_args($args, $defaults));

		return $this->request('poule-programma', $params);
	}

	/**
	 * Haal poule uitslagen op
	 *
	 * @param int $poulecode Poule code
	 * @param array $args {
	 *     @type int    $aantaldagen         Aantal dagen (standaard: 14)
	 *     @type int    $weekoffset          Week offset (standaard: -2)
	 *     @type string $eigenwedstrijden    "JA" of "NEE"
	 *     @type string $sorteervolgorde     Sorteervolgorde (standaard: "datum")
	 *     @type string $gebruiklokaleteamgegevens "JA" of "NEE"
	 * }
	 * @return array|WP_Error Poule uitslagen of fout
	 */
	public function get_pouleuitslagen($poulecode, $args = [])
	{
		$defaults = [
			'aantaldagen' => 14,
			'weekoffset' => -2,
			'eigenwedstrijden' => 'JA',
			'sorteervolgorde' => 'datum',
			'gebruiklokaleteamgegevens' => 'NEE'
		];

		$params = array_merge([
			'poulecode' => $poulecode
		], wp_parse_args($args, $defaults));

		return $this->request('pouleuitslagen', $params);
	}

	/**
	 * Haal poule lijst op
	 *
	 * @return array|WP_Error Poule lijst of fout
	 */
	public function get_poulelijst()
	{
		return $this->request('poulelijst');
	}

	/**
	 * Haal team poule lijst op
	 *
	 * @param int $teamcode Team code
	 * @param int $lokaleteamcode Lokale team code
	 * @return array|WP_Error Team poule lijst of fout
	 */
	public function get_teampoulelijst($teamcode, $lokaleteamcode)
	{
		return $this->request('teampoulelijst', [
			'teamcode' => $teamcode,
			'lokaleteamcode' => $lokaleteamcode
		]);
	}

	// ===================================================================
	// CLUB GEGEVENS
	// ===================================================================

	/**
	 * Haal club gegevens op
	 *
	 * Returns een object met de volgende properties:
	 * - clubnaam: string - Naam van de club
	 * - clubcode: string - Unieke club code
	 * - relatiecode: string - Relatie code
	 * - straatnaam: string - Straatnaam
	 * - huisnummer: string - Huisnummer
	 * - huisnummertoevoeging: string - Toevoeging bij huisnummer
	 * - postcode: string - Postcode
	 * - plaats: string - Plaatsnaam
	 * - telefoonnummer: string - Telefoonnummer
	 * - emailadres: string - Email adres
	 * - website: string - Website URL
	 * - oprichtingsdatum: string - Oprichtingsdatum (Y-m-d format)
	 * - thuisshirtkleur: string - Thuis shirt kleur
	 * - thuisbroekkleur: string - Thuis broek kleur
	 * - thuissokkleur: string - Thuis sok kleur
	 * - uitshirtkleur: string - Uit shirt kleur
	 * - uitbroekkleur: string - Uit broek kleur
	 * - uitsokkleur: string - Uit sok kleur
	 * - kleinlogo: string - Klein logo (base64 encoded)
	 * - grootlogo: string - Groot logo (base64 encoded)
	 *
	 * @return array|WP_Error Club gegevens object (zie Sportlink_Type_ClubInfo) of WP_Error bij fout
	 * @see Sportlink_Type_ClubInfo voor volledige type definitie
	 */
	public function get_clubgegevens()
	{
		return $this->request('clubgegevens');
	}

	/**
	 * Haal club logo op
	 *
	 * @return array|WP_Error Club logo of fout
	 */
	public function get_clublogo()
	{
		return $this->request('clublogo');
	}

	/**
	 * Haal bestuur op
	 *
	 * @return array|WP_Error Bestuur gegevens of fout
	 */
	public function get_bestuur()
	{
		return $this->request('bestuur');
	}

	/**
	 * Haal commissies op
	 *
	 * @return array|WP_Error Commissies lijst of fout
	 */
	public function get_commissies()
	{
		return $this->request('commissies');
	}

	/**
	 * Haal commissie details op
	 *
	 * @param int $commissiecode Commissie code
	 * @return array|WP_Error Commissie details of fout
	 */
	public function get_commissie_details($commissiecode)
	{
		return $this->request('commissie-details', [
			'commissiecode' => $commissiecode
		]);
	}

	/**
	 * Haal commissie leden op
	 *
	 * @param int $commissiecode Commissie code
	 * @param bool $toonlidfoto Toon lid foto (standaard: false)
	 * @return array|WP_Error Commissie leden of fout
	 */
	public function get_commissie_leden($commissiecode, $toonlidfoto = false)
	{
		return $this->request('commissie-leden', [
			'commissiecode' => $commissiecode,
			'toonlidfoto' => $toonlidfoto ? 'JA' : 'NEE'
		]);
	}

	// ===================================================================
	// VERENIGINGSACTIVITEITEN
	// ===================================================================

	/**
	 * Haal verenigingsactiviteiten op
	 *
	 * @param array $args {
	 *     @type int    $aantaldagen   Aantal dagen vooruit (standaard: 7)
	 *     @type string $kalendersoort Filter op kalender soort
	 * }
	 * @return array|WP_Error Verenigingsactiviteiten of fout
	 */
	public function get_verenigingsactiviteiten($args = [])
	{
		$defaults = [
			'aantaldagen' => 7
		];

		$params = wp_parse_args($args, $defaults);
		return $this->request('verenigingsactiviteiten', $params);
	}

	/**
	 * Haal verjaardagen op
	 *
	 * @param int $aantaldagen Aantal dagen vooruit (standaard: 21)
	 * @return array|WP_Error Verjaardagen of fout
	 */
	public function get_verjaardagen($aantaldagen = 21)
	{
		return $this->request('verjaardagen', [
			'aantaldagen' => $aantaldagen
		]);
	}

	// ===================================================================
	// VRIJWILLIGERS
	// ===================================================================

	/**
	 * Haal vrijwilligerstaken op
	 *
	 * @return array|WP_Error Vrijwilligerstaken of fout
	 */
	public function get_vrijwilligerstaken()
	{
		return $this->request('vrijwilligerstaken');
	}

	/**
	 * Haal vrijwilligers voor taak op
	 *
	 * @param int $vrijwilligerstaakcode Vrijwilligerstaak code
	 * @param array $args {
	 *     @type int $weekoffset   Week offset (standaard: 0)
	 *     @type int $aantaldagen  Aantal dagen vooruit (standaard: 30)
	 * }
	 * @return array|WP_Error Vrijwilligers of fout
	 */
	public function get_vrijwilligers($vrijwilligerstaakcode, $args = [])
	{
		$defaults = [
			'weekoffset' => 0,
			'aantaldagen' => 30
		];

		$params = array_merge([
			'vrijwilligerstaakcode' => $vrijwilligerstaakcode
		], wp_parse_args($args, $defaults));

		return $this->request('vrijwilligers', $params);
	}

	// ===================================================================
	// SCHEIDSRECHTERS
	// ===================================================================

	/**
	 * Haal scheidsrechters aanstellingen op
	 *
	 * @param array $args {
	 *     @type int    $aantalregels      Aantal regels (standaard: 5)
	 *     @type int    $aantaldagen       Aantal dagen vooruit (standaard: 15)
	 *     @type int    $weekoffset        Week offset (standaard: 0)
	 *     @type string $sorteervolgorde   Sorteervolgorde (standaard: "datum")
	 * }
	 * @return array|WP_Error Scheidsrechters aanstellingen of fout
	 */
	public function get_scheidsrechtersaanstellingen($args = [])
	{
		$defaults = [
			'aantalregels' => 5,
			'aantaldagen' => 15,
			'weekoffset' => 0,
			'sorteervolgorde' => 'datum'
		];

		$params = wp_parse_args($args, $defaults);
		return $this->request('scheidsrechtersaanstellingen', $params);
	}

	// ===================================================================
	// TRAININGEN
	// ===================================================================

	/**
	 * Haal trainingen lijst op
	 *
	 * @return array|WP_Error Trainingen lijst of fout
	 */
	public function get_trainingenlijst()
	{
		return $this->request('trainingenlijst');
	}

	/**
	 * Haal training details op
	 *
	 * @param int $trainingid Training ID
	 * @param int $aantaldagen Aantal dagen vooruit (standaard: 30)
	 * @return array|WP_Error Training details of fout
	 */
	public function get_trainingdetails($trainingid, $aantaldagen = 30)
	{
		return $this->request('trainingdetails', [
			'trainingid' => $trainingid,
			'aantaldagen' => $aantaldagen
		]);
	}

	/**
	 * Haal team trainingen lijst op
	 *
	 * @param int $teamcode Team code
	 * @param int $lokaleteamcode Lokale team code
	 * @return array|WP_Error Team trainingen of fout
	 */
	public function get_team_trainingenlijst($teamcode, $lokaleteamcode)
	{
		return $this->request('team-trainingenlijst', [
			'teamcode' => $teamcode,
			'lokaleteamcode' => $lokaleteamcode
		]);
	}

	// ===================================================================
	// KEUZELIJSTEN (voor filters/dropdowns)
	// ===================================================================

	/**
	 * Haal competitiesoorten keuzelijst op
	 *
	 * @return array|WP_Error Competitiesoorten of fout
	 */
	public function get_keuzelijst_competitiesoorten()
	{
		return $this->request('keuzelijst-competitiesoorten');
	}

	/**
	 * Haal competitieperiode keuzelijst op
	 *
	 * @return array|WP_Error Competitieperiodes of fout
	 */
	public function get_keuzelijst_competitieperiode()
	{
		return $this->request('keuzelijst-competitieperiode');
	}

	/**
	 * Haal spelsoorten keuzelijst op
	 *
	 * @return array|WP_Error Spelsoorten of fout
	 */
	public function get_keuzelijst_spelsoorten()
	{
		return $this->request('keuzelijst-spelsoorten');
	}

	/**
	 * Haal leeftijdscategorieen keuzelijst op
	 *
	 * @return array|WP_Error Leeftijdscategorieen of fout
	 */
	public function get_keuzelijst_leeftijdscategorieen()
	{
		return $this->request('keuzelijst-leeftijdscategorieen');
	}

	/**
	 * Haal teamsoorten keuzelijst op
	 *
	 * @return array|WP_Error Teamsoorten of fout
	 */
	public function get_keuzelijst_teamsoorten()
	{
		return $this->request('keuzelijst-teamsoorten');
	}

	/**
	 * Haal geslacht keuzelijst op
	 *
	 * @return array|WP_Error Geslachten of fout
	 */
	public function get_keuzelijst_geslacht()
	{
		return $this->request('keuzelijst-geslacht');
	}

	/**
	 * Haal dagsoorten keuzelijst op
	 *
	 * @return array|WP_Error Dagsoorten of fout
	 */
	public function get_keuzelijst_dagsoorten()
	{
		return $this->request('keuzelijst-dagsoorten');
	}

	/**
	 * Haal wedstrijdtypes keuzelijst op
	 *
	 * @return array|WP_Error Wedstrijdtypes of fout
	 */
	public function get_keuzelijst_wedstrijdtypes()
	{
		return $this->request('keuzelijst-wedstrijdtypes');
	}

	/**
	 * Haal sorteervolgorde keuzelijst op
	 *
	 * @return array|WP_Error Sorteervolgordes of fout
	 */
	public function get_keuzelijst_sorteervolgordes()
	{
		return $this->request('keuzelijst-sorteervolgordes');
	}

	/**
	 * Haal boolean keuzelijst op (JA/NEE)
	 *
	 * @return array|WP_Error Boolean waarden of fout
	 */
	public function get_keuzelijst_boolean()
	{
		return $this->request('keuzelijst-boolean');
	}

	/**
	 * Haal periodenummers voor poule op
	 *
	 * @param int $poulecode Poule code
	 * @return array|WP_Error Periodenummers of fout
	 */
	public function get_keuzelijst_periodenummers($poulecode)
	{
		return $this->request('keuzelijst-periodenummers', [
			'poulecode' => $poulecode
		]);
	}

	// ===================================================================
	// HELPER METHODS
	// ===================================================================

	/**
	 * Creëer een API instance met standaard instellingen
	 *
	 * @return Sportlink_API|null API instance of null bij ontbrekende configuratie
	 */
	public static function create_from_settings()
	{
		$client_id = get_option('sportlink_club_dataservices_client_id');
		$cache_time = get_option('sportlink_club_dataservices_cachetime', 30);

		if (empty($client_id)) {
			return null;
		}

		return new self($client_id, $cache_time);
	}
}
