<style>
	.sportlink-admin-teams {
		margin-top: 20px;
	}

	.sportlink-admin-teams th {
		text-align: left;
		padding: 10px;
		background: #f0f0f1;
		font-weight: 600;
	}

	.sportlink-admin-teams td {
		padding: 10px;
		border-bottom: 1px solid #e0e0e0;
	}

	.sportlink-poule-link {
		color: #2271b1;
		text-decoration: none;
		cursor: pointer;
		font-weight: 500;
	}

	.sportlink-poule-link:hover {
		color: #135e96;
		text-decoration: underline;
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
</style>

<table class="form-table sportlink-admin-teams">
	<thead>
		<tr valign="top">
			<th>Team</th>
			<th>Teamcode</th>
			<th>Poules</th>
			<th>Leeftijdscategorie</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($data->teams as $team) {
		?>
			<tr valign="top">
				<td><?php echo esc_html($data->clubInfo->gegevens->clubnaam . " " . $team->teamnaam); ?></td>
				<td><?php echo esc_html($team->teamcode); ?></td>
				<td>
					<?php
					// Split poules by <br> tag
					$poulesArray = explode('<br>', $team->poules);
					$poulesArray = array_filter($poulesArray); // Remove empty elements

					foreach ($poulesArray as $index => $pouleText) {
						// Extract poulecode from text like "12345 (Competitie naam)"
						if (preg_match('/^(\d+)\s*\((.+)\)$/', trim($pouleText), $matches)) {
							$poulecode = $matches[1];
							$pouleName = $matches[2];
					?>
							<a href="#" class="sportlink-poule-link" data-poulecode="<?php echo esc_attr($poulecode); ?>">
								<?php echo esc_html($poulecode . ' (' . $pouleName . ')'); ?>
							</a>
					<?php
							// Add <br> between multiple poules
							if ($index < count($poulesArray) - 1) {
								echo '<br>';
							}
						} else {
							// Fallback if pattern doesn't match
							echo esc_html($pouleText);
							if ($index < count($poulesArray) - 1) {
								echo '<br>';
							}
						}
					}
					?>
				</td>
				<td><?php echo esc_html($team->leeftijdscategorie); ?></td>
			</tr>
		<?php
		}
		?>
	</tbody>
</table>

<!-- Modal -->
<div id="sportlinkPouleModal" class="sportlink-modal">
	<div class="sportlink-modal-content">
		<div class="sportlink-modal-header">
			<span class="sportlink-modal-close">&times;</span>
			<h2 id="sportlinkPouleModalTitle">Poulestand</h2>
		</div>
		<div class="sportlink-modal-body" id="sportlinkPouleModalBody">
			<div class="sportlink-modal-loading">
				<p>Poulestand laden...</p>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function($) {
		var modal = $('#sportlinkPouleModal');
		var modalBody = $('#sportlinkPouleModalBody');
		var modalTitle = $('#sportlinkPouleModalTitle');

		// Close button
		$('.sportlink-modal-close').on('click', function() {
			modal.hide();
		});

		// Click outside modal
		$(window).on('click', function(event) {
			if (event.target.id === 'sportlinkPouleModal') {
				modal.hide();
			}
		});

		// Close on Escape key
		$(document).on('keydown', function(event) {
			if (event.key === 'Escape' && modal.is(':visible')) {
				modal.hide();
			}
		});

		// Poule link click
		$('.sportlink-poule-link').on('click', function(e) {
			e.preventDefault();
			var poulecode = $(this).data('poulecode');

			// Show modal with loading state
			modalBody.html('<div class="sportlink-modal-loading"><p>Poulestand laden...</p></div>');
			modal.show();

			// Load poule details via AJAX
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'sportlink_get_poule_details',
					poulecode: poulecode,
					nonce: '<?php echo wp_create_nonce('sportlink_poule_details'); ?>'
				},
				success: function(response) {
					if (response.success) {
						modalTitle.text(response.data.title);
						modalBody.html(response.data.html);
					} else {
						modalBody.html('<div class="notice notice-error"><p>Fout bij het laden van poulestand.</p></div>');
					}
				},
				error: function() {
					modalBody.html('<div class="notice notice-error"><p>Fout bij het laden van poulestand.</p></div>');
				}
			});
		});
	});
</script>
