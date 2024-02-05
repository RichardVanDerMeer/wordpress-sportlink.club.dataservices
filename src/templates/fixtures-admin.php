<table class="form-table">
	<thead>
		<tr valign="top">
			<th>Datum</th>
			<th>Tijd</th>
			<th>Wedstrijd</th>
			<th>Wedstrijdnummer</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($data->fixtures as $fixture) {
		?>
			<tr valign="top">
				<td>
					<?php echo date_i18n('d M', strtotime($fixture->wedstrijddatum)); ?>
				</td>
				<td><?php echo $fixture->aanvangstijd; ?></td>
				<td><?php echo $fixture->wedstrijd; ?></td>
				<td><?php echo $fixture->wedstrijdnummer; ?></td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
