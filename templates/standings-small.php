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
			<tr class="standings__standing <?php echo esc_attr($standing->eigenteam == "true" ? "standings__standing--own-team" : ""); ?>">
				<td class="standings__position"><?php echo esc_html($standing->positie); ?></td>
				<td class="standings__name"><?php echo esc_html($standing->teamnaam); ?></td>
				<td class="standings__played"><?php echo esc_html($standing->gespeeldewedstrijden); ?></td>
				<td class="standings__points"><?php echo esc_html($standing->punten); ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
