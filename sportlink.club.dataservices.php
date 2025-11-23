<?php

/**
 * Plugin Name: Sportlink KNVB Club.Dataservices
 * Description: Toon het volledige wedstrijdprogramma, uitslagen, standen, teams en wedstrijd-details vanuit Sportlink Club.Dataservice
 * Version: 1.2.0
 * Author: Richard van der Meer
 * Author URI: http://richardvandermeer.nl/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * */

// PHP error reporting - only enable in debug mode
if (defined('WP_DEBUG') && WP_DEBUG) {
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
}

define("SPORTLINK_PLUGIN_DIR", plugin_dir_path(__FILE__));

if (!class_exists('Gamajo_Template_Loader')) {
	require plugin_dir_path(__FILE__) . 'includes/class-gamajo-template-loader.php';
}

// Load API interface class
require_once plugin_dir_path(__FILE__) . 'includes/class-sportlink-api.php';

// Load type definitions for API responses (for IDE autocomplete)
require_once plugin_dir_path(__FILE__) . 'includes/class-sportlink-types.php';

/***********************************************************************
 Enqueue frontend styles
 */
function sportlink_enqueue_styles()
{
	// Only load on pages that have the shortcode
	global $post;
	if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'sportlink')) {
		wp_add_inline_style('wp-block-library', '
			.sportlink-error {
				background: #f8d7da;
				border-left: 4px solid #dc3545;
				padding: 12px 16px;
				margin: 16px 0;
				border-radius: 4px;
				color: #721c24;
			}
			.sportlink-notice--warning {
				background: #fff3cd;
				border-left: 4px solid #ffc107;
				padding: 12px 16px;
				margin: 16px 0;
				border-radius: 4px;
				color: #856404;
			}
		');
	}
}
add_action('wp_enqueue_scripts', 'sportlink_enqueue_styles');


if (!class_exists('Gamajo_Template_Loader')) {
	require plugin_dir_path(__FILE__) . 'includes/class-gamajo-template-loader.php';
}

/***********************************************************************
 Admin init functie
 */
if (is_admin()) {
	add_action('admin_menu', 'sportlink_club_dataservices_menu');
	add_action('admin_init', 'sportlink_club_dataservices_register_settings');
	add_action('wp_dashboard_setup', 'sportlink_add_dashboard_widget');
	add_action('wp_ajax_sportlink_get_match_details', 'sportlink_ajax_get_match_details');
}

/***********************************************************************
 Dashboard widget voor API status
 */
function sportlink_add_dashboard_widget()
{
	wp_add_dashboard_widget(
		'sportlink_api_status',
		'Sportlink API Status',
		'sportlink_dashboard_widget_content'
	);
}

/***********************************************************************
 AJAX handler for match details
 */
function sportlink_ajax_get_match_details()
{
	// Verify nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sportlink_match_details')) {
		wp_send_json_error(array('message' => 'Invalid nonce'));
		return;
	}

	// Check user permissions
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Insufficient permissions'));
		return;
	}

	// Get and sanitize wedstrijdcode
	if (!isset($_POST['wedstrijdcode'])) {
		wp_send_json_error(array('message' => 'Missing wedstrijdcode'));
		return;
	}

	$wedstrijdcode = sanitize_text_field(wp_unslash($_POST['wedstrijdcode']));

	try {
		$sportlinkClient = new SportlinkClient(
			get_option('sportlink_club_dataservices_key'),
			get_option('sportlink_club_dataservices_cachetime')
		);

		// Get match details
		$match = $sportlinkClient->doRequest("wedstrijd-informatie", true, array('wedstrijdcode=' . $wedstrijdcode));
		$history = $sportlinkClient->doRequest("wedstrijd-historische-resultaten", true, array('wedstrijdcode=' . $wedstrijdcode));

		if (!$match || !isset($match->wedstrijdinformatie)) {
			wp_send_json_error(array('message' => 'Match details not found'));
			return;
		}

		$info = $match->wedstrijdinformatie;
		$doelpunten = isset($match->doelpunten) ? $match->doelpunten : array();
		$kaarten = isset($match->kaarten) ? $match->kaarten : array();
		$wissels = isset($match->wissels) ? $match->wissels : array();

		// Sort history by date (newest first)
		if (!empty($history) && is_array($history)) {
			usort($history, function ($a, $b) {
				$dateA = isset($a->wedstrijddatum) ? strtotime($a->wedstrijddatum) : 0;
				$dateB = isset($b->wedstrijddatum) ? strtotime($b->wedstrijddatum) : 0;

				// If dates are invalid, put them at the bottom
				if ($dateA === false) $dateA = 0;
				if ($dateB === false) $dateB = 0;

				// Sort descending (newest first)
				return $dateB - $dateA;
			});
		}

		// Get poule standing
		$poule = array();
		if (!empty($info->poulecode)) {
			$poule = $sportlinkClient->doRequest("poulestand", true, array('poulecode=' . $info->poulecode));
		}

		// Build HTML
		ob_start();
		include plugin_dir_path(__FILE__) . 'templates/match-admin.php';
		$html = ob_get_clean();

		// Send response
		wp_send_json_success(array(
			'title' => esc_html($info->thuisteam) . ' - ' . esc_html($info->uitteam),
			'html' => $html
		));
	} catch (Exception $e) {
		wp_send_json_error(array('message' => 'Error loading match details: ' . $e->getMessage()));
	}
}

add_action('wp_ajax_sportlink_get_match_details', 'sportlink_ajax_get_match_details');

/***********************************************************************
 AJAX handler for poule details
 */
function sportlink_ajax_get_poule_details()
{
	// Verify nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sportlink_poule_details')) {
		wp_send_json_error(array('message' => 'Invalid nonce'));
		return;
	}

	// Check user permissions
	if (!current_user_can('manage_options')) {
		wp_send_json_error(array('message' => 'Insufficient permissions'));
		return;
	}

	// Get and sanitize poulecode
	if (!isset($_POST['poulecode'])) {
		wp_send_json_error(array('message' => 'Missing poulecode'));
		return;
	}

	$poulecode = sanitize_text_field(wp_unslash($_POST['poulecode']));

	try {
		$sportlinkClient = new SportlinkClient(
			get_option('sportlink_club_dataservices_key'),
			get_option('sportlink_club_dataservices_cachetime')
		);

		// Get poule standing
		$stand = $sportlinkClient->doRequest("poulestand", true, array('poulecode=' . $poulecode));

		if (!$stand || !is_array($stand) || count($stand) === 0) {
			wp_send_json_error(array('message' => 'Poule details niet gevonden'));
			return;
		}

		// Get poule name from first team
		$pouleName = isset($stand[0]->klassepoule) ? $stand[0]->klassepoule : 'Poulestand';

		// Build HTML
		ob_start();
		include plugin_dir_path(__FILE__) . 'templates/poule-admin.php';
		$html = ob_get_clean();

		wp_send_json_success(array(
			'title' => $pouleName,
			'html' => $html
		));
	} catch (Exception $e) {
		wp_send_json_error(array('message' => $e->getMessage()));
	}
}

add_action('wp_ajax_sportlink_get_poule_details', 'sportlink_ajax_get_poule_details');

function sportlink_dashboard_widget_content()
{
	$circuit_breaker = get_transient('sportlink_circuit_breaker');
	$cache_time = get_option('sportlink_club_dataservices_cachetime', 30);

	echo '<div style="padding: 10px;">';

	if ($circuit_breaker && $circuit_breaker > 3) {
		echo '<p style="color: #dc3545; font-weight: bold;">⚠️ API Status: Niet beschikbaar</p>';
		echo '<p>De Sportlink API reageert niet. Er wordt verouderde data getoond.</p>';
		echo '<p><small>Mislukte pogingen: ' . esc_html($circuit_breaker) . '</small></p>';
	} elseif ($circuit_breaker && $circuit_breaker > 0) {
		echo '<p style="color: #ffc107; font-weight: bold;">⚡ API Status: Instabiel</p>';
		echo '<p>Er zijn recent enkele API fouten opgetreden.</p>';
		echo '<p><small>Aantal fouten: ' . esc_html($circuit_breaker) . '</small></p>';
	} else {
		echo '<p style="color: #28a745; font-weight: bold;">✓ API Status: Operationeel</p>';
		echo '<p>De verbinding met de Sportlink API werkt normaal.</p>';
	}

	echo '<hr style="margin: 15px 0;">';
	echo '<p><strong>Cache instellingen:</strong></p>';
	echo '<p>Cache tijd: ' . esc_html($cache_time) . ' minuten</p>';

	$settings_url = admin_url('options-general.php?page=sportlink.club.dataservices');
	echo '<p><a href="' . esc_url($settings_url) . '" class="button button-secondary">Instellingen</a></p>';

	echo '</div>';
}

/***********************************************************************
 Define wordpress options menu
 */
function sportlink_club_dataservices_menu()
{
	add_options_page(
		'Sportlink - KNVB opties',   // Title in browser tab
		'Sportlink - KNVB',          // Title in settings menu
		'manage_options',    // Capability needed to see this menu
		'sportlink.club.dataservices',     // Slug
		'sportlink_club_dataservices_options'
	); // Function to call when rendering this menu
}


/***********************************************************************
 Register shortcodes
 */
function shortcode_sportlink_club_dataservices($atts)
{
	if (is_string($atts)) {
		$atts = array();
	}
	$atts = shortcode_atts(array(
		'type' => 'programma',
		'template' => '',
		'team' => '',
		'poule' => '',
		'aantalwekenvooruit' => in_array('aantalwekenvooruit', $atts) ? $atts['aantalwekenvooruit'] : 0,
		'aantaldagen' => in_array('aantaldagen', $atts) ? $atts['aantaldagen'] : '',
	), $atts, 'sportlink');

	// Sanitize shortcode attributes
	$atts['type'] = sanitize_key($atts['type']);
	$atts['template'] = sanitize_file_name($atts['template']);
	$atts['team'] = sanitize_text_field($atts['team']);
	$atts['poule'] = sanitize_text_field($atts['poule']);
	$atts['aantalwekenvooruit'] = intval($atts['aantalwekenvooruit']);
	$atts['aantaldagen'] = $atts['aantaldagen'] !== '' ? intval($atts['aantaldagen']) : '';

	// Validate type parameter
	$allowed_types = array('programma', 'stand', 'uitslagen', 'programma-uitslagen', 'wedstrijd');
	if (!in_array($atts['type'], $allowed_types)) {
		$atts['type'] = 'programma';
	}

	ob_start();
	try {
		$sportlinkClient = new SportlinkClient(get_option('sportlink_club_dataservices_key'), get_option('sportlink_club_dataservices_cachetime'));


		switch ($atts['type']) {
			case 'programma':
				$sportlinkClient->showFixtures($atts);
				break;
			case 'stand':
				$sportlinkClient->showStandings($atts);
				break;
			case 'uitslagen':
				$sportlinkClient->showResults($atts);
				break;
			case 'programma-uitslagen':
				$sportlinkClient->showFixturesResults($atts);
				break;
			case 'wedstrijd':
				$sportlinkClient->showMatchDetail($atts);
				break;
		}
	} catch (Exception $e) {
		// Check if we're showing stale data
		$error_message = $e->getMessage();
		$is_stale = strpos($error_message, 'circuit breaker') !== false ||
			strpos($error_message, 'could not be reached') !== false ||
			strpos($error_message, 'status code') !== false;

		if ($is_stale) {
			echo '<div class="sportlink-notice sportlink-notice--warning">';
			echo '<p><strong>Let op:</strong> De getoonde gegevens kunnen verouderd zijn. De Sportlink API is tijdelijk niet beschikbaar.</p>';
			echo '</div>';
		} else {
			echo '<div class="sportlink-error">';
			echo '<p>De Sportlink gegevens kunnen momenteel niet worden geladen.</p>';
			if (defined('WP_DEBUG') && WP_DEBUG) {
				echo '<p><small>' . esc_html($error_message) . '</small></p>';
			}
			echo '</div>';
		}
	}

	return ob_get_clean();
}
add_shortcode('sportlink', 'shortcode_sportlink_club_dataservices');

/***********************************************************************
 Rendering options page
 */
function sportlink_club_dataservices_options()
{
	// Handle cache clearing
	if (isset($_POST['sportlink_clear_cache']) && check_admin_referer('sportlink_clear_cache_action', 'sportlink_clear_cache_nonce')) {
		sportlink_clear_cache();
		// Also reset circuit breaker
		delete_transient('sportlink_circuit_breaker');
		echo '<div class="notice notice-success is-dismissible"><p>Cache succesvol gewist en circuit breaker gereset!</p></div>';
	}

	// Check circuit breaker status
	$circuit_breaker = get_transient('sportlink_circuit_breaker');
	if ($circuit_breaker && $circuit_breaker > 3) {
		echo '<div class="notice notice-warning"><p><strong>Let op:</strong> De Sportlink API is tijdelijk niet beschikbaar. Er wordt verouderde data getoond. Circuit breaker status: ' . esc_html($circuit_breaker) . ' mislukte pogingen.</p></div>';
	}

	$sportlinkClient = null;
	$apiKey = get_option('sportlink_club_dataservices_key');

	// Only create client if API key is set
	if (!empty($apiKey)) {
		try {
			$sportlinkClient = new SportlinkClient($apiKey, get_option('sportlink_club_dataservices_cachetime'));
		} catch (Exception $e) {
			echo '<div class="notice notice-error"><p>Er kan momenteel geen verbinding worden gemaakt met de Sportlink API</p></div>';
			$sportlinkClient = null;
		}
	}

?>
	<div class="wrap">

		<h2>Sportlink - KNVB</h2>

		<?php
		$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';
		// Whitelist allowed tabs
		$allowed_tabs = array('settings', 'teams', 'fixtures', 'results', 'team-shortcodes', 'match-shortcodes', 'parameter-shortcodes');
		if (!in_array($active_tab, $allowed_tabs)) {
			$active_tab = 'settings';
		}
		?> <h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url(admin_url('options-general.php?page=sportlink.club.dataservices&tab=settings')); ?>" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Instellingen</a>
			<?php if ($sportlinkClient) : ?>
				<a href="<?php echo esc_url(admin_url('options-general.php?page=sportlink.club.dataservices&tab=teams')); ?>" class="nav-tab <?php echo $active_tab == 'teams' ? 'nav-tab-active' : ''; ?>">Teams</a>
				<a href="<?php echo esc_url(admin_url('options-general.php?page=sportlink.club.dataservices&tab=fixtures')); ?>" class="nav-tab <?php echo $active_tab == 'fixtures' ? 'nav-tab-active' : ''; ?>">Programma</a>
				<a href="<?php echo esc_url(admin_url('options-general.php?page=sportlink.club.dataservices&tab=results')); ?>" class="nav-tab <?php echo $active_tab == 'results' ? 'nav-tab-active' : ''; ?>">Uitslagen</a>
				<a href="<?php echo esc_url(admin_url('options-general.php?page=sportlink.club.dataservices&tab=team-shortcodes')); ?>" class="nav-tab <?php echo $active_tab == 'team-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcodes per team</a>
				<a href="<?php echo esc_url(admin_url('options-general.php?page=sportlink.club.dataservices&tab=match-shortcodes')); ?>" class="nav-tab <?php echo $active_tab == 'match-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcodes per wedstrijd</a>
				<a href="<?php echo esc_url(admin_url('options-general.php?page=sportlink.club.dataservices&tab=parameter-shortcodes')); ?>" class="nav-tab <?php echo $active_tab == 'parameter-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcode parameters</a>
			<?php endif; ?>
		</h2>
	</div>

	<form method="post" action="options.php">
		<?php
		if ($active_tab == 'settings') {
			settings_fields('sportlink.club.dataservices-settings-group');
			do_settings_sections('sportlink.club.dataservices-settings-group');

		?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row">API sleutel</th>
					<td>
						<input type="text" name="sportlink_club_dataservices_key" value="<?php echo esc_attr(get_option('sportlink_club_dataservices_key')); ?>" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">Cache-tijd (in minuten)</th>
					<td>
						<input type="text" name="sportlink_club_dataservices_cachetime" value="<?php echo esc_attr(get_option('sportlink_club_dataservices_cachetime')); ?>" />
					</td>
				</tr>


				<tr valign="top">
					<th scope="row">SSL-beveiliging overschrijven</th>
					<td>
						<input type="checkbox" name="sportlink_club_dataservices_overwrite_ssl" <?php echo get_option('sportlink_club_dataservices_overwrite_ssl') !== '' ? 'checked' : ''; ?> value="true" />
					</td>
				</tr>

				<?php if ($sportlinkClient && $sportlinkClient->isConnected() && $sportlinkClient->getClubInfo()) :
					$clubInfo = $sportlinkClient->getClubInfo();
					$gegevens = $clubInfo->gegevens;
					$bezoekadres = $clubInfo->bezoekadres;
				?>
					<tr valign="top">
						<th scope="row">Club</th>
						<td>
							<div style="display: flex; gap: 20px;">
								<div style="flex: 1;">
									<p style="margin: 0 0 10px 0;">
										<strong style="font-size: 15px;"><?php echo esc_html($gegevens->clubnaam); ?></strong> (<?php echo esc_html($gegevens->clubcode); ?>)
									</p>

									<?php if (!empty($bezoekadres->straatnaam) || !empty($bezoekadres->plaats)) : ?>
										<p style="margin: 0 0 10px 0;">
											<strong>Adres:</strong><br>
											<?php
											if (!empty($bezoekadres->straatnaam)) {
												echo esc_html($bezoekadres->straatnaam);
												if (!empty($bezoekadres->huisnummer)) {
													echo ' ' . esc_html($bezoekadres->huisnummer);
												}
												if (!empty($bezoekadres->nummertoevoeging)) {
													echo esc_html($bezoekadres->nummertoevoeging);
												}
												echo '<br>';
											}
											if (!empty($bezoekadres->postcode) || !empty($bezoekadres->plaats)) {
												if (!empty($bezoekadres->postcode)) {
													echo esc_html($bezoekadres->postcode) . ' ';
												}
												if (!empty($bezoekadres->plaats)) {
													echo esc_html($bezoekadres->plaats);
												}
											}
											?>
										</p>
									<?php endif; ?>

									<?php if (!empty($gegevens->telefoonnummer) || !empty($gegevens->email) || !empty($gegevens->website)) : ?>
										<p style="margin: 0 0 10px 0;">
											<strong>Contact:</strong><br>
											<?php if (!empty($gegevens->telefoonnummer)) : ?>
												Tel: <?php echo esc_html($gegevens->telefoonnummer); ?><br>
											<?php endif; ?>
											<?php if (!empty($gegevens->email)) : ?>
												Email: <a href="mailto:<?php echo esc_attr($gegevens->email); ?>"><?php echo esc_html($gegevens->email); ?></a><br>
											<?php endif; ?>
											<?php if (!empty($gegevens->website)) : ?>
												Website: <a href="<?php echo esc_url($gegevens->website); ?>" target="_blank"><?php echo esc_html($gegevens->website); ?></a>
											<?php endif; ?>
										</p>
									<?php endif; ?>

									<?php if (!empty($gegevens->thuisshirtkleur) || !empty($gegevens->uitshirtkleur)) : ?>
										<p style="margin: 0 0 10px 0;">
											<strong>Clubkleuren:</strong><br>
											<?php if (!empty($gegevens->thuisshirtkleur)) : ?>
												Thuis: Shirt <?php echo esc_html($gegevens->thuisshirtkleur); ?>
												<?php if (!empty($gegevens->thuisbroekkleur)) : ?>
													, Broek <?php echo esc_html($gegevens->thuisbroekkleur); ?>
												<?php endif; ?>
												<?php if (!empty($gegevens->thuissokkenkleur)) : ?>
													, Sokken <?php echo esc_html($gegevens->thuissokkenkleur); ?>
												<?php endif; ?>
												<br>
											<?php endif; ?>
											<?php if (!empty($gegevens->uitshirtkleur)) : ?>
												Uit: Shirt <?php echo esc_html($gegevens->uitshirtkleur); ?>
												<?php if (!empty($gegevens->uitbroekkleur)) : ?>
													, Broek <?php echo esc_html($gegevens->uitbroekkleur); ?>
												<?php endif; ?>
												<?php if (!empty($gegevens->uitsokkenkleur)) : ?>
													, Sokken <?php echo esc_html($gegevens->uitsokkenkleur); ?>
												<?php endif; ?>
											<?php endif; ?>
										</p>
									<?php endif; ?>

									<?php if (!empty($gegevens->oprichtingsdatum)) :
										// Format date using WordPress functions
										$timestamp = strtotime($gegevens->oprichtingsdatum);
										$formatted_date = date_i18n('j F Y', $timestamp);
									?>
										<p style="margin: 0 0 10px 0;">
											<strong>Opgericht:</strong> <?php echo esc_html($formatted_date); ?>
										</p>
									<?php endif; ?>
								</div>
								<div style="flex-shrink: 0;">
									<img alt="<?php echo esc_attr($gegevens->clubnaam); ?>" src="data:image/jpg;base64,<?php echo esc_attr($gegevens->kleinlogo); ?>" style="max-width: 150px; height: auto;" />
								</div>
							</div>
						</td>
					</tr>
				<?php endif; ?>
			</table>

			<?php
			submit_button();
			?>
	</form>

	<!-- Cache Clear Button -->
	<h3>Cache beheer</h3>
	<form method="post" action="">
		<?php wp_nonce_field('sportlink_clear_cache_action', 'sportlink_clear_cache_nonce'); ?>
		<p>Wis de cache om de nieuwste gegevens van de Sportlink API op te halen.</p>
		<?php submit_button('Cache wissen', 'secondary', 'sportlink_clear_cache', false); ?>
	</form>

<?php
		} elseif ($active_tab == 'teams') {
?>
	<form method="post" action="options.php">
		<?php
			$sportlinkClient->showTeams();
		?>
	</form>
<?php
		} elseif ($active_tab == 'fixtures') {
?>
	<form method="post" action="options.php">
		<?php
			try {
				$sportlinkClient->showAdminFixtures();
			} catch (Exception $e) {
				echo '<div class="notice notice-error"><p><strong>Fout bij het laden van programma:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
				echo '<p>De Sportlink API is tijdelijk niet bereikbaar. Probeer het later opnieuw.</p>';
			}
		} elseif ($active_tab == 'results') {
		?>
		<form method="post" action="options.php">
		<?php
			try {
				$sportlinkClient->showAdminResults();
			} catch (Exception $e) {
				echo '<div class="notice notice-error"><p><strong>Fout bij het laden van uitslagen:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
				echo '<p>De Sportlink API is tijdelijk niet bereikbaar. Probeer het later opnieuw.</p>';
			}
		}
		?>
		</form>
	<?php

}
function sportlink_club_dataservices_register_settings()
{
	register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_key', array(
		'type' => 'string',
		'sanitize_callback' => 'sportlink_sanitize_api_key',
		'default' => ''
	));

	register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_cachetime', array(
		'type' => 'integer',
		'sanitize_callback' => 'absint',
		'default' => 30
	));

	register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_overwrite_ssl', array(
		'type' => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default' => ''
	));
}

// Sanitize API key and clear cache when changed
function sportlink_sanitize_api_key($value)
{
	$old_value = get_option('sportlink_club_dataservices_key');
	$new_value = sanitize_text_field($value);

	// If API key changed, clear all sportlink transients
	if ($old_value !== $new_value) {
		sportlink_clear_cache();
	}

	return $new_value;
}

// Clear all sportlink transients
function sportlink_clear_cache()
{
	global $wpdb;

	// Delete all transients that start with 'sportlink_'
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like('_transient_sportlink_') . '%',
			$wpdb->esc_like('_transient_timeout_sportlink_') . '%'
		)
	);

	// Also clear stale cache options
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like('sportlink_stale_') . '%'
		)
	);
}

/**
 * Sportlink API Client
 *
 * Main client class for interacting with the Sportlink KNVB API.
 * Handles caching, error handling, and circuit breaker pattern.
 *
 * @package Sportlink_KNVB
 * @version 1.2.0
 *
 * @see /includes/class-sportlink-types.php for detailed return type definitions
 */
class SportlinkClient
{
	const API_URL = 'https://data.sportlink.com/';

	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * @var Sportlink_Type_ClubInfo|false Club information object
	 */
	private $clubInfo;

	/**
	 * @var bool
	 */
	private $isConnected = false;

	/**
	 * @var stdClass|null Teams data
	 */
	private $teams;

	/**
	 * @var bool Track if teams have been loaded
	 */
	private $teamsLoaded = false;

	/**
	 * @var int Cache time in minutes
	 */
	private $cacheTime;

	/**
	 * @var Sportlink_Template_Loader
	 */
	private $template;

	/**
	 * Create an ageCategories variable that maps the ageCategories to the ageCategory codes
	 */
	private $ageCategories = array(
		'senioren' => 999,
		'senioren vrouwen' => 995,
		'JO23' => 239,
		'MO23' => 235,
		'JO22' => 229,
		'MO22' => 225,
		'JO21' => 219,
		'MO21' => 215,
		'JO20' => 209,
		'MO20' => 205,
		'JO19' => 199,
		'MO19' => 195,
		'JO18' => 189,
		'MO18' => 185,
		'JO17' => 179,
		'MO17' => 175,
		'JO16' => 169,
		'MO16' => 165,
		'JO15' => 159,
		'MO15' => 155,
		'JO14' => 149,
		'MO14' => 145,
		'JO13' => 139,
		'MO13' => 135,
		'JO12' => 129,
		'MO12' => 125,
		'JO11' => 119,
		'MO11' => 115,
		'JO10' => 109,
		'MO10' => 105,
		'JO9' => 99,
		'MO9' => 95,
		'JO8' => 89,
		'MO8' => 85,
		'JO7' => 79,
		'MO7' => 75
	);


	/**
	 * @param string $apiKey
	 * @param int $cacheTime
	 */
	public function __construct($apiKey, $cacheTime)
	{
		$this->apiKey = $apiKey;
		$this->cacheTime = $cacheTime ? $cacheTime : 30;

		if (!!$this->apiKey) {
			$this->connect();
			if ($this->isConnected()) {
				$this->clubInfo = $this->requestClubInfo();
			}
		}

		$this->template =  new Sportlink_Template_Loader;
	}

	/**
	 * Connect to the API
	 *
	 * @return void
	 * @throws Exception When API cannot be reached
	 */
	public function connect()
	{
		$apiInfoURL = SportlinkClient::API_URL . "clubgegevens" . "?client_id=" . $this->apiKey;
		if (!$this->url_exists($apiInfoURL)) {
			throw new Exception("Sportlink API not found");
		} else {
			$this->isConnected = true;
		}
	}

	/**
	 * Check if client is connected to API
	 *
	 * @return bool
	 */
	public function isConnected()
	{
		return $this->isConnected;
	}

	/**
	 * Request club information from the API
	 *
	 * @return Sportlink_Type_ClubInfo|false Club information object or false on failure
	 */
	private function requestClubInfo()
	{
		$clubgegevens = $this->doRequest("clubgegevens");
		if ($clubgegevens) {
			return $clubgegevens;
		}
		return false;
	}

	/**
	 * Get the club information
	 *
	 * @return Sportlink_Type_ClubInfo|false Club information object or false if not available
	 */
	public function getClubInfo()
	{
		return $this->clubInfo;
	}

	/**
	 * Make a request to the Sportlink API or get it from cache
	 *
	 * Returns different data structures based on the endpoint called:
	 * - clubgegevens: stdClass with club information (see Sportlink_Type_ClubInfo)
	 * - teams: array of stdClass team objects (see Sportlink_Type_Team)
	 * - programma/uitslagen: array of stdClass match objects (see Sportlink_Type_Match)
	 * - wedstrijd-informatie: stdClass with detailed match info (see Sportlink_Type_MatchDetail)
	 * - poulestand: array of stdClass standing objects (see Sportlink_Type_Standing)
	 * - team-indeling: array of stdClass team member objects (see Sportlink_Type_TeamMember)
	 * - wedstrijd-deelnemers: array of stdClass participant objects (see Sportlink_Type_MatchParticipant)
	 * - bestuur: array of stdClass board member objects (see Sportlink_Type_Board)
	 * - commissies: array of stdClass committee objects (see Sportlink_Type_Committee)
	 * - verenigingsactiviteiten: array of stdClass activity objects (see Sportlink_Type_ClubActivity)
	 *
	 * @param string $endpoint The API endpoint to call (e.g., 'teams', 'programma', 'clubgegevens')
	 * @param bool $cached Whether to use caching (default: true)
	 * @param array|null $parameters Array of URL parameters (e.g., ['teamcode=123', 'aantaldagen=30'])
	 * @return stdClass|array|false API response data structure (varies by endpoint) or false on failure
	 * @throws Exception When not connected to API or request fails critically
	 */
	public function doRequest($endpoint, $cached = true, $parameters = array())
	{
		if (!$this->isConnected) {
			throw new Exception("Not connected to Sportlink API");
		}

		// Build the JSON request string from the given array of parameters
		$jsonurl = SportlinkClient::API_URL . $endpoint . "?client_id=" . $this->apiKey;

		$cacheParameters = "";
		if (!is_null($parameters)) {
			foreach ($parameters as $param) {
				$jsonurl .= "&" . $param;
				$cacheParameters .= "-" . $param;
			}
		}

		// Create unique transient key (max 172 chars for transient names)
		$transient_key = 'sportlink_' . md5($endpoint . $cacheParameters);
		$stale_cache_key = 'sportlink_stale_' . md5($endpoint . $cacheParameters);
		$circuit_breaker_key = 'sportlink_circuit_breaker';

		// Try to get cached data from transient
		if ($cached) {
			$cached_data = get_transient($transient_key);
			if ($cached_data !== false) {
				return $cached_data;
			}
		}

		// Check if API is in circuit breaker mode (too many recent failures)
		$circuit_breaker = get_transient($circuit_breaker_key);

		if ($circuit_breaker && $circuit_breaker > 3) {
			// Circuit is open - use stale cache if available
			$stale_data = get_option($stale_cache_key);
			if ($stale_data !== false) {
				return $stale_data;
			}
			throw new Exception("Sportlink API is temporarily unavailable (circuit breaker open)");
		}

		// No cache or cache disabled - fetch from API using wp_remote_get
		$response = wp_remote_get($jsonurl, array(
			'timeout' => 8, // Reduced timeout for faster failure detection
			'sslverify' => get_option('sportlink_club_dataservices_overwrite_ssl') !== 'true'
		));

		// Handle errors - try stale cache first
		if (is_wp_error($response)) {
			$this->incrementCircuitBreaker();

			// Try to use stale cache as fallback
			$stale_data = get_option($stale_cache_key);
			if ($stale_data !== false) {
				// Set a short-lived transient to avoid hammering the API
				set_transient($transient_key, $stale_data, 300); // 5 minutes
				return $stale_data;
			}

			throw new Exception("Sportlink API endpoint could not be reached: " . $response->get_error_message());
		}

		$status_code = wp_remote_retrieve_response_code($response);
		if ($status_code < 200 || $status_code >= 400) {
			$this->incrementCircuitBreaker();

			// Try stale cache on error
			$stale_data = get_option($stale_cache_key);
			if ($stale_data !== false) {
				set_transient($transient_key, $stale_data, 300);
				return $stale_data;
			}

			throw new Exception("Sportlink API returned status code: " . $status_code);
		}

		$json = wp_remote_retrieve_body($response);

		if (empty($json)) {
			$this->incrementCircuitBreaker();

			$stale_data = get_option($stale_cache_key);
			if ($stale_data !== false) {
				set_transient($transient_key, $stale_data, 300);
				return $stale_data;
			}

			throw new Exception("Sportlink API returned empty response");
		}

		$data = json_decode($json);

		if (json_last_error() !== JSON_ERROR_NONE) {
			$this->incrementCircuitBreaker();

			$stale_data = get_option($stale_cache_key);
			if ($stale_data !== false) {
				set_transient($transient_key, $stale_data, 300);
				return $stale_data;
			}

			throw new Exception("Failed to parse JSON response: " . json_last_error_msg());
		}

		// Success! Reset circuit breaker
		delete_transient($circuit_breaker_key);

		// Store in transient cache (cache time in minutes, converted to seconds)
		if ($cached && $data) {
			set_transient($transient_key, $data, $this->cacheTime * 60);
			// Also store as long-term stale cache (7 days) for emergency fallback
			update_option($stale_cache_key, $data, false);
		}

		return $data;
	}

	/**
	 * Increment circuit breaker counter on API failures
	 *
	 * @return void
	 */
	private function incrementCircuitBreaker()
	{
		$circuit_breaker_key = 'sportlink_circuit_breaker';
		$current_count = get_transient($circuit_breaker_key);
		$new_count = $current_count ? $current_count + 1 : 1;

		// Store for 5 minutes - if API keeps failing, circuit stays open
		set_transient($circuit_breaker_key, $new_count, 300);
	}

	/**
	 * Get teams with lazy loading and request-level caching
	 *
	 * @return stdClass|array Teams data
	 */
	private function getTeams()
	{
		if (!$this->teamsLoaded) {
			$this->teams = $this->doRequest("teams", true, null);
			$this->addAgeCategoryToTeams($this->teams);
			$this->teamsLoaded = true;
		}
		return $this->teams;
	}

	/**
	 * Show all teams in regular competition
	 *
	 * @return void
	 */
	public function showTeams()
	{
		$this->teams = $this->getTeams();
		$this->teams = $this->orderTeamsByCategory($this->teams);

		// Load the correct template
		$this->template
			->set_template_data(array('teams' => $this->teams, 'clubInfo' => $this->clubInfo))
			->get_template_part('teams', 'admin');
	}

	/**
	 * Show the fixtures for the admin-page
	 *
	 * @return void
	 */
	public function showAdminFixtures()
	{
		// 20 days
		$fixtures = $this->doRequest("programma", true, array("aantaldagen=20", "sorteervolgorde=datum-team-tijd", "eigenwedstrijden=ja", "weekoffset=0"));

		$fixtures = $this->orderMatchesByDateTeam($fixtures);

		// Load the correct template
		$this->template
			->set_template_data(array('fixtures' => $fixtures))
			->get_template_part('fixtures', 'admin');
	}

	/**
	 * Show the results for the admin-page
	 *
	 * @return void
	 */
	public function showAdminResults()
	{
		// 20 days back
		$results = $this->doRequest("uitslagen", true, array("aantaldagen=20", "sorteervolgorde=datum-team-tijd-omgekeerd", "eigenwedstrijden=ja", "weekoffset=-3"));

		$results = $this->orderMatchesByDateTeam($results);

		// Load the correct template
		$this->template
			->set_template_data(array('fixtures' => $results))
			->get_template_part('results', 'admin');
	}

	/**
	 * Show the fixtures
	 *
	 * @param array $atts Shortcode attributes
	 * @return void
	 */
	public function showFixtures($atts)
	{
		$atts = shortcode_atts(array(
			'aantaldagen' => in_array('aantaldagen', $atts) ? $atts['aantaldagen'] : ($atts['team'] !== '' ? 365 : 13),
			'sorteervolgorde' => 'datum-team-tijd',
			'eigenwedstrijden' => 'ja',
			'weekoffset' => $atts['aantalwekenvooruit'],
			'teamcode' => $atts['team'],
			'template' => ''
		), $atts);

		$fixtures = $this->doRequest("programma", true, $this->getRequestArray($atts));

		$fixtures = $this->orderMatchesByDateTeam($fixtures);

		// Load the correct template
		$this->template
			->set_template_data(array('fixtures' => $fixtures))
			->get_template_part('fixtures', $atts['template']);
	}

	/**
	 * Show the results
	 *
	 * @param array $atts Shortcode attributes
	 * @return void
	 */
	public function showResults($atts)
	{
		// Calculate the number of weeks since the start of the current season
		$competition_start_year = date('n') >= 7 ? date('Y') : date('Y') - 1;
		$number_of_weeks = ceil(abs(strtotime($competition_start_year . '-07-01') - strtotime(date('Y-m-d'))) / 60 / 60 / 24 / 7);

		// Calculate the number of days that have to be shown
		$number_of_days = $atts['aantaldagen'] !== '' ? $atts['aantaldagen'] : ($atts['team'] !== '' ? $number_of_weeks * 7 : 14);

		$atts = shortcode_atts(array(
			'sorteervolgorde' => 'datum-team-tijd-omgekeerd',
			'eigenwedstrijden' => $atts['team'] !== '' ? 'nee' : 'ja',
			'weekoffset' => $atts['aantalwekenvooruit'] < 0 ? $atts['aantalwekenvooruit'] : ($atts['team'] !== '' ? -$number_of_weeks : -1),
			'teamcode' => $atts['team'],
			'template' => ''
		), $atts);

		$atts['aantaldagen'] = $number_of_days;

		$results = $this->doRequest("uitslagen", true, $this->getRequestArray($atts));

		$results = $this->orderMatchesByDateTeam($results);

		// Load the correct template
		$this->template
			->set_template_data(array('results' => $results))
			->get_template_part('results', $atts['template']);
	}

	/**
	 * Show the fixtures and results of today
	 *
	 * @param array $atts Shortcode attributes
	 * @return void
	 */
	public function showFixturesResults($atts)
	{
		$atts = shortcode_atts(array(
			'aantaldagen' => in_array('aantaldagen', $atts) ? $atts['aantaldagen'] : ($atts['team'] !== '' ? 365 : 13),
			'sorteervolgorde' => 'datum-team-tijd',
			'eigenwedstrijden' => 'ja',
			'weekoffset' => $atts['aantalwekenvooruit'],
			'template' => ''
		), $atts);

		$resultAtts = shortcode_atts(array(
			'aantaldagen' => 7,
			'sorteervolgorde' => 'datum-team-tijd',
			'eigenwedstrijden' => 'ja',
			'weekoffset' => -1,
			'template' => ''
		), $atts);

		$fixtures = $this->doRequest("programma", true, $this->getRequestArray($atts));
		$results = $this->doRequest("uitslagen", true, $this->getRequestArray($resultAtts));

		$matches = array_merge($fixtures, $results);
		usort($matches, function ($a, $b) {
			return strcmp($a->datum, $b->datum);
		});

		$matches = $this->orderMatchesByDateTeam($matches);

		// Load the correct template
		$this->template
			->set_template_data(array('fixtures' => $matches))
			->get_template_part('fixtures', $atts['template']);
	}

	/**
	 * Show the match details
	 *
	 * @param array $atts Shortcode attributes
	 * @return void
	 */
	public function showMatchDetail($atts)
	{
		// Validate and sanitize wedstrijd parameter
		$wedstrijd_code = '';
		if (isset($_GET['wedstrijd'])) {
			$wedstrijd_code = sanitize_text_field(wp_unslash($_GET['wedstrijd']));
		}

		$matchAtts = shortcode_atts(array(
			'wedstrijdcode' => $wedstrijd_code,
			'template' => ''
		), $atts);
		$teamsAtts = shortcode_atts(array(
			'template' => ''
		), $atts);

		try {
			$match = $this->doRequest("wedstrijd-informatie", true, $this->getRequestArray($matchAtts));
			$history = $this->doRequest("wedstrijd-historische-resultaten", true, $this->getRequestArray($matchAtts));
			$teams = $this->getTeams(); // Use cached teams

			$pouleAtts = shortcode_atts(array(
				'poulecode' => $match?->wedstrijdinformatie?->poulecode,
				'template' => ''
			), $atts);
			$poule = $this->doRequest("poulestand", true, $this->getRequestArray($pouleAtts));

			if ($match) {
				$match->history = $history;
				$match->poule = $poule;
				$match->teams = $teams;

				// Load the correct template
				$this->template
					->set_template_data(array('match' => $match))
					->get_template_part('match', $atts['template']);
			}
		} catch (Exception $e) {
			// Silently fail if match details cannot be loaded
			return;
		}
	}

	/**
	 * Show the standings
	 *
	 * @param array $atts Shortcode attributes
	 * @return void
	 */
	public function showStandings($atts)
	{

		$atts = shortcode_atts(array(
			'poulecode' => $atts['poule'],
			'template' => ''
		), $atts);

		$standings = $this->doRequest("poulestand", true, $this->getRequestArray($atts));

		// Load the correct template
		$this->template
			->set_template_data(array('standings' => $standings))
			->get_template_part('standings', $atts['template']);
	}

	/**
	 * Group all fixtures by date
	 *
	 * @param array $fixtures Array of fixture objects
	 * @return stdClass Grouped fixtures by date
	 */
	private function groupFixturesByDate($fixtures)
	{
		$groupedFixtures = new stdClass();

		foreach ($fixtures as $fixture) {
			if (!property_exists($groupedFixtures, strtolower($fixture->kaledatum))) {
				$groupedFixtures->{strtolower($fixture->kaledatum)} = new stdClass();
			}
			$groupedFixtures->{strtolower($fixture->kaledatum)}->{$fixture->wedstrijdcode} = $fixture;
		}
		return $groupedFixtures;
	}

	/**
	 * Add category ID to all teams
	 *
	 * @param array|stdClass $teams Teams array or object
	 * @return void
	 */
	private function addAgeCategoryToTeams($teams)
	{
		if ($teams) {
			foreach ($teams as $team) {
				// If the team name contains any of the keys in the $ageCategories array, set the $team->leeftijdscategorieid to the corresponding value
				foreach ($this->ageCategories as $key => $value) {
					if (strpos(strtolower($team->teamnaam), strtolower($key)) !== false) {
						$team->leeftijdscategorieid = $value;
						continue 2;
					}
				}

				// If the $team->geslacht is set to 'man' and the $team->leeftijdscategorie is set to 'Senioren', set the $team->leeftijdscategorieid to 999
				if ($team->geslacht == 'man' && $team->leeftijdscategorie == 'Senioren') {
					$team->leeftijdscategorieid = 999;

					// If $team->speeldag contains 'Vrijdag', set the $team->leeftijdscategorieid
					if (strpos(strtolower($team->speeldag), 'vrijdag') !== false) {
						$team->leeftijdscategorieid = 899;
					}
					continue;
				}

				// If the $team->geslacht is set to 'vrouw' and the $team->leeftijdscategorie is set to 'Senioren Vrouwen', set the $team->leeftijdscategorieid to 995
				if ($team->geslacht == 'vrouw' && $team->leeftijdscategorie == 'Senioren Vrouwen') {
					$team->leeftijdscategorieid = 995;

					// If $team->speeldag contains 'Vrijdag', set the $team->leeftijdscategorieid
					if (strpos(strtolower($team->speeldag), 'vrijdag') !== false) {
						$team->leeftijdscategorieid = 895;
					}
					continue;
				}

				// If the team name does not contain any of the keys in the $ageCategories array, set the $team->leeftijdscategorieid to 0
				$team->leeftijdscategorieid = 0;
			}
		}
	}

	/**
	 * Order teams by category
	 *
	 * @param array $teams Teams array
	 * @return stdClass Ordered teams by category
	 */
	private function orderTeamsByCategory($teams)
	{
		$groupedTeams = new stdClass();

		usort($teams, array($this, "compareTeamIDs"));

		foreach ($teams as $team) {
			if (!property_exists($groupedTeams, strtolower($team->leeftijdscategorieid))) {
				$groupedTeams->{strtolower($team->leeftijdscategorieid)} = new stdClass();
			}

			if (property_exists($groupedTeams->{strtolower($team->leeftijdscategorieid)}, $team->teamnaam)) {
				$team->poules = $groupedTeams->{strtolower($team->leeftijdscategorieid)}->{$team->teamnaam}->poules;
			} else {
				$team->poules = '';
			}

			$groupedTeams->{strtolower($team->leeftijdscategorieid)}->{$team->teamnaam} = $team;
			$team->poules .=  $groupedTeams->{strtolower($team->leeftijdscategorieid)}->{$team->teamnaam}->poulecode . ' (' . $groupedTeams->{strtolower($team->leeftijdscategorieid)}->{$team->teamnaam}->competitienaam . ')<br>';
		}

		$flattenedTeams = new stdClass();
		foreach ($groupedTeams as $category) {

			$category = get_object_vars($category);

			usort($category, function ($a, $b) {
				return strcmp($a->teamnaam, $b->teamnaam);
			});

			foreach ($category as $key => $team) {
				$flattenedTeams->{$team->teamcode} = $team;
			}
		}

		return $flattenedTeams;
	}

	/**
	 * Order matches by date first, then by team
	 *
	 * @param array|false $matches Matches array or false
	 * @return stdClass Ordered matches
	 */
	private function orderMatchesByDateTeam($matches)
	{
		// Use cached teams to avoid redundant API calls
		if (!$this->teamsLoaded) {
			$this->getTeams();
		}

		$matches = $this->addAgeCategoryToFixtures($matches);

		if (!$matches) {
			return new stdClass();
		}

		// Group matches by date using array for better performance
		$matchesByDate = array();

		foreach ($matches as $match) {
			$dateKey = strtolower($match->datum);
			if (!isset($matchesByDate[$dateKey])) {
				$matchesByDate[$dateKey] = array();
			}
			$matchesByDate[$dateKey][] = $match;
		}

		// Sort matches within each date
		foreach ($matchesByDate as &$matchDate) {
			usort($matchDate, function ($a, $b) {
				// Sort by age category (descending), then team name
				if (isset($a->leeftijdscategorieid) && isset($b->leeftijdscategorieid)) {
					if ($a->leeftijdscategorieid != $b->leeftijdscategorieid) {
						return $a->leeftijdscategorieid > $b->leeftijdscategorieid ? -1 : 1;
					}
				}
				if (isset($a->teamnaam) && isset($b->teamnaam)) {
					return strcmp($a->teamnaam, $b->teamnaam);
				}
				return 0;
			});
		}
		unset($matchDate); // Break reference

		// Flatten back to stdClass indexed by wedstrijdcode
		$flattenedMatches = new stdClass();
		foreach ($matchesByDate as $matchDate) {
			foreach ($matchDate as $match) {
				$flattenedMatches->{$match->wedstrijdcode} = $match;
			}
		}

		return $flattenedMatches;
	}

	/**
	 * Add age category to all fixtures
	 *
	 * @param array|false $fixtures Fixtures array or false
	 * @return array|false Fixtures with age category added
	 */
	private function addAgeCategoryToFixtures($fixtures)
	{
		if ($fixtures && $this->teams) {
			// Create a lookup table for faster team access - O(n) instead of O(n*m)
			$teamLookup = array();
			foreach ($this->teams as $team) {
				$teamLookup[$team->teamcode] = $team;
			}

			// Now lookup is O(1) per fixture instead of O(m)
			foreach ($fixtures as $fixture) {
				// Check home team
				if (isset($teamLookup[$fixture->thuisteamid])) {
					$fixture->leeftijdscategorieid = $teamLookup[$fixture->thuisteamid]->leeftijdscategorieid;
				}
				// Check away team if home team not found
				elseif (isset($teamLookup[$fixture->uitteamid])) {
					$fixture->leeftijdscategorieid = $teamLookup[$fixture->uitteamid]->leeftijdscategorieid;
				}
			}
		}

		return $fixtures;
	}

	/**
	 * Find the team involved by this fixture
	 *
	 * @param stdClass $fixture Fixture object
	 * @return stdClass|null Team object or null
	 */
	private function getTeamFromFixture($fixture)
	{
		if ($this->teams) {
			foreach ($this->teams as $team) {
				if ($fixture->thuisteamid == $team->teamcode || $fixture->uitteamid == $team->teamcode) {
					return $team;
				}
			}
		}

		return null;
	}

	/**
	 * Build an array with all request-parameters to be sent to Sportlink
	 *
	 * @param array $atts Attributes array
	 * @return array Request parameters
	 */
	private function getRequestArray($atts)
	{
		$requestAttributes = array();

		// Whitelist of allowed parameters
		$allowed_params = array(
			'aantaldagen',
			'sorteervolgorde',
			'eigenwedstrijden',
			'weekoffset',
			'teamcode',
			'poulecode',
			'wedstrijdcode',
			'template'
		);

		foreach ($atts as $key => $value) {
			// Skip template parameter (not sent to API)
			if ($key === 'template') {
				continue;
			}

			// Only allow whitelisted parameters
			if (!in_array($key, $allowed_params)) {
				continue;
			}

			// Sanitize value based on type
			$sanitized_value = $this->sanitizeApiParameter($key, $value);

			if ($sanitized_value !== '') {
				$requestAttributes[] = sanitize_key($key) . '=' . $sanitized_value;
			}
		}
		return $requestAttributes;
	}

	/**
	 * Sanitize API parameters based on their expected type
	 *
	 * @param string $key Parameter key
	 * @param mixed $value Parameter value
	 * @return string|int Sanitized value
	 */
	private function sanitizeApiParameter($key, $value)
	{
		switch ($key) {
			case 'aantaldagen':
			case 'weekoffset':
				// Integer values (can be negative for weekoffset)
				return intval($value);

			case 'teamcode':
			case 'poulecode':
			case 'wedstrijdcode':
				// Alphanumeric codes
				return sanitize_text_field($value);

			case 'sorteervolgorde':
				// Specific string values
				$allowed_values = array(
					'datum-team-tijd',
					'datum-team-tijd-omgekeerd',
					'team-datum-tijd'
				);
				return in_array($value, $allowed_values) ? $value : 'datum-team-tijd';

			case 'eigenwedstrijden':
				// Boolean-like values
				return in_array($value, array('ja', 'nee')) ? $value : 'ja';

			default:
				return sanitize_text_field($value);
		}
	}

	/**
	 * Compare by team category order
	 *
	 * @param stdClass $a First team
	 * @param stdClass $b Second team
	 * @return int Comparison result
	 */
	private function compareTeamIDs($a, $b)
	{
		if ($a->leeftijdscategorieid == $b->leeftijdscategorieid) {
			return 0;
		}
		return ($a->leeftijdscategorieid > $b->leeftijdscategorieid) ? -1 : 1;
	}

	/**
	 * Compare by default team order
	 *
	 * @param stdClass $a First team
	 * @param stdClass $b Second team
	 * @return int Comparison result
	 */
	private function compareTeamOrder($a, $b)
	{
		if ($a->teamvolgorde == $b->teamvolgorde) {
			return 0;
		}
		return ($a->teamvolgorde < $b->teamvolgorde) ? -1 : 1;
	}

	/**
	 * Check if a given URL exists (with timeout and caching)
	 *
	 * @param string $url URL to check
	 * @return bool True if URL exists
	 */
	private function url_exists($url)
	{
		// Check transient cache first
		$cache_key = 'sportlink_url_check_' . md5($url);
		$cached_result = get_transient($cache_key);

		if ($cached_result !== false) {
			return $cached_result === 'exists';
		}

		// Use wp_remote_head for better WordPress integration
		$response = wp_remote_head($url, array(
			'timeout' => 5,
			'sslverify' => get_option('sportlink_club_dataservices_overwrite_ssl') !== 'true'
		));

		if (is_wp_error($response)) {
			// Cache negative result for 5 minutes
			set_transient($cache_key, 'not_exists', 300);
			return false;
		}

		$status_code = wp_remote_retrieve_response_code($response);
		$exists = $status_code >= 200 && $status_code < 400;

		// Cache result for 1 hour
		set_transient($cache_key, $exists ? 'exists' : 'not_exists', 3600);

		return $exists;
	}
}

class Sportlink_Template_Loader extends Gamajo_Template_Loader
{
	/**
	 * Prefix for filter names.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $filter_prefix = 'sportlink-knvb';

	/**
	 * Directory name where custom templates for this plugin should be found in the theme.
	 */
	protected $theme_template_directory = 'sportlink-knvb';

	/**
	 * Reference to the root directory path of this plugin.
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $plugin_directory = SPORTLINK_PLUGIN_DIR;

	/**
	 * Directory name where templates are found in this plugin.
	 *
	 * Can either be a defined constant, or a relative reference from where the subclass lives.
	 *
	 * e.g. 'templates' or 'includes/templates', etc.
	 */
	protected $plugin_template_directory = 'templates';
}
