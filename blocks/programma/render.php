<?php

/**
 * Sportlink Programma Block - Server-side Rendering
 *
 * @package Sportlink
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Get block attributes
$teamcode = isset($attributes['teamcode']) ? sanitize_text_field($attributes['teamcode']) : '';
$aantalregels = isset($attributes['aantalregels']) ? absint($attributes['aantalregels']) : 10;
$aantaldagen = isset($attributes['aantaldagen']) ? absint($attributes['aantaldagen']) : 30;
$weekoffset = isset($attributes['weekoffset']) ? intval($attributes['weekoffset']) : 0;
$eigenwedstrijden = isset($attributes['eigenwedstrijden']) ? strtoupper(sanitize_key($attributes['eigenwedstrijden'])) : 'JA';
$thuis = isset($attributes['thuis']) ? strtoupper(sanitize_key($attributes['thuis'])) : 'JA';
$uit = isset($attributes['uit']) ? strtoupper(sanitize_key($attributes['uit'])) : 'JA';
$spelsoort = isset($attributes['spelsoort']) ? strtoupper(sanitize_key($attributes['spelsoort'])) : 'ALLES';
$competitiesoort = isset($attributes['competitiesoort']) ? strtoupper(sanitize_key($attributes['competitiesoort'])) : 'ALLES';
$dagsoort = isset($attributes['dagsoort']) ? strtoupper(sanitize_key($attributes['dagsoort'])) : 'ALLES';
$leeftijdscategorie = isset($attributes['leeftijdscategorie']) ? strtoupper(sanitize_key($attributes['leeftijdscategorie'])) : 'ALLES';
$sorteervolgorde = isset($attributes['sorteervolgorde']) ? sanitize_key($attributes['sorteervolgorde']) : 'datum';
$gebruiklokaleteamgegevens = isset($attributes['gebruiklokaleteamgegevens']) ? strtoupper(sanitize_key($attributes['gebruiklokaleteamgegevens'])) : 'NEE';

// Display options
$show_time = isset($attributes['showTime']) ? (bool) $attributes['showTime'] : true;
$show_date = isset($attributes['showDate']) ? (bool) $attributes['showDate'] : true;
$show_location = isset($attributes['showLocation']) ? (bool) $attributes['showLocation'] : true;
$show_referee = isset($attributes['showReferee']) ? (bool) $attributes['showReferee'] : false;
$show_field = isset($attributes['showField']) ? (bool) $attributes['showField'] : false;
$show_remarks = isset($attributes['showRemarks']) ? (bool) $attributes['showRemarks'] : false;
$show_competition = isset($attributes['showCompetition']) ? (bool) $attributes['showCompetition'] : false;
$show_logos = isset($attributes['showLogos']) ? (bool) $attributes['showLogos'] : false;
$logo_width = isset($attributes['logoWidth']) ? absint($attributes['logoWidth']) : 60;
$show_versus = isset($attributes['showVersus']) ? (bool) $attributes['showVersus'] : true;
$group_by_date = isset($attributes['groupByDate']) ? (bool) $attributes['groupByDate'] : false;
$font_size = isset($attributes['fontSize']) ? sanitize_key($attributes['fontSize']) : 'medium';
$column_order = isset($attributes['columnOrder']) && is_array($attributes['columnOrder'])
	? $attributes['columnOrder']
	: ['time', 'home', 'versus', 'away', 'location', 'field', 'referee', 'competition', 'remarks'];
$display_style = isset($attributes['displayStyle']) ? sanitize_key($attributes['displayStyle']) : 'table';

// Column labels mapping
$column_labels = [
	'time' => __('Tijd', 'sportlink'),
	'home' => __('Thuis', 'sportlink'),
	'versus' => '',
	'away' => __('Uit', 'sportlink'),
	'location' => __('Locatie', 'sportlink'),
	'field' => __('Veld', 'sportlink'),
	'referee' => __('Scheidsrechter', 'sportlink'),
	'competition' => __('Competitie', 'sportlink'),
	'remarks' => __('Opmerking', 'sportlink')
];

// Helper function to render a column
$render_column = function ($column_type, $wedstrijd) use ($show_time, $show_location, $show_field, $show_referee, $show_competition, $show_remarks, $show_logos, $logo_width, $show_versus) {
	switch ($column_type) {
		case 'time':
			if (!$show_time) return;
			echo '<td class="tijd">' . esc_html($wedstrijd['aanvangstijd'] ?? '') . '</td>';
			break;
		case 'home':
			echo '<td class="team-thuis">';
			if ($show_logos && !empty($wedstrijd['thuisteamlogo'])) {
				echo '<img src="' . esc_url($wedstrijd['thuisteamlogo']) . '" alt="" class="team-logo" style="width: ' . esc_attr($logo_width) . 'px; height: auto; margin-right: 5px;"> ';
			}
			echo esc_html($wedstrijd['thuisteam'] ?? '');
			echo '</td>';
			break;
		case 'versus':
			if (!$show_versus) return;
			echo '<td class="versus">-</td>';
			break;
		case 'away':
			echo '<td class="team-uit">';
			echo esc_html($wedstrijd['uitteam'] ?? '');
			if ($show_logos && !empty($wedstrijd['uitteamlogo'])) {
				echo ' <img src="' . esc_url($wedstrijd['uitteamlogo']) . '" alt="" class="team-logo" style="width: ' . esc_attr($logo_width) . 'px; height: auto; margin-left: 5px;">';
			}
			echo '</td>';
			break;
		case 'location':
			if (!$show_location) return;
			echo '<td class="locatie">' . esc_html($wedstrijd['accommodatie'] ?? '') . '</td>';
			break;
		case 'field':
			if (!$show_field) return;
			echo '<td class="veld">' . esc_html($wedstrijd['veld'] ?? '') . '</td>';
			break;
		case 'referee':
			if (!$show_referee) return;
			echo '<td class="scheidsrechter">' . esc_html($wedstrijd['scheidsrechter'] ?? '') . '</td>';
			break;
		case 'competition':
			if (!$show_competition) return;
			echo '<td class="competitie">' . esc_html($wedstrijd['competitienaam'] ?? '') . '</td>';
			break;
		case 'remarks':
			if (!$show_remarks) return;
			echo '<td class="opmerking">' . esc_html($wedstrijd['bijzonderheden'] ?? '') . '</td>';
			break;
	}
};

// Build API arguments
$args = [
	'aantalregels' => $aantalregels,
	'aantaldagen' => $aantaldagen,
	'weekoffset' => $weekoffset,
	'eigenwedstrijden' => $eigenwedstrijden,
	'thuis' => $thuis,
	'uit' => $uit,
	'spelsoort' => $spelsoort,
	'competitiesoort' => $competitiesoort,
	'dagsoort' => $dagsoort,
	'leeftijdscategorie' => $leeftijdscategorie,
	'sorteervolgorde' => $sorteervolgorde,
	'gebruiklokaleteamgegevens' => $gebruiklokaleteamgegevens,
];

// Add teamcode if provided
if (!empty($teamcode)) {
	$args['teamcode'] = $teamcode;
}

// Get API instance
$api = Sportlink_API::create_from_settings();

if (!$api) {
	echo '<div class="sportlink-error">';
	echo '<p>' . esc_html__('Sportlink API is niet geconfigureerd. Ga naar Instellingen → Sportlink om de API key in te stellen.', 'sportlink') . '</p>';
	echo '</div>';
	return;
}

// Fetch programma data
$programma = $api->get_programma($args);

if (is_wp_error($programma)) {
	echo '<div class="sportlink-error">';
	echo '<p>' . esc_html__('Kon het programma niet ophalen: ', 'sportlink') . esc_html($programma->get_error_message()) . '</p>';
	echo '</div>';
	return;
}

if (empty($programma)) {
	echo '<div class="sportlink-no-results">';
	echo '<p>' . esc_html__('Geen wedstrijden gevonden voor de geselecteerde criteria.', 'sportlink') . '</p>';
	echo '</div>';
	return;
}

// Render the programma using existing template
// You can customize this to use a different template or inline rendering

// Map font-size to WordPress text size classes
$font_size_class_map = [
	'small' => 'has-small-font-size',
	'medium' => '',  // Default, geen extra class
	'large' => 'has-large-font-size',
	'x-large' => 'has-x-large-font-size'
];
$font_size_class = isset($font_size_class_map[$font_size]) ? $font_size_class_map[$font_size] : '';
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'sportlink-programma-block ' . $font_size_class]); ?>>
	<?php
	// Use existing template logic or create custom rendering
	echo '<div class="sportlink-programma-list sportlink-display-' . esc_attr($display_style) . '">';

	// Group by date if enabled and no specific team selected
	if ($group_by_date && empty($teamcode)) {
		// Group wedstrijden by date
		$grouped = [];
		foreach ($programma as $wedstrijd) {
			if (is_object($wedstrijd)) {
				$wedstrijd = (array) $wedstrijd;
			}
			$datum = $wedstrijd['datum'] ?? '';
			if (!isset($grouped[$datum])) {
				$grouped[$datum] = [];
			}
			$grouped[$datum][] = $wedstrijd;
		}

		// Render header once at the top
		echo '<table class="sportlink-table">';
		echo '<thead>';
		echo '<tr>';

		// Render headers in custom order
		foreach ($column_order as $column) {
			$label = $column_labels[$column] ?? '';
			// Skip columns that are disabled
			if ($column === 'time' && !$show_time) continue;
			if ($column === 'location' && !$show_location) continue;
			if ($column === 'field' && !$show_field) continue;
			if ($column === 'referee' && !$show_referee) continue;
			if ($column === 'competition' && !$show_competition) continue;
			if ($column === 'remarks' && !$show_remarks) continue;

			echo '<th>' . esc_html($label) . '</th>';
		}

		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		// Render grouped by date
		foreach ($grouped as $datum => $wedstrijden) {
			// Date separator row - calculate colspan based on visible columns
			$colspan = 0;
			foreach ($column_order as $column) {
				if ($column === 'time' && !$show_time) continue;
				if ($column === 'location' && !$show_location) continue;
				if ($column === 'field' && !$show_field) continue;
				if ($column === 'referee' && !$show_referee) continue;
				if ($column === 'competition' && !$show_competition) continue;
				if ($column === 'remarks' && !$show_remarks) continue;
				$colspan++;
			}

			echo '<tr class="sportlink-date-row">';
			echo '<td colspan="' . esc_attr($colspan) . '" style="font-weight: bold; background-color: #f5f5f5; padding: 10px;">' . esc_html($datum) . '</td>';
			echo '</tr>';

			foreach ($wedstrijden as $wedstrijd) {
				$is_thuiswedstrijd = isset($wedstrijd['thuisteam']) && strtolower($wedstrijd['thuisteam']) !== 'onbekend';
				$row_class = $is_thuiswedstrijd ? 'thuiswedstrijd' : 'uitwedstrijd';

				echo '<tr class="' . esc_attr($row_class) . '">';

				// Render columns in custom order
				foreach ($column_order as $column) {
					$render_column($column, $wedstrijd);
				}

				echo '</tr>';
			}
		}

		echo '</tbody>';
		echo '</table>';
	} else {
		// Regular rendering without grouping
		echo '<table class="sportlink-table">';
		echo '<thead>';
		echo '<tr>';

		// Add date column first if enabled
		if ($show_date) {
			echo '<th>' . esc_html__('Datum', 'sportlink') . '</th>';
		}

		// Render columns in custom order
		foreach ($column_order as $column) {
			// Check visibility based on column type
			$should_show = true;
			switch ($column) {
				case 'time':
					$should_show = $show_time;
					break;
				case 'location':
					$should_show = $show_location;
					break;
				case 'field':
					$should_show = $show_field;
					break;
				case 'referee':
					$should_show = $show_referee;
					break;
				case 'competition':
					$should_show = $show_competition;
					break;
				case 'remarks':
					$should_show = $show_remarks;
					break;
					// home, versus, away are always shown
			}

			if ($should_show && isset($column_labels[$column])) {
				echo '<th>' . esc_html($column_labels[$column]) . '</th>';
			}
		}

		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ($programma as $wedstrijd) {
			// Convert stdClass to array if needed
			if (is_object($wedstrijd)) {
				$wedstrijd = (array) $wedstrijd;
			}

			$is_thuiswedstrijd = isset($wedstrijd['thuisteam']) && strtolower($wedstrijd['thuisteam']) !== 'onbekend';
			$row_class = $is_thuiswedstrijd ? 'thuiswedstrijd' : 'uitwedstrijd';

			echo '<tr class="' . esc_attr($row_class) . '">';

			// Add date column first if enabled
			if ($show_date) {
				echo '<td class="datum">' . esc_html($wedstrijd['datum'] ?? '') . '</td>';
			}

			// Render columns in custom order
			foreach ($column_order as $column) {
				$render_column($column, $wedstrijd);
			}

			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';
	}

	echo '</div>';
	?>
</div>
