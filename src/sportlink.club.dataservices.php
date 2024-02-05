<?php

/**
 * Plugin Name: Sportlink KNVB Club.Dataservices
 * Description: Toon het volledige wedstrijdprogramma, uitslagen, standen, teams en wedstrijd-details vanuit Sportlink Club.Dataservice
 * Version: 1.1.0
 * Author: Richard van der Meer
 * Author URI: http://richardvandermeer.nl/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * */

define("SPORTLINK_PLUGIN_DIR", plugin_dir_path(__FILE__));


if (!class_exists('Gamajo_Template_Loader')) {
	require plugin_dir_path(__FILE__) . 'includes/class-gamajo-template-loader.php';
}

/***********************************************************************
 Admin init functie
 */
if (is_admin()) {
	add_action('admin_menu', 'sportlink_club_dataservices_menu');
	add_action('admin_init', 'sportlink_club_dataservices_register_settings');
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
			case 'wedstrijd':
				$sportlinkClient->showMatchDetail($atts);
				break;
		}
	} catch (Exception $e) {
		echo '<div class="sportlink-error"><p>Er kan momenteel geen verbinding worden gemaakt met de Sportlink API</p></div>';
	}

	return ob_get_clean();
}
add_shortcode('sportlink', 'shortcode_sportlink_club_dataservices');

/***********************************************************************
 Rendering options page
 */
function sportlink_club_dataservices_options()
{
	$sportlinkClient = null;
	try {
		$sportlinkClient = new SportlinkClient(get_option('sportlink_club_dataservices_key'), get_option('sportlink_club_dataservices_cachetime'));
	} catch (Exception $e) {
		echo '<div class="sportlink-error"><p>Er kan momenteel geen verbinding worden gemaakt met de Sportlink API</p></div>';
	}

?>
	<div class="wrap">

		<h2>Sportlink - KNVB</h2>

		<?php
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
		?>

		<h2 class="nav-tab-wrapper">
			<a href="?page=sportlink.club.dataservices&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Instellingen</a>
			<?php if ($sportlinkClient) : ?>
				<a href="?page=sportlink.club.dataservices&tab=teams" class="nav-tab <?php echo $active_tab == 'teams' ? 'nav-tab-active' : ''; ?>">Teams</a>
				<a href="?page=sportlink.club.dataservices&tab=fixtures" class="nav-tab <?php echo $active_tab == 'fixtures' ? 'nav-tab-active' : ''; ?>">Programma</a>
				<a href="?page=sportlink.club.dataservices&tab=team-shortcodes" class="nav-tab <?php echo $active_tab == 'team-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcodes per team</a>
				<a href="?page=sportlink.club.dataservices&tab=match-shortcodes" class="nav-tab <?php echo $active_tab == 'match-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcodes per wedstrijd</a>
				<a href="?page=sportlink.club.dataservices&tab=parameter-shortcodes" class="nav-tab <?php echo $active_tab == 'parameter-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcode parameters</a>
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

				<?php if ($sportlinkClient && $sportlinkClient->isConnected() && $sportlinkClient->getClubInfo()) : ?>
					<tr valign="top">
						<th scope="row">Club</th>
						<td>
							<?php echo $sportlinkClient->getClubInfo()->clubnaam; ?> (<?php echo $sportlinkClient->getClubInfo()->clubcode; ?>)
							<br><br>
							<img alt="<?php echo $sportlinkClient->getClubInfo()->clubnaam; ?>" src="data:image/jpg;base64,<?php echo $sportlinkClient->getClubInfo()->kleinlogo; ?>" />
						</td>
					</tr>
				<?php endif; ?>
			</table>

		<?php
			submit_button();
		} elseif ($active_tab == 'teams') {
			$sportlinkClient->showTeams();
		} elseif ($active_tab == 'fixtures') {
			$sportlinkClient->showAdminFixtures();
		}
		?>
	</form>
<?php

}

function sportlink_club_dataservices_register_settings()
{
	register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_key');
	register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_cachetime');
	register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_overwrite_ssl');
}



class SportlinkClient
{
	const API_URL = 'https://data.sportlink.com/';

	protected $apiKey;
	private $clubInfo;
	private $isConnected = false;
	private $teams;
	private $cacheTime;
	private $template;

	/**
	 * Create an ageCategories variable that maps the ageCategories to the ageCategory codes
	 */
	private $ageCategories = array(
		'senioren' => 999,
		'senioren vrouwen' => 995,
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

	// Connect to the API
	public function connect()
	{
		$apiInfoURL = SportlinkClient::API_URL . "clubgegevens" . "?client_id=" . $this->apiKey;
		if (!$this->url_exists($apiInfoURL)) {
			throw new Exception("Sportlink API not found");
		} else {
			$this->isConnected = true;
		}
	}

	// Check if client is connected to API
	public function isConnected()
	{
		return $this->isConnected;
	}

	// Request club info
	private function requestClubInfo()
	{
		$clubgegevens = $this->doRequest("clubgegevens");
		if ($clubgegevens) {
			return $clubgegevens->gegevens;
		}
		return false;
	}

	// Return club info
	public function getClubInfo()
	{
		return $this->clubInfo;
	}

	// Make a request to the Sportlink API
	//  or get it from the local cache file
	public function doRequest($endpoint, $cached = true, $parameters = array())
	{
		if (!$this->isConnected) {
			throw new Exception("Not connected to Sportlink API");
		} else {
			// Build the JSON request string from the given array of parameters
			// Also build the unique cache filename from the given array of parameters
			$jsonurl = SportlinkClient::API_URL . $endpoint . "?client_id=" . $this->apiKey;

			$arrContextOptions = null;
			if (get_option('sportlink_club_dataservices_overwrite_ssl') === 'true') {
				$arrContextOptions = array(
					"ssl" => array(
						"verify_peer" => false,
						"verify_peer_name" => false,
					),
				);
			}

			$cacheParameters = "";
			if (!is_null($parameters)) {
				foreach ($parameters as $param) {
					$jsonurl .= "&" . $param;
					$cacheParameters .= "-" . $param;
				}
			}

			$cachePath = wp_upload_dir()['basedir'] . "/sportlink.club.dataservices/cache/";
			// Create cache directory if it doesn't exist
			if (!is_dir($cachePath)) {
				mkdir($cachePath, 0777, true);
			}

			$cacheFile = $cachePath . $endpoint . "-" . md5($cacheParameters) . ".json";

			// When we're allowed to use a cached version, check if that version exists and it doesn't exceed max age
			if ($cached) {
				if (file_exists($cacheFile)) {

					// If cache file is older then allowed cache time, refresh it
					if (intval(date("i", time() - filemtime($cacheFile))) > $this->cacheTime) {
						// Request online resource
						try {
							$json = @file_get_contents($jsonurl, false, stream_context_create($arrContextOptions));
						} catch (Exception $e) {
							throw new Exception("Sportlink API endpoint could not be reached", 1);
						}


						// Write the cache file
						file_put_contents($cacheFile, $json);
					} else {
						// If cache file is valid, read that one
						$jsonurl = $cacheFile;
						$json = file_get_contents($jsonurl);
					}
				} else {
					// If cache file is doesn't exist, request online resource
					$json = file_get_contents($jsonurl, false, stream_context_create($arrContextOptions));

					// Write the cache file
					file_put_contents($cacheFile, $json);
				}
			} else {
				// When we're not allowed to use cached version, request online resource
				$json = @file_get_contents($jsonurl, false, stream_context_create($arrContextOptions));

				// Write the cache file
				file_put_contents($cacheFile, $json);
			}

			return json_decode($json);
		}
	}

	// Show all teams in regular competition
	public function showTeams()
	{
		$this->teams = $this->doRequest("teams", true, null);

		$this->addAgeCategoryToTeams($this->teams);
		$this->teams = $this->orderTeamsByCategory($this->teams);

		// Load the correct template
		$this->template
			->set_template_data(array('teams' => $this->teams, 'clubInfo' => $this->clubInfo))
			->get_template_part('teams', 'admin');
	}

	// Show the fixtures for the admin-page
	public function showAdminFixtures()
	{
		$fixtures = $this->doRequest("programma", true, array("aantaldagen=140", "sorteervolgorde=datum-team-tijd", "eigenwedstrijden=ja", "weekoffset=0"));

		$fixtures = $this->orderMatchesByDateTeam($fixtures);

		// Load the correct template
		$this->template
			->set_template_data(array('fixtures' => $fixtures))
			->get_template_part('fixtures', 'admin');
	}

	// Show the fixtures
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

	// Show the results
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

	// Show the fixtures and results of today
	public function showMatchDetail($atts)
	{
		$matchAtts = shortcode_atts(array(
			'wedstrijdcode' => stripslashes(esc_attr(esc_html($_GET['wedstrijd']))),
			'template' => ''
		), $atts);
		$teamsAtts = shortcode_atts(array(
			'template' => ''
		), $atts);

		$match = @$this->doRequest("wedstrijd-informatie", true, $this->getRequestArray($matchAtts));
		$history = @$this->doRequest("wedstrijd-historische-resultaten", true, $this->getRequestArray($matchAtts));
		$teams = @$this->doRequest("teams", true, $this->getRequestArray($teamsAtts));

		$pouleAtts = shortcode_atts(array(
			'poulecode' => $match?->wedstrijdinformatie?->poulecode,
			'template' => ''
		), $atts);
		$poule = @$this->doRequest("poulestand", true, $this->getRequestArray($pouleAtts));

		if ($match) {
			$match->history = $history;
			$match->poule = $poule;
			$match->teams = $teams;


			// Load the correct template
			$this->template
				->set_template_data(array('match' => $match))
				->get_template_part('match', $atts['template']);
		}
	}

	// Show the standings
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

	// Add category ID to all teams
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

	// Order teams by category
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

	// Order matches by date first,
	//   then by team
	private function orderMatchesByDateTeam($matches)
	{
		$this->teams = $this->doRequest("teams", true, null);
		$this->addAgeCategoryToTeams($this->teams);


		$matches = $this->addAgeCategoryToFixtures($matches);

		$matchesByDate = new stdClass();
		$flattenedMatches = new stdClass();

		if ($matches) {
			foreach ($matches as $match) {
				if (!property_exists($matchesByDate, strtolower($match->datum))) {
					$matchesByDate->{strtolower($match->datum)} = new stdClass();
				}
				$matchesByDate->{strtolower($match->datum)}->{$match->wedstrijdcode} = $match;
			}

			foreach ($matchesByDate as $matchDate) {
				$matchDate = get_object_vars($matchDate);

				usort($matchDate, function ($a, $b) {
					if (isset($a->leeftijdscategorieid) && isset($b->leeftijdscategorieid)) {
						if ($a->leeftijdscategorieid == $b->leeftijdscategorieid) {
							if (isset($a->teamnaam) && isset($b->teamnaam)) {
								return strcmp($a->teamnaam, $b->teamnaam);
							}
							return 0;
						}
						return $a->leeftijdscategorieid > $b->leeftijdscategorieid ? -1 : 1;
					}
				});

				foreach ($matchDate as $key => $match) {
					$flattenedMatches->{$match->wedstrijdcode} = $match;
				}
			}
		}

		return $flattenedMatches;
	}

	// Add age category to all fixtures
	private function addAgeCategoryToFixtures($fixtures)
	{
		if ($fixtures) {
			foreach ($fixtures as $fixture) {

				$team = $this->getTeamFromFixture($fixture);
				if (!is_null($team)) {
					$fixture->leeftijdscategorieid = $team->leeftijdscategorieid;
				}
			}
		}

		return $fixtures;
	}

	// Find the team involved by this fixture
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

	// Build an array with all request-parameters to be sent to Sportlink
	private function getRequestArray($atts)
	{
		$requestAttributes = array();
		foreach ($atts as $key => $value) {
			$requestAttributes[] = $key . '=' . $value;
		}
		return $requestAttributes;
	}

	// Compare by team category order
	private function compareTeamIDs($a, $b)
	{
		if ($a->leeftijdscategorieid == $b->leeftijdscategorieid) {
			return 0;
		}
		return ($a->leeftijdscategorieid > $b->leeftijdscategorieid) ? -1 : 1;
	}

	// Compare by default team order
	private function compareTeamOrder($a, $b)
	{
		if ($a->teamvolgorde == $b->teamvolgorde) {
			return 0;
		}
		return ($a->teamvolgorde < $b->teamvolgorde) ? -1 : 1;
	}

	// Check if a given URL exists
	private function url_exists($url)
	{
		$handle = curl_init($url);
		curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($handle);
		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		curl_close($handle);
		return $httpCode >= 200 && $httpCode < 400;

		//   // Replace default stream settings
		//   //  We only need the headers of the request
		//   stream_context_set_default(
		//     array(
		//       'http' => array(
		//         'method' => 'HEAD'
		//       )
		//     )
		//   );
		//   $headers = get_headers($url);
		//   if (!$headers) {
		//     return false;
		//   }
		//   $status = substr($headers[0], 9, 3);

		//   // Restore default stream settings
		//   stream_context_set_default(
		//     array(
		//       'http' => array(
		//         'method' => 'GET'
		//       )
		//     )
		//   );

		//   // URL exists if status is between 200 and 400
		//   if ($status >= 200 && $status < 400) {
		//     return true;
		//   } else {
		//     return false;
		//   }
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
