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

  $sportlinkClient = new SportlinkClient(get_option('sportlink_club_dataservices_key'));

  ?>
  <div class="wrap">

    <h2>Sportlink Club.Dataservices</h2>

    <?php
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'settings';
    ?>

    <h2 class="nav-tab-wrapper">
      <a href="?page=sportlink.club.dataservices&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Instellingen</a>
      <a href="?page=sportlink.club.dataservices&tab=teams" class="nav-tab <?php echo $active_tab == 'teams' ? 'nav-tab-active' : ''; ?>">Teams</a>
      <a href="?page=sportlink.club.dataservices&tab=general-shortcodes" class="nav-tab <?php echo $active_tab == 'general-shortcodes' ? 'nav-tab-active' : ''; ?>">Algemene shortcodes</a>
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

      <?php if ($sportlinkClient->isConnected()) : ?>
      <tr valign="top">
        <th scope="row">Club</th>
        <td>
          <?php echo $sportlinkClient->getClubInfo()->clubnaam; ?>
          <br><br>
          <img alt="Embedded Image" src="data:image/jpg;base64,<?php echo $sportlinkClient->getClubInfo()->kleinlogo; ?>" />
        </td>
      </tr>
      <?php endif; ?>
    </table>

    <?php
    submit_button();
  } elseif( $active_tab == 'teams' ) {
    $sportlinkClient->showTeams();
  }
  ?>
  </form>
  <?php

}

function sportlink_club_dataservices_register_settings() { // whitelist options
  register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_key');
  register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_pathname');
  register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_clubname');
  register_setting('sportlink.club.dataservices-settings-group', 'sportlink_club_dataservices_cachetime');
}



class SportlinkClient {
  const API_URL = 'https://data.sportlink.com/';

  protected $apiKey;
  private $clubInfo;
  private $isConnected = false;


  public function __construct($apiKey) {
    $this->apiKey = $apiKey;

    // Check if client can connect to API
    //  Request all club info
    $apiInfoURL = SportlinkClient::API_URL . "clubgegevens" . "?client_id=" . $this->apiKey;
    if (!$this->url_exists($apiInfoURL)) {
      throw new Exception('Sportlink API not found');
    } else {
      $this->isConnected = true;
      $this->clubInfo = $this->requestClubInfo();
    }
  }

  // Check if client is connected to API
  public function isConnected() {
    return $this->isConnected;
  }

  // Request club info
  private function requestClubInfo() {
    return $this->doRequest("clubgegevens")->gegevens;
  }

  // Return club info
  public function getClubInfo() {
    return $this->clubInfo;
  }

  // Make a request to the Sportlink API
  public function doRequest($endpoint, $parameters = Array()) {
    if (!$this->isConnected) {
      throw new Exception('Not connected to Sportlink API');
    } else {
      $jsonurl = SportlinkClient::API_URL . $endpoint . "?client_id=" . $this->apiKey;
      foreach ($parameters as $param) {
        $jsonurl .= "&" . $param;
      }

      $json = file_get_contents($jsonurl);

      return json_decode($json);
    }
  }

  // Show all teams in regular competition
  public function showTeams() {
    $teams = $this->doRequest("teams", Array("competitiesoort=regulier"));
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
    $this->addAgeCategoryID($teams);
    $teams = $this->groupTeamsByCategory($teams);

    foreach ($teams as $team) {
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


    // $this->addAgeCategoryID($teams);
    // $categoryTeams = (array)$this->groupTeamsByCategory($teams);


    // foreach ($categoryTeams as $category) {
    //   foreach ($category as $team) {
    //     echo $this->clubInfo->clubnaam . " " . $team->teamnaam . " - " . $team->leeftijdscategorie . "<br>\n";
    //   }
    // }
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
  private function addAgeCategoryID($teams) {
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
  private function groupTeamsByCategory($teams) {
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
