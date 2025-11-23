<?php

/**
 * Quick Start Voorbeeld - Kopieer en pas aan naar wens
 *
 * Dit bestand bevat kant-en-klare shortcodes die je direct kan gebruiken.
 * Voeg deze toe aan je theme's functions.php of maak er een custom plugin van.
 */

// ============================================================================
// SHORTCODE: [sportlink_teams]
// Toont een lijst van alle teams
// ============================================================================

function sportlink_teams_shortcode($atts)
{
	$api = Sportlink_API::create_from_settings();

	if (!$api) {
		return '<p class="sportlink-error">Sportlink API niet geconfigureerd.</p>';
	}

	$atts = shortcode_atts([
		'geslacht' => 'ALLES',
		'leeftijd' => 'ALLES',
		'spelsoort' => 'ALLES'
	], $atts);

	$teams = $api->get_teams([
		'geslacht' => $atts['geslacht'],
		'leeftijdscategorie' => $atts['leeftijd'],
		'spelsoort' => $atts['spelsoort']
	]);

	if (is_wp_error($teams)) {
		return '<p class="sportlink-error">Fout bij ophalen teams.</p>';
	}

	if (empty($teams)) {
		return '<p>Geen teams gevonden.</p>';
	}

	$output = '<div class="sportlink-teams">';
	$output .= '<ul>';
	foreach ($teams as $team) {
		$output .= '<li>';
		$output .= '<strong>' . esc_html($team['teamnaam']) . '</strong>';
		if (!empty($team['klassepoule'])) {
			$output .= ' - ' . esc_html($team['klassepoule']);
		}
		$output .= '</li>';
	}
	$output .= '</ul>';
	$output .= '</div>';

	return $output;
}
add_shortcode('sportlink_teams', 'sportlink_teams_shortcode');


// ============================================================================
// SHORTCODE: [sportlink_programma teamcode="123"]
// Toont komende wedstrijden
// ============================================================================

function sportlink_programma_shortcode($atts)
{
	$api = Sportlink_API::create_from_settings();

	if (!$api) {
		return '<p class="sportlink-error">Sportlink API niet geconfigureerd.</p>';
	}

	$atts = shortcode_atts([
		'teamcode' => null,
		'aantal' => 5,
		'dagen' => 30
	], $atts);

	$params = [
		'aantalregels' => intval($atts['aantal']),
		'aantaldagen' => intval($atts['dagen']),
		'sorteervolgorde' => 'datum'
	];

	if ($atts['teamcode']) {
		$params['teamcode'] = intval($atts['teamcode']);
	}

	$programma = $api->get_programma($params);

	if (is_wp_error($programma)) {
		return '<p class="sportlink-error">Fout bij ophalen programma.</p>';
	}

	if (empty($programma)) {
		return '<p>Geen wedstrijden gepland.</p>';
	}

	$output = '<div class="sportlink-programma">';
	$output .= '<table>';
	$output .= '<thead><tr>';
	$output .= '<th>Datum</th><th>Tijd</th><th>Wedstrijd</th><th>Locatie</th>';
	$output .= '</tr></thead>';
	$output .= '<tbody>';

	foreach ($programma as $wedstrijd) {
		$output .= '<tr>';
		$output .= '<td>' . esc_html($wedstrijd['datum']) . '</td>';
		$output .= '<td>' . esc_html($wedstrijd['aanvangstijd']) . '</td>';
		$output .= '<td>' . esc_html($wedstrijd['wedstrijd']) . '</td>';
		$output .= '<td>' . esc_html($wedstrijd['accommodatie']) . '</td>';
		$output .= '</tr>';
	}

	$output .= '</tbody></table>';
	$output .= '</div>';

	return $output;
}
add_shortcode('sportlink_programma', 'sportlink_programma_shortcode');


// ============================================================================
// SHORTCODE: [sportlink_uitslagen teamcode="123"]
// Toont recente uitslagen
// ============================================================================

function sportlink_uitslagen_shortcode($atts)
{
	$api = Sportlink_API::create_from_settings();

	if (!$api) {
		return '<p class="sportlink-error">Sportlink API niet geconfigureerd.</p>';
	}

	$atts = shortcode_atts([
		'teamcode' => null,
		'aantal' => 5
	], $atts);

	$params = [
		'aantalregels' => intval($atts['aantal']),
		'weekoffset' => -1,
		'aantaldagen' => 30
	];

	if ($atts['teamcode']) {
		$params['teamcode'] = intval($atts['teamcode']);
	}

	$uitslagen = $api->get_uitslagen($params);

	if (is_wp_error($uitslagen)) {
		return '<p class="sportlink-error">Fout bij ophalen uitslagen.</p>';
	}

	if (empty($uitslagen)) {
		return '<p>Geen recente uitslagen.</p>';
	}

	$output = '<div class="sportlink-uitslagen">';
	$output .= '<table>';
	$output .= '<thead><tr>';
	$output .= '<th>Datum</th><th>Wedstrijd</th><th>Uitslag</th>';
	$output .= '</tr></thead>';
	$output .= '<tbody>';

	foreach ($uitslagen as $wedstrijd) {
		$output .= '<tr>';
		$output .= '<td>' . esc_html($wedstrijd['datum']) . '</td>';
		$output .= '<td>' . esc_html($wedstrijd['wedstrijd']) . '</td>';
		$output .= '<td><strong>' . esc_html($wedstrijd['uitslag']) . '</strong></td>';
		$output .= '</tr>';
	}

	$output .= '</tbody></table>';
	$output .= '</div>';

	return $output;
}
add_shortcode('sportlink_uitslagen', 'sportlink_uitslagen_shortcode');


// ============================================================================
// SHORTCODE: [sportlink_stand poulecode="12345"]
// Toont poule stand
// ============================================================================

function sportlink_stand_shortcode($atts)
{
	$api = Sportlink_API::create_from_settings();

	if (!$api) {
		return '<p class="sportlink-error">Sportlink API niet geconfigureerd.</p>';
	}

	$atts = shortcode_atts([
		'poulecode' => null
	], $atts);

	if (!$atts['poulecode']) {
		return '<p class="sportlink-error">Geen poulecode opgegeven. Gebruik: [sportlink_stand poulecode="12345"]</p>';
	}

	$stand = $api->get_poulestand(intval($atts['poulecode']));

	if (is_wp_error($stand)) {
		return '<p class="sportlink-error">Fout bij ophalen stand.</p>';
	}

	if (empty($stand)) {
		return '<p>Geen stand beschikbaar.</p>';
	}

	$output = '<div class="sportlink-stand">';
	$output .= '<table>';
	$output .= '<thead><tr>';
	$output .= '<th>Pos</th><th>Team</th><th>G</th><th>W</th><th>GL</th><th>V</th><th>DV</th><th>DT</th><th>DS</th><th>Pnt</th>';
	$output .= '</tr></thead>';
	$output .= '<tbody>';

	foreach ($stand as $team) {
		$eigenteam = (!empty($team['eigenteam']) && $team['eigenteam']) ? ' class="eigenteam"' : '';
		$output .= '<tr' . $eigenteam . '>';
		$output .= '<td>' . esc_html($team['positie']) . '</td>';
		$output .= '<td>' . esc_html($team['teamnaam']) . '</td>';
		$output .= '<td>' . esc_html($team['gespeeldewedstrijden']) . '</td>';
		$output .= '<td>' . esc_html($team['gewonnen']) . '</td>';
		$output .= '<td>' . esc_html($team['gelijk']) . '</td>';
		$output .= '<td>' . esc_html($team['verloren']) . '</td>';
		$output .= '<td>' . esc_html($team['doelpuntenvoor']) . '</td>';
		$output .= '<td>' . esc_html($team['doelpuntentegen']) . '</td>';
		$output .= '<td>' . esc_html($team['doelsaldo']) . '</td>';
		$output .= '<td><strong>' . esc_html($team['punten']) . '</strong></td>';
		$output .= '</tr>';
	}

	$output .= '</tbody></table>';
	$output .= '</div>';

	// Optionele CSS
	$output .= '<style>
        .sportlink-stand table { width: 100%; border-collapse: collapse; }
        .sportlink-stand th, .sportlink-stand td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .sportlink-stand th { background-color: #f4f4f4; font-weight: bold; }
        .sportlink-stand tr.eigenteam { background-color: #fff3cd; font-weight: bold; }
        .sportlink-stand tr:hover { background-color: #f9f9f9; }
    </style>';

	return $output;
}
add_shortcode('sportlink_stand', 'sportlink_stand_shortcode');


// ============================================================================
// SHORTCODE: [sportlink_clubinfo]
// Toont club informatie
// ============================================================================

function sportlink_clubinfo_shortcode()
{
	$api = Sportlink_API::create_from_settings();

	if (!$api) {
		return '<p class="sportlink-error">Sportlink API niet geconfigureerd.</p>';
	}

	$club = $api->get_clubgegevens();

	if (is_wp_error($club)) {
		return '<p class="sportlink-error">Fout bij ophalen club gegevens.</p>';
	}

	$gegevens = $club['gegevens'];

	$output = '<div class="sportlink-clubinfo">';

	if (!empty($gegevens['logo'])) {
		$output .= '<div class="club-logo">';
		$output .= '<img src="' . esc_url($gegevens['logo']) . '" alt="' . esc_attr($gegevens['clubnaam']) . ' logo" style="max-width: 200px;">';
		$output .= '</div>';
	}

	$output .= '<h3>' . esc_html($gegevens['clubnaam']) . '</h3>';

	if (!empty($gegevens['informatie'])) {
		$output .= '<p>' . nl2br(esc_html($gegevens['informatie'])) . '</p>';
	}

	$output .= '<div class="club-contact">';

	if (!empty($gegevens['straatnaam'])) {
		$output .= '<p><strong>Adres:</strong><br>';
		$output .= esc_html($gegevens['straatnaam']) . ' ' . esc_html($gegevens['huisnummer']) . '<br>';
		$output .= esc_html($gegevens['postcode']) . ' ' . esc_html($gegevens['plaats']) . '</p>';
	}

	if (!empty($gegevens['email'])) {
		$output .= '<p><strong>Email:</strong> ';
		$output .= '<a href="mailto:' . esc_attr($gegevens['email']) . '">' . esc_html($gegevens['email']) . '</a></p>';
	}

	if (!empty($gegevens['website'])) {
		$output .= '<p><strong>Website:</strong> ';
		$output .= '<a href="' . esc_url($gegevens['website']) . '" target="_blank">' . esc_html($gegevens['website']) . '</a></p>';
	}

	$output .= '</div>';
	$output .= '</div>';

	return $output;
}
add_shortcode('sportlink_clubinfo', 'sportlink_clubinfo_shortcode');


// ============================================================================
// BASIS STYLING
// Voeg dit toe aan je CSS bestand of theme
// ============================================================================

/*
.sportlink-error {
    background: #f8d7da;
    border-left: 4px solid #dc3545;
    padding: 12px 16px;
    margin: 16px 0;
    border-radius: 4px;
    color: #721c24;
}

.sportlink-teams ul {
    list-style: none;
    padding: 0;
}

.sportlink-teams li {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.sportlink-programma table,
.sportlink-uitslagen table,
.sportlink-stand table {
    width: 100%;
    border-collapse: collapse;
}

.sportlink-programma th,
.sportlink-programma td,
.sportlink-uitslagen th,
.sportlink-uitslagen td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.sportlink-programma th,
.sportlink-uitslagen th {
    background-color: #f4f4f4;
    font-weight: bold;
}
*/

// ============================================================================
// GEBRUIK IN WORDPRESS
// ============================================================================

/*
In posts/pages:

[sportlink_teams]
[sportlink_teams geslacht="MAN" leeftijd="SENIOREN"]

[sportlink_programma teamcode="12345" aantal="10"]

[sportlink_uitslagen teamcode="12345" aantal="5"]

[sportlink_stand poulecode="67890"]

[sportlink_clubinfo]

*/
