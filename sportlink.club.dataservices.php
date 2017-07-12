<?php
/**
 * Plugin Name: Sportlink KNVB Club.Dataservices
 * Description: Toon het volledige wedstrijdprogramma, uitslagen, standen en teams vanuit Sportlink Club.Dataservice
 * Version: 0.0.1
 * Author: Richard van der Meer
 * Author URI: http://richardvandermeer.nl/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * */

// PHP error reporting, should be turned off in production
// TODO: Turn error reporting off in production
error_reporting(E_ALL);
ini_set("display_errors", 1);


/***********************************************************************
 Admin init functie
 */
if(is_admin()) {
  add_action('admin_menu', 'sportlink_club_dataservices_menu');
  add_action('admin_init', 'sportlink_club_dataservices_register_settings');
}

/***********************************************************************
 Define wordpress options menu
 */
function sportlink_club_dataservices_menu() {
  add_options_page('Sportlink Club.Dataservices opties',   // Title in browser tab
                   'Sportlink Club.Dataservices',          // Title in settings menu
                   'manage_options',    // Capability needed to see this menu
                   'sportlink.club.dataservices',     // Slug
                   'sportlink_club_dataservices_options'); // Function to call when rendering this menu
}

/***********************************************************************
 Rendering options page
 */
function sportlink_club_dataservices_options() {

  $sportlinkClient = new SportlinkClient(get_option('sportlink_club_dataservices_key'), get_option('sportlink_club_dataservices_cachetime'));

  ?>
  <div class="wrap">

    <h2>Sportlink Club.Dataservices</h2>

    <?php
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
    ?>

    <h2 class="nav-tab-wrapper">
      <a href="?page=sportlink.club.dataservices&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Instellingen</a>
      <a href="?page=sportlink.club.dataservices&tab=teams" class="nav-tab <?php echo $active_tab == 'teams' ? 'nav-tab-active' : ''; ?>">Teams</a>
      <a href="?page=sportlink.club.dataservices&tab=fixtures" class="nav-tab <?php echo $active_tab == 'fixtures' ? 'nav-tab-active' : ''; ?>">Programma</a>
      <a href="?page=sportlink.club.dataservices&tab=team-shortcodes" class="nav-tab <?php echo $active_tab == 'team-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcodes per team</a>
      <a href="?page=sportlink.club.dataservices&tab=match-shortcodes" class="nav-tab <?php echo $active_tab == 'match-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcodes per wedstrijd</a>
      <a href="?page=sportlink.club.dataservices&tab=parameter-shortcodes" class="nav-tab <?php echo $active_tab == 'parameter-shortcodes' ? 'nav-tab-active' : ''; ?>">Shortcode parameters</a>
    </h2>
  </div>

  <form method="post" action="options.php">
  <?php
  if( $active_tab == 'settings' ) {
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

      <?php if ($sportlinkClient->isConnected()) : ?>
      <tr valign="top">
        <th scope="row">Club</th>
        <td>
          <?php echo $sportlinkClient->getClubInfo()->clubnaam; ?>
          <br><br>
          <img alt="<?php echo $sportlinkClient->getClubInfo()->clubnaam; ?>" src="data:image/jpg;base64,<?php echo $sportlinkClient->getClubInfo()->kleinlogo; ?>" />
        </td>
      </tr>
      <?php endif; ?>
    </table>

    <?php
    submit_button();
  } elseif( $active_tab == 'teams' ) {
    $sportlinkClient->showTeams();
  } elseif( $active_tab == 'fixtures' ) {
    $sportlinkClient->showFixtures();
  }
  ?>
  </form>
  <?php

}

function sportlink_club_dataservices_register_settings() { // whitelist options
  register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_key');
  register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_cachetime');
}



class SportlinkClient {
  const API_URL = 'https://data.sportlink.com/';

  protected $apiKey;
  private $clubInfo;
  private $isConnected = false;
  private $teams;
  private $cacheTime;


  public function __construct($apiKey, $cacheTime) {
    $this->apiKey = $apiKey;
    $this->cacheTime = $cacheTime ? $cacheTime : 30;

    $this->connect();
    if ($this->isConnected()) {
      $this->clubInfo = $this->requestClubInfo();
    }
  }

  // Connect to the API
  public function connect() {
    $apiInfoURL = SportlinkClient::API_URL . "clubgegevens" . "?client_id=" . $this->apiKey;
    if (!$this->url_exists($apiInfoURL)) {
      // throw new Exception("Sportlink API not found");
    } else {
      $this->isConnected = true;
    }
  }

  // Check if client is connected to API
  public function isConnected() {
    return $this->isConnected;
  }

  // Request club info
  private function requestClubInfo() {
    return $this->doRequest("clubgegevens", true)->gegevens;
  }

  // Return club info
  public function getClubInfo() {
    return $this->clubInfo;
  }

  // Make a request to the Sportlink API
  //  or get it from the local cache file
  public function doRequest($endpoint, $cached = true, $parameters = Array()) {
    if (!$this->isConnected) {
      throw new Exception("Not connected to Sportlink API");
    } else {
      // Build the JSON request string from the given array of parameters
      // Also build the unique cache filename from the given array of parameters
      $jsonurl = SportlinkClient::API_URL . $endpoint . "?client_id=" . $this->apiKey;
      $cacheParameters = "";
      foreach ($parameters as $param) {
        $jsonurl .= "&" . $param;
        $cacheParameters .= "-" . $param;
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

          echo $cacheFile . ": " . intval(date("i", time() - filemtime($cacheFile))) . "<br>";

          // If cache file is older then allowed cache time, refresh it
          if (intval(date("i", time() - filemtime($cacheFile))) > $this->cacheTime) {
            $json = file_get_contents($jsonurl);

            // Write the cache file
            file_put_contents($cacheFile, $json);
          } else {
            $jsonurl = $cacheFile;
            $json = file_get_contents($jsonurl);
          }

        } else {
          $json = file_get_contents($jsonurl);

          // Write the cache file
          file_put_contents($cacheFile, $json);
        }
      } else {
        $json = file_get_contents($jsonurl);

        // Write the cache file
        file_put_contents($cacheFile, $json);
      }

      return json_decode($json);
    }
  }

  // Show all teams in regular competition
  public function showTeams() {
    $this->teams = $this->doRequest("teams", true, Array("competitiesoort=regulier"));
    ?>
    <table class="form-table">
      <thead>
        <tr valign="top">
          <th>Team</th>
          <th>Teamcode</th>
          <th>Poulecode</th>
          <th>Leeftijdscategorie</th>
        </tr>
      </thead>
      <tbody>
    <?php
    $this->addAgeCategoryToTeams($this->teams);
    $this->teams = $this->orderTeamsByCategory($this->teams);

    foreach ($this->teams as $team) {
    ?>
    <tr valign="top">
      <td><?php echo $this->clubInfo->clubnaam . " " . $team->teamnaam; ?></td>
      <td><?php echo $team->teamcode; ?></td>
      <td><?php echo $team->poulecode; ?></td>
      <td><?php echo $team->leeftijdscategorie; ?></td>
      <td><?php echo $team->leeftijdscategorieid; ?></td>
    </tr>
    <?php
    }
    ?>
      </tbody>
    </table>
    <?php
  }

  public function showFixtures() {
    $fixtures = $this->doRequest("programma", true, Array("aantaldagen=12", "sorteervolgorde=datum-team-tijd", "eigenwedstrijden=nee", "weekoffset=0"));

    $fixtures = $this->orderFixturesByDateTeam($fixtures);

    // print_r($fixtures);

    ?>
    <table class="form-table">
      <thead>
        <tr valign="top">
          <th>Datum</th>
          <th>Wedstrijd</th>
        </tr>
      </thead>
      <tbody>
    <?php
    foreach ($fixtures as $fixture) {
    ?>
    <tr valign="top">
      <td><?php echo $fixture->datum; ?></td>
      <td><?php echo $fixture->wedstrijd; ?></td>
    </tr>
    <?php
    }
    ?>
      </tbody>
    </table>
    <?php
  }

  private function groupFixturesByDate($fixtures) {
    $groupedFixtures = new stdClass();

    foreach ($fixtures as $fixture) {
      if (!property_exists($groupedFixtures, strtolower($fixture->kaledatum))) {
        $groupedFixtures->{strtolower($fixture->kaledatum)} = new stdClass();
      }
      $groupedFixtures->{strtolower($fixture->kaledatum)}->{$fixture->wedstrijdcode} = $fixture;
    }
    return $groupedFixtures;
  }

  // Add category ID to all teams
  private function addAgeCategoryToTeams($teams) {
    foreach ($teams as $team) {
      switch (strtolower($team->leeftijdscategorie)) {
        case "senioren":
        case "senioren vrouwen":
          $team->{"leeftijdscategorieid"} = 99;
          break;
        case "onder 19":
        case "onder 19 meiden":
          $team->{"leeftijdscategorieid"} = 19;
          break;
        case "onder 17":
        case "onder 17 meiden":
          $team->{"leeftijdscategorieid"} = 17;
          break;
        case "onder 15":
        case "onder 15 meiden":
          $team->{"leeftijdscategorieid"} = 15;
          break;
        case "onder 13":
        case "onder 13 meiden":
          $team->{"leeftijdscategorieid"} = 13;
          break;
        case "onder 11":
        case "onder 11 meiden":
          $team->{"leeftijdscategorieid"} = 11;
          break;
        case "onder 9":
        case "onder 9 meiden":
          $team->{"leeftijdscategorieid"} = 9;
          break;
        case "onder 7":
        case "onder 7 meiden":
          $team->{"leeftijdscategorieid"} = 7;
          break;
        default:
          $team->{"leeftijdscategorieid"} = -1;
          break;
      }
    }
  }

  // Group teams by category
  private function orderTeamsByCategory($teams) {
    $groupedTeams = new stdClass();

    foreach ($teams as $team) {
      if (!property_exists($groupedTeams, strtolower($team->leeftijdscategorieid))) {
        $groupedTeams->{strtolower($team->leeftijdscategorieid)} = new stdClass();
      }
      $groupedTeams->{strtolower($team->leeftijdscategorieid)}->{$team->teamnaam} = $team;
    }

    $flattenedTeams = new stdClass();
    foreach ($groupedTeams as $category) {
      foreach ($category as $key => $team) {
        $flattenedTeams->$key = $team;
      }
    }

    return $flattenedTeams;
  }

  private function orderFixturesByDateTeam($fixtures) {
    $this->teams = $this->doRequest("teams", true, Array("competitiesoort=regulier"));
    $this->addAgeCategoryToTeams($this->teams);


    return $this->addAgeCategoryToFixtures($fixtures);
  }

  private function addAgeCategoryToFixtures($fixtures) {
    // print_r($this->teams);

    print_r($fixtures);

    return $fixtures;
  }

  // Compare by team category order
  private function compareTeamIDs($a, $b) {
    if ($a->leeftijdscategorieid == $b->leeftijdscategorieid) {
      return 0;
    }
    return ($a->leeftijdscategorieid > $b->leeftijdscategorieid) ? -1 : 1;
  }

  // Compare by default team order
  private function compareTeamOrder($a, $b) {
    if ($a->teamvolgorde == $b->teamvolgorde) {
      return 0;
    }
    return ($a->teamvolgorde < $b->teamvolgorde) ? -1 : 1;
  }

  // Check if a given URL exists
  private function url_exists($url) {
    // Replace default stream settings
    //  We only need the headers of the request
    stream_context_set_default(
      array(
          'http' => array(
              'method' => 'HEAD'
          )
      )
    );
    $headers = @get_headers($url);
    $status = substr($headers[0], 9, 3);

    // Restore default stream settings
    stream_context_set_default(
      array(
          'http' => array(
              'method' => 'GET'
          )
      )
    );

    // URL exists if status is between 200 and 400
    if ($status >= 200 && $status < 400 ) {
      return true;
    } else {
      return false;
    }
  }
}
