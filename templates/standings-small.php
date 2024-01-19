<table class="sportlink sportlink--standings standings">
	<thead>
		<tr>
			<td class="standings__position"></td>
			<td class="standings__name">Team</td>
			<td class="standings__played">Gespeeld</td>
			<td class="standings__points">Punten</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data->standings as $standing) { ?>
			<tr class="standings__standing <?php echo $standing->eigenteam == "true" ? "standings__standing--own-team" : ""; ?>">
				<td class="standings__position"><?php echo $standing->positie; ?></td>
				<td class="standings__name"><?php echo $standing->teamnaam; ?></td>
				<td class="standings__played"><?php echo $standing->gespeeldewedstrijden; ?></td>
				<td class="standings__points"><?php echo $standing->punten; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
