<table class="sportlink sportlink--results results">
	<thead class="results__head">
		<tr>
			<th class="results__date">Datum</th>
			<th class="results__match">Wedstrijd</th>
			<th class="results__time">Uitslag</th>
		</tr>
	</thead>
	<tbody class="results__body">
		<?php
		foreach ($data->results as $fixture) {
			$date = date_create($fixture->wedstrijddatum);
		?>
			<tr class="results__fixture">
				<td class="results__date">
					<?php echo date_i18n('d M', strtotime($fixture->wedstrijddatum)); ?>
				</td>
				<td class="results__match">
					<?php echo $fixture->wedstrijd; ?>
				</td>
				<td class="results__time">
					<?php echo $fixture->uitslag; ?>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
