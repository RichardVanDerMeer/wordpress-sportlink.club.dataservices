<table class="sportlink sportlink--standings standings">
	<thead>
		<tr>
			<td class="standings__position"></td>
			<td class="standings__name">Team</td>
			<td class="standings__played">Gespeeld</td>
			<td class="standings__won">Gewonnen</td>
			<td class="standings__drawn">Gelijk</td>
			<td class="standings__lost">Verloren</td>
			<td class="standings__goals-scored">Goals voor</td>
			<td class="standings__goals-conceded">Goals tegen</td>
			<td class="standings__goals-total">Saldo</td>
			<td class="standings__points-lost">Verliespunten</td>
			<td class="standings__points">Punten</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($data->standings as $standing) { ?>
			<tr class="standings__standing <?php echo esc_attr($standing->eigenteam == "true" ? "standings__standing--own-team" : ""); ?>">
				<td class="standings__position"><?php echo esc_html($standing->positie); ?></td>
				<td class="standings__name"><?php echo esc_html($standing->teamnaam); ?></td>
				<td class="standings__played"><?php echo esc_html($standing->gespeeldewedstrijden); ?></td>
				<td class="standings__won"><?php echo esc_html($standing->gewonnen); ?></td>
				<td class="standings__drawn"><?php echo esc_html($standing->gelijk); ?></td>
				<td class="standings__lost"><?php echo esc_html($standing->verloren); ?></td>
				<td class="standings__goals-scored"><?php echo esc_html($standing->doelpuntenvoor); ?></td>
				<td class="standings__goals-conceded"><?php echo esc_html($standing->doelpuntentegen); ?></td>
				<td class="standings__goals-total"><?php echo esc_html($standing->doelsaldo); ?></td>
				<td class="standings__points-lost"><?php echo esc_html($standing->verliespunten); ?></td>
				<td class="standings__points"><?php echo esc_html($standing->punten); ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
