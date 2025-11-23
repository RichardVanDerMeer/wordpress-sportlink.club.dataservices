<style>
	.sportlink-admin-results {
		margin-top: 20px;
	}

	.sportlink-admin-results th {
		text-align: left;
		padding: 10px;
		background: #f0f0f1;
		font-weight: 600;
	}

	.sportlink-admin-results td {
		padding: 10px;
		border-bottom: 1px solid #e0e0e0;
	}

	.sportlink-match-link {
		color: #2271b1;
		text-decoration: none;
		cursor: pointer;
		font-weight: 500;
	}

	.sportlink-match-link:hover {
		color: #135e96;
		text-decoration: underline;
	}

	.sportlink-match-number {
		color: #646970;
	}

	/* Modal styles */
	.sportlink-modal {
		display: none;
		position: fixed;
		z-index: 100000;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		overflow: auto;
		background-color: rgba(0, 0, 0, 0.6);
	}

	.sportlink-modal-content {
		background-color: #fefefe;
		margin: 2% auto;
		padding: 0;
		border: 1px solid #888;
		width: 90%;
		max-width: 1200px;
		max-height: 90vh;
		overflow-y: auto;
		border-radius: 4px;
		box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	}

	.sportlink-modal-header {
		padding: 20px;
		background-color: #2271b1;
		color: white;
		border-radius: 4px 4px 0 0;
	}

	.sportlink-modal-header h2 {
		margin: 0;
		color: white;
	}

	.sportlink-modal-close {
		color: white;
		float: right;
		font-size: 28px;
		font-weight: bold;
		cursor: pointer;
		line-height: 20px;
	}

	.sportlink-modal-close:hover,
	.sportlink-modal-close:focus {
		color: #f0f0f1;
	}

	.sportlink-modal-body {
		padding: 20px;
	}

	.sportlink-modal-loading {
		text-align: center;
		padding: 40px;
	}

	.sportlink-detail-table {
		width: 100%;
		border-collapse: collapse;
		margin-bottom: 20px;
		background: white;
	}

	.sportlink-detail-table th {
		background: #f0f0f1;
		padding: 12px;
		text-align: left;
		font-weight: 600;
		border-bottom: 2px solid #c3c4c7;
	}

	.sportlink-detail-table td {
		padding: 10px 12px;
		border-bottom: 1px solid #e0e0e0;
	}

	.sportlink-detail-label {
		font-weight: 600;
		color: #1d2327;
		width: 30%;
	}

	.sportlink-detail-value {
		color: #3c434a;
	}

	.sportlink-date-header {
		background: #2271b1;
		color: white;
		font-weight: 600;
		padding: 12px 10px;
	}

	.sportlink-date-header td {
		border-bottom: none;
	}
</style>

<table class="form-table sportlink-admin-results">
	<thead>
		<tr valign="top">
			<th>Datum</th>
			<th>Tijd</th>
			<th>Wedstrijd</th>
			<th>Uitslag</th>
			<th>Wedstrijdnummer</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$currentDate = null;
		foreach ($data->fixtures as $fixture) {
			$fixtureDate = date('Y-m-d', strtotime($fixture->wedstrijddatum));

			// Show date header if date changed
			if ($currentDate !== $fixtureDate) {
				$currentDate = $fixtureDate;
				$dateFormatted = date_i18n('l j F Y', strtotime($fixture->wedstrijddatum));
		?>
				<tr class="sportlink-date-header">
					<td colspan="5"><?php echo esc_html($dateFormatted); ?></td>
				</tr>
			<?php
			}
			?>
			<tr valign="top">
				<td>
					<?php echo date_i18n('d M', strtotime($fixture->wedstrijddatum)); ?>
				</td>
				<td><?php echo $fixture->aanvangstijd; ?></td>
				<td>
					<a href="#" class="sportlink-match-link" data-wedstrijdcode="<?php echo esc_attr($fixture->wedstrijdcode); ?>">
						<?php echo esc_html($fixture->wedstrijd); ?>
					</a>
				</td>
				<td>
					<?php echo !empty($fixture->uitslag) ? esc_html($fixture->uitslag) : '-'; ?>
				</td>
				<td>
					<a href="#" class="sportlink-match-link sportlink-match-number" data-wedstrijdcode="<?php echo esc_attr($fixture->wedstrijdcode); ?>">
						<?php echo esc_html($fixture->wedstrijdnummer); ?>
					</a>
				</td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>

<!-- Modal -->
<div id="sportlinkMatchModal" class="sportlink-modal">
	<div class="sportlink-modal-content">
		<div class="sportlink-modal-header">
			<span class="sportlink-modal-close">&times;</span>
			<h2 id="sportlinkModalTitle">Wedstrijddetails</h2>
		</div>
		<div class="sportlink-modal-body" id="sportlinkModalBody">
			<div class="sportlink-modal-loading">
				<p>Wedstrijddetails laden...</p>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
		var modal = $('#sportlinkMatchModal');
		var modalBody = $('#sportlinkModalBody');
		var modalTitle = $('#sportlinkModalTitle');

		// Close button
		$('.sportlink-modal-close').on('click', function() {
			modal.hide();
		});

		// Click outside modal
		$(window).on('click', function(event) {
			if (event.target.id === 'sportlinkMatchModal') {
				modal.hide();
			}
		});

		// Close on Escape key
		$(document).on('keydown', function(event) {
			if (event.key === 'Escape' && modal.is(':visible')) {
				modal.hide();
			}
		});

		// Match link click
		$('.sportlink-match-link').on('click', function(e) {
			e.preventDefault();
			var wedstrijdcode = $(this).data('wedstrijdcode');

			// Show modal with loading state
			modalBody.html('<div class="sportlink-modal-loading"><p>Wedstrijddetails laden...</p></div>');
			modal.show();

			// Load match details via AJAX
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'sportlink_get_match_details',
					wedstrijdcode: wedstrijdcode,
					nonce: '<?php echo wp_create_nonce('sportlink_match_details'); ?>'
				},
				success: function(response) {
					if (response.success) {
						modalTitle.text(response.data.title);
						modalBody.html(response.data.html);
					} else {
						modalBody.html('<div class="notice notice-error"><p>Fout bij het laden van wedstrijddetails.</p></div>');
					}
				},
				error: function() {
					modalBody.html('<div class="notice notice-error"><p>Fout bij het laden van wedstrijddetails.</p></div>');
				}
			});
		});
	});
</script>
