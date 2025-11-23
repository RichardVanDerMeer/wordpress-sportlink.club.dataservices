import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, ToggleControl, Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const {
		teamcode,
		aantalregels,
		aantaldagen,
		weekoffset,
		eigenwedstrijden,
		thuis,
		uit,
		spelsoort,
		competitiesoort,
		dagsoort,
		leeftijdscategorie,
		sorteervolgorde,
		gebruiklokaleteamgegevens,
		showTime,
		showDate,
		showLocation,
		showReferee,
		showField,
		showRemarks,
		showCompetition,
		showLogos,
		logoWidth,
		showVersus,
		groupByDate,
		fontSize,
		columnOrder = ["time", "home", "versus", "away", "location", "field", "referee", "competition", "remarks"],
		displayStyle
	} = attributes;

	// State for teams
	const [teams, setTeams] = useState([]);
	const [isLoadingTeams, setIsLoadingTeams] = useState(true);

	// Fetch teams from REST API
	useEffect(() => {
		setIsLoadingTeams(true);
		apiFetch({ path: '/sportlink/v1/teams' })
			.then((data) => {
				console.log('Teams fetched:', data);
				setTeams(Array.isArray(data) ? data : []);
				setIsLoadingTeams(false);
			})
			.catch((error) => {
				console.error('Error fetching teams:', error);
				setTeams([]);
				setIsLoadingTeams(false);
			});
	}, []);

	// Build team options for dropdown
	const teamOptions = [
		{ label: __('Alle teams', 'sportlink'), value: '' }
	];

	if (teams && Array.isArray(teams)) {
		teams.forEach(team => {
			teamOptions.push({
				label: team.label || team.teamnaam || '',
				value: team.value || team.teamcode || ''
			});
		});
	}

	// Column labels
	const columnLabels = {
		time: __('Tijd', 'sportlink'),
		home: __('Thuis', 'sportlink'),
		versus: __('VS', 'sportlink'),
		away: __('Uit', 'sportlink'),
		location: __('Locatie', 'sportlink'),
		field: __('Veld', 'sportlink'),
		referee: __('Scheidsrechter', 'sportlink'),
		competition: __('Competitie', 'sportlink'),
		remarks: __('Opmerking', 'sportlink')
	};

	// Move column up
	const moveColumnUp = (index) => {
		if (index === 0) return;
		const newOrder = [...columnOrder];
		[newOrder[index - 1], newOrder[index]] = [newOrder[index], newOrder[index - 1]];
		setAttributes({ columnOrder: newOrder });
	};

	// Move column down
	const moveColumnDown = (index) => {
		if (index === columnOrder.length - 1) return;
		const newOrder = [...columnOrder];
		[newOrder[index], newOrder[index + 1]] = [newOrder[index + 1], newOrder[index]];
		setAttributes({ columnOrder: newOrder });
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Algemene instellingen', 'sportlink')} initialOpen={true}>
					{isLoadingTeams ? (
						<div style={{ padding: '10px 0', textAlign: 'center' }}>
							<Spinner />
							<p>{__('Teams laden...', 'sportlink')}</p>
						</div>
					) : (
						<SelectControl
							label={__('Team', 'sportlink')}
							value={teamcode}
							options={teamOptions}
							onChange={(value) => setAttributes({ teamcode: value })}
							help={__('Selecteer een team of "Alle teams"', 'sportlink')}
						/>
					)}

					<RangeControl
						label={__('Aantal wedstrijden', 'sportlink')}
						value={aantalregels}
						onChange={(value) => setAttributes({ aantalregels: value })}
						min={1}
						max={100}
					/>

					<RangeControl
						label={__('Aantal dagen vooruit', 'sportlink')}
						value={aantaldagen}
						onChange={(value) => setAttributes({ aantaldagen: value })}
						min={1}
						max={365}
					/>

					<RangeControl
						label={__('Week offset', 'sportlink')}
						value={weekoffset}
						onChange={(value) => setAttributes({ weekoffset: value })}
						min={-52}
						max={52}
						help={__('0 = huidige week, 1 = volgende week, -1 = vorige week', 'sportlink')}
					/>

					<SelectControl
						label={__('Sorteervolgorde', 'sportlink')}
						value={sorteervolgorde}
						options={[
							{ label: __('Datum', 'sportlink'), value: 'datum' },
							{ label: __('Team', 'sportlink'), value: 'team' }
						]}
						onChange={(value) => setAttributes({ sorteervolgorde: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Filter opties', 'sportlink')} initialOpen={false}>
					<SelectControl
						label={__('Eigen wedstrijden', 'sportlink')}
						value={eigenwedstrijden}
						options={[
							{ label: __('Ja', 'sportlink'), value: 'JA' },
							{ label: __('Nee', 'sportlink'), value: 'NEE' }
						]}
						onChange={(value) => setAttributes({ eigenwedstrijden: value })}
					/>

					<SelectControl
						label={__('Thuiswedstrijden', 'sportlink')}
						value={thuis}
						options={[
							{ label: __('Ja', 'sportlink'), value: 'JA' },
							{ label: __('Nee', 'sportlink'), value: 'NEE' }
						]}
						onChange={(value) => setAttributes({ thuis: value })}
					/>

					<SelectControl
						label={__('Uitwedstrijden', 'sportlink')}
						value={uit}
						options={[
							{ label: __('Ja', 'sportlink'), value: 'JA' },
							{ label: __('Nee', 'sportlink'), value: 'NEE' }
						]}
						onChange={(value) => setAttributes({ uit: value })}
					/>

					<SelectControl
						label={__('Spelsoort', 'sportlink')}
						value={spelsoort}
						options={[
							{ label: __('Alles', 'sportlink'), value: 'ALLES' },
							{ label: __('Veld', 'sportlink'), value: 'VELD' },
							{ label: __('Zaal', 'sportlink'), value: 'ZAAL' }
						]}
						onChange={(value) => setAttributes({ spelsoort: value })}
					/>

					<SelectControl
						label={__('Competitiesoort', 'sportlink')}
						value={competitiesoort}
						options={[
							{ label: __('Alles', 'sportlink'), value: 'ALLES' },
							{ label: __('Regulier', 'sportlink'), value: 'REGULIER' },
							{ label: __('Beker', 'sportlink'), value: 'BEKER' }
						]}
						onChange={(value) => setAttributes({ competitiesoort: value })}
					/>

					<SelectControl
						label={__('Dag', 'sportlink')}
						value={dagsoort}
						options={[
							{ label: __('Alles', 'sportlink'), value: 'ALLES' },
							{ label: __('Zaterdag', 'sportlink'), value: 'ZATERDAG' },
							{ label: __('Zondag', 'sportlink'), value: 'ZONDAG' }
						]}
						onChange={(value) => setAttributes({ dagsoort: value })}
					/>

					<SelectControl
						label={__('Leeftijdscategorie', 'sportlink')}
						value={leeftijdscategorie}
						options={[
							{ label: __('Alles', 'sportlink'), value: 'ALLES' },
							{ label: __('Senioren', 'sportlink'), value: 'SENIOREN' },
							{ label: __('Junioren', 'sportlink'), value: 'JUNIOREN' }
						]}
						onChange={(value) => setAttributes({ leeftijdscategorie: value })}
					/>
				</PanelBody>

				<PanelBody title={__('Geavanceerde opties', 'sportlink')} initialOpen={false}>
					<SelectControl
						label={__('Gebruik lokale teamgegevens', 'sportlink')}
						value={gebruiklokaleteamgegevens}
						options={[
							{ label: __('Nee', 'sportlink'), value: 'NEE' },
							{ label: __('Ja', 'sportlink'), value: 'JA' }
						]}
						onChange={(value) => setAttributes({ gebruiklokaleteamgegevens: value })}
						help={__('Gebruik aangepaste teamnamen indien ingesteld', 'sportlink')}
					/>
				</PanelBody>

				<PanelBody title={__('Weergave opties', 'sportlink')} initialOpen={false}>
					<SelectControl
						label={__('Weergave stijl', 'sportlink')}
						value={displayStyle}
						options={[
							{ label: __('Tabel', 'sportlink'), value: 'table' },
							{ label: __('Lijst', 'sportlink'), value: 'list' },
							{ label: __('Kaarten', 'sportlink'), value: 'cards' }
						]}
						onChange={(value) => setAttributes({ displayStyle: value })}
					/>

					<ToggleControl
						label={__('Toon datum', 'sportlink')}
						checked={showDate}
						onChange={(value) => setAttributes({ showDate: value })}
					/>

					<ToggleControl
						label={__('Toon tijd', 'sportlink')}
						checked={showTime}
						onChange={(value) => setAttributes({ showTime: value })}
					/>

					<ToggleControl
						label={__('Toon locatie', 'sportlink')}
						checked={showLocation}
						onChange={(value) => setAttributes({ showLocation: value })}
					/>

					<ToggleControl
						label={__('Toon scheidsrechter', 'sportlink')}
						checked={showReferee}
						onChange={(value) => setAttributes({ showReferee: value })}
					/>

					<ToggleControl
						label={__('Toon veld', 'sportlink')}
						checked={showField}
						onChange={(value) => setAttributes({ showField: value })}
					/>

					<ToggleControl
						label={__('Toon opmerkingen', 'sportlink')}
						checked={showRemarks}
						onChange={(value) => setAttributes({ showRemarks: value })}
					/>

					<ToggleControl
						label={__('Toon competitie', 'sportlink')}
						checked={showCompetition}
						onChange={(value) => setAttributes({ showCompetition: value })}
					/>

					<ToggleControl
						label={__('Toon clublogo\'s', 'sportlink')}
						checked={showLogos}
						onChange={(value) => setAttributes({ showLogos: value })}
						help={__('Indien beschikbaar via API', 'sportlink')}
					/>

					{showLogos && (
						<RangeControl
							label={__('Logo breedte (px)', 'sportlink')}
							value={logoWidth}
							onChange={(value) => setAttributes({ logoWidth: value })}
							min={20}
							max={200}
							step={5}
						/>
					)}

					<ToggleControl
						label={__('Toon versus-streepje', 'sportlink')}
						checked={showVersus}
						onChange={(value) => setAttributes({ showVersus: value })}
					/>

					<SelectControl
						label={__('Lettergrootte', 'sportlink')}
						value={fontSize}
						options={[
							{ label: __('Klein', 'sportlink'), value: 'small' },
							{ label: __('Normaal', 'sportlink'), value: 'medium' },
							{ label: __('Groot', 'sportlink'), value: 'large' },
							{ label: __('Extra groot', 'sportlink'), value: 'x-large' }
						]}
						onChange={(value) => setAttributes({ fontSize: value })}
					/>

					{!teamcode && (
						<ToggleControl
							label={__('Groepeer per datum', 'sportlink')}
							checked={groupByDate}
							onChange={(value) => setAttributes({ groupByDate: value })}
							help={__('Alleen beschikbaar bij "Alle teams"', 'sportlink')}
						/>
					)}
				</PanelBody>

				<PanelBody title={__('Kolom volgorde', 'sportlink')} initialOpen={false}>
					<p style={{ marginBottom: '10px', fontSize: '12px', color: '#757575' }}>
						{__('Versleep kolommen om de volgorde aan te passen', 'sportlink')}
					</p>
					{columnOrder.map((column, index) => (
						<div key={column} style={{
							display: 'flex',
							alignItems: 'center',
							marginBottom: '8px',
							padding: '8px',
							backgroundColor: '#f5f5f5',
							borderRadius: '4px'
						}}>
							<span style={{ flex: 1, fontWeight: '500' }}>
								{columnLabels[column] || column}
							</span>
							<Button
								isSmall
								disabled={index === 0}
								onClick={() => moveColumnUp(index)}
								icon="arrow-up-alt2"
								label={__('Naar boven', 'sportlink')}
							/>
							<Button
								isSmall
								disabled={index === columnOrder.length - 1}
								onClick={() => moveColumnDown(index)}
								icon="arrow-down-alt2"
								label={__('Naar beneden', 'sportlink')}
								style={{ marginLeft: '4px' }}
							/>
						</div>
					))}
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps()}>
				<div className="sportlink-block-preview">
					<div className="sportlink-block-icon">
						<span className="dashicons dashicons-calendar-alt"></span>
					</div>
					<div className="sportlink-block-content">
						<h3>{__('Sportlink Programma', 'sportlink')}</h3>
						<p className="sportlink-block-description">
							{teamcode ? (
								(() => {
									const selectedTeam = teamOptions.find(t => t.value === teamcode);
									return __('Team: ', 'sportlink') + (selectedTeam?.label || teamcode);
								})()
							) : (
								__('Alle teams', 'sportlink')
							)}
							{' • '}
							{aantalregels} {__('wedstrijden', 'sportlink')}
							{' • '}
							{aantaldagen} {__('dagen vooruit', 'sportlink')}
						</p>
						<p className="sportlink-block-note">
							{__('Het programma wordt dynamisch geladen op de pagina', 'sportlink')}
						</p>
					</div>
				</div>
			</div>
		</>
	);
}
