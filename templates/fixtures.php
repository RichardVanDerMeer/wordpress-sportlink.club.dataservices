<table class="sportlink sportlink--fixtures fixtures">
	<thead class="fixtures__head">
		<tr>
			<th class="fixtures__date">Datum</th>
			<th class="fixtures__time">Tijd</th>
			<th class="fixtures__match">Wedstrijd</th>
		</tr>
	</thead>
	<tbody class="fixtures__body">
		<?php
		foreach ($data->fixtures as $fixture) {
			$date = date_create($fixture->wedstrijddatum);
		?>
			<tr class="fixtures__fixture">
				<td class="fixtures__date">
					<?php echo esc_html(date_i18n('d M', strtotime($fixture->wedstrijddatum))); ?>
				</td>
				<td class="fixtures__time">
					<?php echo esc_html($date->format("H:i")); ?>
				</td>
				<td class="fixtures__match">
					<?php echo esc_html($fixture->wedstrijd); ?>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>
