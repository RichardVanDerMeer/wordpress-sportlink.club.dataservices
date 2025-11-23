<?php

/**
 * Template for displaying poule standing details in admin modal
 *
 * Available variables: $stand (array of team standing objects)
 */
?>

<table class="sportlink-detail-table">
	<thead>
		<tr>
			<th>Pos</th>
			<th>Team</th>
			<th>Gespeeld</th>
			<th>Gewonnen</th>
			<th>Gelijk</th>
			<th>Verloren</th>
			<th>Doelpunten</th>
			<th>Punten</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($stand as $team): ?>
			<tr>
				<td><?php echo esc_html($team->positie); ?></td>
				<td><?php echo esc_html($team->teamnaam); ?></td>
				<td><?php echo esc_html($team->gespeeldewedstrijden); ?></td>
				<td><?php echo esc_html($team->gewonnen); ?></td>
				<td><?php echo esc_html($team->gelijk); ?></td>
				<td><?php echo esc_html($team->verloren); ?></td>
				<td><?php echo esc_html($team->doelpuntenvoor . ' - ' . $team->doelpuntentegen); ?></td>
				<td><strong><?php echo esc_html($team->punten); ?></strong></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
