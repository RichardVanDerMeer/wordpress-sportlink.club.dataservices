<?php
global $match;
$match = $data->match;

$homeTeamName = $match->wedstrijdinformatie->thuisteam;
$awayTeamName = $match->wedstrijdinformatie->uitteam;

/**
 * Return the (simplified) match type
 */
function getMatchType($match, $teamInfo)
{
	if (!$teamInfo) {
		if (!$match->wedstrijdinformatie->wedstrijdtype) {
			return 'Oefenwedstrijd';
		}
		return $match->wedstrijdinformatie->wedstrijdtype;
	}
}
?>

<h1>
	<?php echo $match->wedstrijdinformatie->thuisteam; ?> - <?php echo $match->wedstrijdinformatie->uitteam; ?>
</h1>

<div class="match-details">

	<div class="match-detail subtle-block">
		<div class="match-detail__competition">
			<?php echo getMatchType($match, $teamInfo); ?>
		</div>
		<div class="match-detail__date">
			<?php echo wp_date("l j F Y", strtotime($match->wedstrijdinformatie->wedstrijddatetime)); ?><br>
			<?php echo $match->wedstrijdinformatie->aanvangstijdopgemaakt; ?> uur
		</div>

		<div class="match-detail__home">
			<img src="https://logoapi.voetbal.nl/logo.php?clubcode=<?php echo $match->thuisteam->code; ?>" alt="Logo <?php echo $homeTeamName; ?>">
			<?php echo $homeTeamName; ?>
		</div>

		<div class="match-detail__score">
			<?php echo $match->wedstrijdinformatie->thuisscore; ?> - <?php echo $match->wedstrijdinformatie->uitscore; ?>
		</div>



		<div class="match-detail__away">
			<img src="https://logoapi.voetbal.nl/logo.php?clubcode=<?php echo $match->uitteam->code; ?>" alt="Logo <?php echo $awayTeamName; ?>">
			<?php echo $awayTeamName; ?>
		</div>

		<div class="match-detail__information">
			<?php
			$isPlayed = $match->wedstrijdinformatie->uitscore !== null && $match->wedstrijdinformatie->thuisscore !== null;

			if (!$isPlayed && $match->wedstrijdinformatie->veldnaam !== '') {
				echo "Veld: " . $match->wedstrijdinformatie->veldnaam . '<br>';
			}

			if (!$isPlayed && $match->matchofficials->scheidsrechters !== '') {
				echo "Scheidsrechters: " . $match->matchofficials->scheidsrechters . '<br>';
			}
			?>
		</div>
	</div>

	<div class="match-accomodation subtle-block">
		<strong><?php echo $match->accommodatie->naam; ?></strong><br>
		<?php echo $match->accommodatie->straat; ?><br>
		<?php echo $match->accommodatie->plaats; ?><br>
		<a href="tel:<?php echo $match->accommodatie->telefoon; ?>"><?php echo $match->accommodatie->telefoon; ?></a><br><br>
		<a href="<?php echo $match->accommodatie->routeplanner; ?>" target="_blank" rel="noopener noreferrer">Route op Google Maps</a>

	</div>

</div>
