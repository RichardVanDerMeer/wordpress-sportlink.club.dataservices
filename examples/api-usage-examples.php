<?php

/**
 * Sportlink API Gebruiksvoorbeelden
 *
 * Dit bestand bevat praktische voorbeelden van hoe je de Sportlink API interface kunt gebruiken.
 * Kopieer en pas deze voorbeelden aan voor je eigen implementatie.
 *
 * @package Sportlink
 * @version 1.0.0
 */

// BELANGRIJK: Dit bestand is alleen voor documentatie doeleinden
// Voeg dit NIET direct toe aan je WordPress installatie

// ============================================================================
// VOORBEELD 1: Basis gebruik - Teams ophalen
// ============================================================================

function mijn_teams_shortcode()
{
	// Maak een API instance aan met de instellingen uit WordPress
	$api = Sportlink_API::create_from_settings();

	if (!$api) {
		return '<p>Sportlink API niet geconfigureerd. Ga naar Instellingen > Sportlink.</p>';
	}

	// Haal alle teams op
	$teams = $api->get_teams();

	// Of met specifieke filters
	$teams = $api->get_teams([
		'geslacht' => 'MAN',
		'leeftijdscategorie' => 'SENIOREN',
		'spelsoort' => 'VELD'
	]);

	// Check voor fouten
	if (is_wp_error($teams)) {
		return '<p class="sportlink-error">Fout bij ophalen teams: ' . esc_html($teams->get_error_message()) . '</p>';
	}

	// Toon teams
	$output = '<ul class="sportlink-teams">';
	foreach ($teams as $team) {
		$output .= '<li>' . esc_html($team['teamnaam']) . ' - ' . esc_html($team['klassepoule']) . '</li>';
	}
	$output .= '</ul>';

	return $output;
}
add_shortcode('mijn_teams', 'mijn_teams_shortcode');


// ============================================================================
// VOORBEELD 2: Wedstrijd programma ophalen
// ============================================================================

function mijn_programma_shortcode($atts)
{
	// Parse shortcode attributen
	$atts = shortcode_atts([
		'teamcode' => null,
		'aantaldagen' => 30,
		'thuis' => 'JA',
		'uit' => 'JA'
	], $atts);

	$api = Sportlink_API::create_from_settings();
	if (!$api) {
		return '<p>API niet geconfigureerd</p>';
	}

	// Haal programma op met filters
	$programma = $api->get_programma([
		'teamcode' => $atts['teamcode'],
		'aantaldagen' => intval($atts['aantaldagen']),
		'thuis' => $atts['thuis'],
		'uit' => $atts['uit'],
		'sorteervolgorde' => 'datum'
	]);

	if (is_wp_error($programma)) {
		return '<p class="sportlink-error">' . esc_html($programma->get_error_message()) . '</p>';
	}

	// Render programma
	$output = '<div class="sportlink-programma">';
	foreach ($programma as $wedstrijd) {
		$output .= '<div class="wedstrijd">';
		$output .= '<span class="datum">' . esc_html($wedstrijd['datum']) . '</span> ';
		$output .= '<span class="tijd">' . esc_html($wedstrijd['aanvangstijd']) . '</span> - ';
		$output .= '<span class="wedstrijd">' . esc_html($wedstrijd['wedstrijd']) . '</span>';
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}
add_shortcode('mijn_programma', 'mijn_programma_shortcode');


// ============================================================================
// VOORBEELD 3: Poule stand weergeven
// ============================================================================

function mijn_stand_shortcode($atts)
{
	$atts = shortcode_atts([
		'poulecode' => null
	], $atts);

	if (!$atts['poulecode']) {
		return '<p>Geen poulecode opgegeven</p>';
	}

	$api = Sportlink_API::create_from_settings();
	if (!$api) {
		return '<p>API niet geconfigureerd</p>';
	}

	// Haal stand op
	$stand = $api->get_poulestand(intval($atts['poulecode']));

	if (is_wp_error($stand)) {
		return '<p class="sportlink-error">' . esc_html($stand->get_error_message()) . '</p>';
	}

	// Render als tabel
	$output = '<table class="sportlink-stand">';
	$output .= '<thead><tr>';
	$output .= '<th>Pos</th><th>Team</th><th>G</th><th>W</th><th>GL</th><th>V</th><th>+/-</th><th>Pnt</th>';
	$output .= '</tr></thead>';
	$output .= '<tbody>';

	foreach ($stand as $team) {
		$eigenteam = isset($team['eigenteam']) && $team['eigenteam'] ? ' class="eigenteam"' : '';
		$output .= '<tr' . $eigenteam . '>';
		$output .= '<td>' . esc_html($team['positie']) . '</td>';
		$output .= '<td>' . esc_html($team['teamnaam']) . '</td>';
		$output .= '<td>' . esc_html($team['gespeeldewedstrijden']) . '</td>';
		$output .= '<td>' . esc_html($team['gewonnen']) . '</td>';
		$output .= '<td>' . esc_html($team['gelijk']) . '</td>';
		$output .= '<td>' . esc_html($team['verloren']) . '</td>';
		$output .= '<td>' . esc_html($team['doelsaldo']) . '</td>';
		$output .= '<td>' . esc_html($team['punten']) . '</td>';
		$output .= '</tr>';
	}

	$output .= '</tbody></table>';

	return $output;
}
add_shortcode('mijn_stand', 'mijn_stand_shortcode');


// ============================================================================
// VOORBEELD 4: Wedstrijd details pagina
// ============================================================================

function mijn_wedstrijd_details_shortcode($atts)
{
	$atts = shortcode_atts([
		'wedstrijdcode' => null
	], $atts);

	// Probeer wedstrijdcode uit URL te halen
	if (!$atts['wedstrijdcode'] && isset($_GET['wedstrijdcode'])) {
		$atts['wedstrijdcode'] = intval($_GET['wedstrijdcode']);
	}

	if (!$atts['wedstrijdcode']) {
		return '<p>Geen wedstrijdcode opgegeven</p>';
	}

	$api = Sportlink_API::create_from_settings();
	if (!$api) {
		return '<p>API niet geconfigureerd</p>';
	}

	$wedstrijdcode = intval($atts['wedstrijdcode']);

	// Haal verschillende wedstrijd gegevens op
	$info = $api->get_wedstrijd_informatie($wedstrijdcode);
	$thuisteam = $api->get_wedstrijd_thuisteam($wedstrijdcode);
	$uitteam = $api->get_wedstrijd_uitteam($wedstrijdcode);
	$accommodatie = $api->get_wedstrijd_accommodatie($wedstrijdcode);

	// Check voor fouten
	if (is_wp_error($info)) {
		return '<p class="sportlink-error">' . esc_html($info->get_error_message()) . '</p>';
	}

	// Render wedstrijd details
	$output = '<div class="sportlink-wedstrijd-details">';

	// Basis informatie
	$output .= '<h2>' . esc_html($info['wedstrijdinformatie']['thuisteam']) . ' - ' . esc_html($info['wedstrijdinformatie']['uitteam']) . '</h2>';
	$output .= '<p><strong>Datum:</strong> ' . esc_html($info['wedstrijdinformatie']['wedstrijddatum']) . '</p>';
	$output .= '<p><strong>Aanvang:</strong> ' . esc_html($info['wedstrijdinformatie']['aanvangstijd']) . '</p>';
	$output .= '<p><strong>Locatie:</strong> ' . esc_html($info['wedstrijdinformatie']['veldlocatie']) . '</p>';

	// Uitslag (als bekend)
	if (!empty($info['wedstrijdinformatie']['thuisscore'])) {
		$output .= '<div class="uitslag">';
		$output .= '<h3>Uitslag: ' . esc_html($info['wedstrijdinformatie']['thuisscore']) . ' - ' . esc_html($info['wedstrijdinformatie']['uitscore']) . '</h3>';
		$output .= '</div>';
	}

	// Accommodatie informatie
	if (!is_wp_error($accommodatie) && !empty($accommodatie['wedstrijd'])) {
		$output .= '<div class="accommodatie">';
		$output .= '<h3>Accommodatie</h3>';
		$output .= '<p>' . esc_html($accommodatie['wedstrijd']['accommodatienaam']) . '</p>';
		$output .= '<p>' . esc_html($accommodatie['wedstrijd']['adres']) . ', ' . esc_html($accommodatie['wedstrijd']['plaats']) . '</p>';
		$output .= '</div>';
	}

	// Thuisteam samenstelling
	if (!is_wp_error($thuisteam) && !empty($thuisteam)) {
		$output .= '<div class="thuisteam">';
		$output .= '<h3>Thuisteam</h3>';
		$output .= '<ul>';
		foreach ($thuisteam as $speler) {
			$output .= '<li>' . esc_html($speler['naam']) . ' (' . esc_html($speler['rol']) . ')</li>';
		}
		$output .= '</ul>';
		$output .= '</div>';
	}

	// Uitteam samenstelling
	if (!is_wp_error($uitteam) && !empty($uitteam)) {
		$output .= '<div class="uitteam">';
		$output .= '<h3>Uitteam</h3>';
		$output .= '<ul>';
		foreach ($uitteam as $speler) {
			$output .= '<li>' . esc_html($speler['naam']) . ' (' . esc_html($speler['rol']) . ')</li>';
		}
		$output .= '</ul>';
		$output .= '</div>';
	}

	$output .= '</div>';

	return $output;
}
add_shortcode('mijn_wedstrijd_details', 'mijn_wedstrijd_details_shortcode');


// ============================================================================
// VOORBEELD 5: Club gegevens weergeven
// ============================================================================

function mijn_clubinfo_shortcode()
{
	$api = Sportlink_API::create_from_settings();
	if (!$api) {
		return '<p>API niet geconfigureerd</p>';
	}

	$club = $api->get_clubgegevens();

	if (is_wp_error($club)) {
		return '<p class="sportlink-error">' . esc_html($club->get_error_message()) . '</p>';
	}

	$gegevens = $club['gegevens'];

	$output = '<div class="sportlink-clubinfo">';
	$output .= '<h2>' . esc_html($gegevens['clubnaam']) . '</h2>';
	$output .= '<p>' . nl2br(esc_html($gegevens['informatie'])) . '</p>';

	if (!empty($gegevens['logo'])) {
		$output .= '<img src="' . esc_url($gegevens['logo']) . '" alt="' . esc_attr($gegevens['clubnaam']) . ' logo">';
	}

	$output .= '<div class="contactgegevens">';
	$output .= '<p><strong>Adres:</strong><br>';
	$output .= esc_html($gegevens['straatnaam']) . ' ' . esc_html($gegevens['huisnummer']) . '<br>';
	$output .= esc_html($gegevens['postcode']) . ' ' . esc_html($gegevens['plaats']) . '</p>';

	if (!empty($gegevens['email'])) {
		$output .= '<p><strong>Email:</strong> <a href="mailto:' . esc_attr($gegevens['email']) . '">' . esc_html($gegevens['email']) . '</a></p>';
	}

	if (!empty($gegevens['website'])) {
		$output .= '<p><strong>Website:</strong> <a href="' . esc_url($gegevens['website']) . '" target="_blank">' . esc_html($gegevens['website']) . '</a></p>';
	}
	$output .= '</div>';

	$output .= '</div>';

	return $output;
}
add_shortcode('mijn_clubinfo', 'mijn_clubinfo_shortcode');


// ============================================================================
// VOORBEELD 6: Gebruik in custom WordPress queries
// ============================================================================

function mijn_custom_widget_function()
{
	$api = Sportlink_API::create_from_settings();
	if (!$api) {
		return;
	}

	// Haal komende 3 wedstrijden op
	$programma = $api->get_programma([
		'aantalregels' => 3,
		'aantaldagen' => 14,
		'sorteervolgorde' => 'datum'
	]);

	if (is_wp_error($programma) || empty($programma)) {
		echo '<p>Geen komende wedstrijden</p>';
		return;
	}

	echo '<div class="widget-programma">';
	echo '<h3>Eerstvolgende wedstrijden</h3>';

	foreach ($programma as $wedstrijd) {
		echo '<div class="wedstrijd-item">';
		echo '<span class="datum">' . esc_html($wedstrijd['datum']) . '</span><br>';
		echo '<strong>' . esc_html($wedstrijd['wedstrijd']) . '</strong><br>';
		echo '<small>' . esc_html($wedstrijd['aanvangstijd']) . ' - ' . esc_html($wedstrijd['accommodatie']) . '</small>';
		echo '</div>';
	}

	echo '</div>';
}


// ============================================================================
// VOORBEELD 7: Keuzelijsten gebruiken voor filters
// ============================================================================

function mijn_teams_filter_form()
{
	$api = Sportlink_API::create_from_settings();
	if (!$api) {
		return '<p>API niet geconfigureerd</p>';
	}

	// Haal keuzelijsten op voor dropdowns
	$geslachten = $api->get_keuzelijst_geslacht();
	$leeftijdscategorieen = $api->get_keuzelijst_leeftijdscategorieen();
	$spelsoorten = $api->get_keuzelijst_spelsoorten();

	$output = '<form method="get" class="sportlink-filter">';

	// Geslacht dropdown
	if (!is_wp_error($geslachten)) {
		$output .= '<label>Geslacht: <select name="geslacht">';
		foreach ($geslachten as $item) {
			$selected = (isset($_GET['geslacht']) && $_GET['geslacht'] === $item['waarde']) ? ' selected' : '';
			$output .= '<option value="' . esc_attr($item['waarde']) . '"' . $selected . '>' . esc_html($item['omschrijving']) . '</option>';
		}
		$output .= '</select></label>';
	}

	// Leeftijdscategorie dropdown
	if (!is_wp_error($leeftijdscategorieen)) {
		$output .= '<label>Leeftijd: <select name="leeftijd">';
		foreach ($leeftijdscategorieen as $item) {
			$selected = (isset($_GET['leeftijd']) && $_GET['leeftijd'] === $item['waarde']) ? ' selected' : '';
			$output .= '<option value="' . esc_attr($item['waarde']) . '"' . $selected . '>' . esc_html($item['omschrijving']) . '</option>';
		}
		$output .= '</select></label>';
	}

	// Spelsoort dropdown
	if (!is_wp_error($spelsoorten)) {
		$output .= '<label>Spelsoort: <select name="spelsoort">';
		foreach ($spelsoorten as $item) {
			$selected = (isset($_GET['spelsoort']) && $_GET['spelsoort'] === $item['waarde']) ? ' selected' : '';
			$output .= '<option value="' . esc_attr($item['waarde']) . '"' . $selected . '>' . esc_html($item['omschrijving']) . '</option>';
		}
		$output .= '</select></label>';
	}

	$output .= '<button type="submit">Filter</button>';
	$output .= '</form>';

	// Nu teams ophalen met de filters
	if (isset($_GET['geslacht']) || isset($_GET['leeftijd']) || isset($_GET['spelsoort'])) {
		$teams = $api->get_teams([
			'geslacht' => isset($_GET['geslacht']) ? sanitize_text_field($_GET['geslacht']) : 'ALLES',
			'leeftijdscategorie' => isset($_GET['leeftijd']) ? sanitize_text_field($_GET['leeftijd']) : 'ALLES',
			'spelsoort' => isset($_GET['spelsoort']) ? sanitize_text_field($_GET['spelsoort']) : 'ALLES'
		]);

		if (!is_wp_error($teams)) {
			$output .= '<ul class="teams-lijst">';
			foreach ($teams as $team) {
				$output .= '<li>' . esc_html($team['teamnaam']) . '</li>';
			}
			$output .= '</ul>';
		}
	}

	return $output;
}
add_shortcode('mijn_teams_filter', 'mijn_teams_filter_form');


// ============================================================================
// VOORBEELD 8: Error handling en fallback content
// ============================================================================

function mijn_robuust_programma_widget()
{
	$api = Sportlink_API::create_from_settings();

	if (!$api) {
		// Fallback als API niet geconfigureerd is
		return '<div class="sportlink-notice">Sportlink nog niet geconfigureerd. Ga naar de WordPress admin om de plugin in te stellen.</div>';
	}

	$programma = $api->get_programma([
		'aantalregels' => 5,
		'aantaldagen' => 7
	]);

	// Check voor verschillende soorten fouten
	if (is_wp_error($programma)) {
		$error_message = $programma->get_error_message();

		// Log de fout voor debugging
		error_log('Sportlink API Error: ' . $error_message);

		// Toon gebruiksvriendelijke foutmelding
		if (strpos($error_message, 'cache') !== false) {
			return '<div class="sportlink-notice--warning">De wedstrijdgegevens kunnen tijdelijk niet worden opgehaald. Probeer het later opnieuw.</div>';
		}

		return '<div class="sportlink-error">Er is een fout opgetreden bij het ophalen van het programma.</div>';
	}

	// Check of er data is
	if (empty($programma)) {
		return '<div class="sportlink-notice">Er staan momenteel geen wedstrijden gepland.</div>';
	}

	// Render normaal programma
	$output = '<div class="sportlink-programma">';
	foreach ($programma as $wedstrijd) {
		$output .= '<div class="wedstrijd">';
		$output .= esc_html($wedstrijd['datum']) . ' - ' . esc_html($wedstrijd['wedstrijd']);
		$output .= '</div>';
	}
	$output .= '</div>';

	return $output;
}
add_shortcode('robuust_programma', 'mijn_robuust_programma_widget');


// ============================================================================
// TIPS & BEST PRACTICES
// ============================================================================

/*
 * TIP 1: Gebruik altijd is_wp_error() om fouten te checken
 *
 * $data = $api->get_teams();
 * if (is_wp_error($data)) {
 *     // Handle error
 * }
 */

/*
 * TIP 2: Escape alle output met esc_html(), esc_attr(), esc_url()
 *
 * echo '<p>' . esc_html($team['teamnaam']) . '</p>';
 * echo '<img src="' . esc_url($logo) . '" alt="' . esc_attr($alt) . '">';
 */

/*
 * TIP 3: Gebruik wp_parse_args() voor default parameters
 *
 * $atts = shortcode_atts([
 *     'teamcode' => null,
 *     'aantaldagen' => 30
 * ], $atts);
 */

/*
 * TIP 4: Cache is automatisch - je hoeft niets extra's te doen
 *
 * De API klasse gebruikt de bestaande cache functionaliteit met circuit breaker.
 */

/*
 * TIP 5: Sanitize user input altijd
 *
 * $teamcode = isset($_GET['teamcode']) ? intval($_GET['teamcode']) : null;
 * $naam = isset($_POST['naam']) ? sanitize_text_field($_POST['naam']) : '';
 */
