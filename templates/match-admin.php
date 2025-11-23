<?php

/**
 * Admin template for displaying detailed match information in modal
 *
 * Available variables: $info, $doelpunten, $kaarten, $wissels, $history, $poule
 */

$has_result = !empty($info->thuisscore) && !empty($info->uitscore);
?>

<?php if ($has_result): ?>
	<div style="text-align: center; padding: 20px; background: #f0f0f1; margin-bottom: 20px; border-radius: 4px;">
		<div style="font-size: 48px; font-weight: bold; color: #2271b1;">
			<?php echo esc_html($info->thuisscore); ?> - <?php echo esc_html($info->uitscore); ?>
		</div>
	</div>
<?php endif; ?>

<!-- Match Information Table -->
<table class="sportlink-detail-table">
	<thead>
		<tr>
			<th colspan="2">Wedstrijdinformatie</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!empty($info->wedstrijdnummer)): ?>
			<tr>
				<td class="sportlink-detail-label">Wedstrijdnummer</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->wedstrijdnummer); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->wedstrijddatumopgemaakt)): ?>
			<tr>
				<td class="sportlink-detail-label">Datum</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->wedstrijddatumopgemaakt); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->aanvangstijdopgemaakt)): ?>
			<tr>
				<td class="sportlink-detail-label">Aanvangstijd</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->aanvangstijdopgemaakt); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->competitietype)): ?>
			<tr>
				<td class="sportlink-detail-label">Competitie</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->competitietype); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->klasse)): ?>
			<tr>
				<td class="sportlink-detail-label">Klasse</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->klasse); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->poule)): ?>
			<tr>
				<td class="sportlink-detail-label">Poule</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->poule); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->veldnaam)): ?>
			<tr>
				<td class="sportlink-detail-label">Veld</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->veldnaam); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->veldlocatie)): ?>
			<tr>
				<td class="sportlink-detail-label">Locatie</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->veldlocatie); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->vertrektijd)): ?>
			<tr>
				<td class="sportlink-detail-label">Vertrektijd</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->vertrektijd); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->rijder)): ?>
			<tr>
				<td class="sportlink-detail-label">Rijder</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->rijder); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->wedstrijdtype)): ?>
			<tr>
				<td class="sportlink-detail-label">Wedstrijdtype</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->wedstrijdtype); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->categorie)): ?>
			<tr>
				<td class="sportlink-detail-label">Categorie</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->categorie); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->speltype)): ?>
			<tr>
				<td class="sportlink-detail-label">Speltype</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->speltype); ?></td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->duur)): ?>
			<tr>
				<td class="sportlink-detail-label">Duur</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->duur); ?> minuten</td>
			</tr>
		<?php endif; ?>

		<?php if (!empty($info->opmerkingen)): ?>
			<tr>
				<td class="sportlink-detail-label">Opmerkingen</td>
				<td class="sportlink-detail-value"><?php echo esc_html($info->opmerkingen); ?></td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>

<?php if ($has_result): ?>
	<!-- Score Details Table -->
	<table class="sportlink-detail-table">
		<thead>
			<tr>
				<th colspan="3">Uitslag details</th>
			</tr>
			<tr>
				<th></th>
				<th><?php echo esc_html($info->thuisteam); ?></th>
				<th><?php echo esc_html($info->uitteam); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($info->{'thuisscore-regulier'}) || !empty($info->{'uitscore-regulier'})): ?>
				<tr>
					<td class="sportlink-detail-label">Reguliere tijd</td>
					<td class="sportlink-detail-value"><?php echo esc_html($info->{'thuisscore-regulier'}); ?></td>
					<td class="sportlink-detail-value"><?php echo esc_html($info->{'uitscore-regulier'}); ?></td>
				</tr>
			<?php endif; ?>

			<?php if (!empty($info->{'thuisscore-nv'}) || !empty($info->{'uitscore-nv'})): ?>
				<tr>
					<td class="sportlink-detail-label">Na verlenging</td>
					<td class="sportlink-detail-value"><?php echo esc_html($info->{'thuisscore-nv'}); ?></td>
					<td class="sportlink-detail-value"><?php echo esc_html($info->{'uitscore-nv'}); ?></td>
				</tr>
			<?php endif; ?>

			<?php if (!empty($info->{'thuisscore-s'}) || !empty($info->{'uitscore-s'})): ?>
				<tr>
					<td class="sportlink-detail-label">Strafschoppen</td>
					<td class="sportlink-detail-value"><?php echo esc_html($info->{'thuisscore-s'}); ?></td>
					<td class="sportlink-detail-value"><?php echo esc_html($info->{'uitscore-s'}); ?></td>
				</tr>
			<?php endif; ?>

			<tr style="background: #f0f0f1;">
				<td class="sportlink-detail-label"><strong>Eindstand</strong></td>
				<td class="sportlink-detail-value"><strong><?php echo esc_html($info->thuisscore); ?></strong></td>
				<td class="sportlink-detail-value"><strong><?php echo esc_html($info->uitscore); ?></strong></td>
			</tr>
		</tbody>
	</table>

	<!-- Goals Table -->
	<?php if (!empty($doelpunten) && is_array($doelpunten)): ?>
		<table class="sportlink-detail-table">
			<thead>
				<tr>
					<th colspan="4">Doelpunten</th>
				</tr>
				<tr>
					<th style="width: 15%;">Minuut</th>
					<th style="width: 35%;">Speler</th>
					<th style="width: 30%;">Team</th>
					<th style="width: 20%;">Type</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($doelpunten as $goal): ?>
					<tr>
						<td><?php echo esc_html($goal->minuut); ?>'</td>
						<td><?php echo esc_html($goal->naam); ?></td>
						<td><?php echo $goal->team === 'THUIS' ? esc_html($info->thuisteam) : esc_html($info->uitteam); ?></td>
						<td><?php echo !empty($goal->soort) ? esc_html($goal->soort) : '-'; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<!-- Cards Table -->
	<?php if (!empty($kaarten) && is_array($kaarten)): ?>
		<table class="sportlink-detail-table">
			<thead>
				<tr>
					<th colspan="4">Kaarten</th>
				</tr>
				<tr>
					<th style="width: 15%;">Minuut</th>
					<th style="width: 35%;">Speler</th>
					<th style="width: 30%;">Team</th>
					<th style="width: 20%;">Kaart</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($kaarten as $card): ?>
					<tr style="background: <?php echo strtolower($card->kaartsoort) === 'geel' ? '#fff9e6' : '#ffe6e6'; ?>;">
						<td><?php echo esc_html($card->minuut); ?>'</td>
						<td><?php echo esc_html($card->naam); ?></td>
						<td><?php echo $card->team === 'THUIS' ? esc_html($info->thuisteam) : esc_html($info->uitteam); ?></td>
						<td>
							<span style="display: inline-block; padding: 2px 8px; border-radius: 3px; font-weight: bold; font-size: 11px; background: <?php echo strtolower($card->kaartsoort) === 'geel' ? '#ffd700' : '#dc3545'; ?>; color: <?php echo strtolower($card->kaartsoort) === 'geel' ? '#333' : 'white'; ?>;">
								<?php echo esc_html($card->kaartsoort); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<!-- Substitutions Table -->
	<?php if (!empty($wissels) && is_array($wissels)): ?>
		<table class="sportlink-detail-table">
			<thead>
				<tr>
					<th colspan="4">Wissels</th>
				</tr>
				<tr>
					<th style="width: 15%;">Minuut</th>
					<th style="width: 30%;">Uit</th>
					<th style="width: 30%;">In</th>
					<th style="width: 25%;">Team</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($wissels as $wissel): ?>
					<tr>
						<td><?php echo esc_html($wissel->minuut); ?>'</td>
						<td><?php echo esc_html($wissel->uit); ?></td>
						<td><?php echo esc_html($wissel->in); ?></td>
						<td><?php echo $wissel->team === 'THUIS' ? esc_html($info->thuisteam) : esc_html($info->uitteam); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
<?php endif; ?>

<!-- Historical Results -->
<?php if (!empty($history) && is_array($history) && count($history) > 0): ?>
	<table class="sportlink-detail-table">
		<thead>
			<tr>
				<th colspan="4">Onderlinge historie</th>
			</tr>
			<tr>
				<th style="width: 20%;">Datum</th>
				<th style="width: 30%;">Thuis</th>
				<th style="width: 30%;">Uit</th>
				<th style="width: 20%;">Uitslag</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($history as $match): ?>
				<tr>
					<td><?php echo esc_html(date_i18n('d-m-Y', strtotime($match->wedstrijddatum))); ?></td>
					<td><?php echo esc_html($match->thuisteam); ?></td>
					<td><?php echo esc_html($match->uitteam); ?></td>
					<td><strong><?php echo esc_html($match->uitslag); ?></strong></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<!-- Poule Standing -->
<?php if (!empty($poule) && is_array($poule) && count($poule) > 0): ?>
	<table class="sportlink-detail-table">
		<thead>
			<tr>
				<th colspan="8">Stand in de poule</th>
			</tr>
			<tr>
				<th style="width: 8%;">Pos</th>
				<th style="width: 30%;">Team</th>
				<th style="width: 10%;">Gespeeld</th>
				<th style="width: 8%;">W</th>
				<th style="width: 8%;">G</th>
				<th style="width: 8%;">V</th>
				<th style="width: 13%;">Doelsaldo</th>
				<th style="width: 15%;">Punten</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($poule as $team): ?>
				<tr style="<?php echo $team->eigenteam ? 'background: #e5f5fa; font-weight: 600;' : ''; ?>">
					<td><?php echo esc_html($team->positie); ?></td>
					<td><?php echo esc_html($team->teamnaam); ?></td>
					<td><?php echo esc_html($team->gespeeldewedstrijden); ?></td>
					<td><?php echo esc_html($team->gewonnen); ?></td>
					<td><?php echo esc_html($team->gelijk); ?></td>
					<td><?php echo esc_html($team->verloren); ?></td>
					<td><?php echo esc_html($team->doelsaldo); ?></td>
					<td><strong><?php echo esc_html($team->punten); ?></strong></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
