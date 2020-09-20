import getIcon from "../Helpers/getIcon";
// noinspection JSUnresolvedVariable
const {__} = wp.i18n;
// noinspection JSUnresolvedVariable
const {PanelBody, RangeControl, Button, ToggleControl} = wp.components;
// noinspection JSUnresolvedVariable
const {MediaUpload, MediaUploadCheck, InspectorControls} = wp.blockEditor;

export default function Controls({props}) {
	const {
		attributes: {
			mapHeight,
			defaultIcon,
			zoom,
			minZoom,
			maxZoom,
			dragging,
			touchZoom,
			doubleClickZoom,
			scrollWheelZoom,
		},
		setAttributes,
	} = props;
	const setDefaultIcon = image => {
		setAttributes({
			defaultIcon: image
		});
	}

	const restoreDefaultIcon = () => {
		setAttributes({
			defaultIcon: null
		});
	}

	const icon = getIcon(props);

	// noinspection JSXNamespaceValidation
	return (
		<InspectorControls>
			<PanelBody
				title={__('Main settings', 'ootb-openstreetmap')}
				initialOpen={true}
			>
				<RangeControl
					label={__('Height (pixels)', 'ootb-openstreetmap')}
					value={mapHeight}
					onChange={
						(pixels) => {
							setAttributes({mapHeight: pixels})
						}
					}
					min={50}
					max={1000}
				/>
				<RangeControl
					label={__('Zoom', 'ootb-openstreetmap')}
					value={zoom}
					onChange={
						(value) => {
							setAttributes({zoom: value})
						}
					}
					min={0}
					max={18}
				/>
				<MediaUploadCheck>
					<div className="ootb-openstreetmap--icon">
						<img
							src={icon.iconUrl}
							alt={__('Map Marker', 'ootb-openstreetmap')}
						/>
						<div className="gmp-openstreetmap--buttons">
							<MediaUpload
								onSelect={setDefaultIcon}
								allowedTypes={['image']}
								value={defaultIcon}
								render={({open}) => (
									<Button
										onClick={open}
										isSecondary
									>
										{__('Change icon', 'ootb-openstreetmap')}
									</Button>
								)}
							/>
							{defaultIcon ?
								<Button
									onClick={restoreDefaultIcon}
									isDestructive
								>
									{__('Restore default', 'ootb-openstreetmap')}
								</Button>
								: null}

						</div>
					</div>
				</MediaUploadCheck>
			</PanelBody>
			<PanelBody
				title={__('Map behavior', 'ootb-openstreetmap')}
				initialOpen={false}
			>
				<ToggleControl
					label={__('Map dragging', 'ootb-openstreetmap')}
					checked={!!dragging}
					onChange={() => setAttributes({dragging: !dragging})}
					help={!!dragging ? __('Enabled.', 'ootb-openstreetmap') : __('Disabled.', 'ootb-openstreetmap')}
				/>
				<ToggleControl
					label={__('Touch zoom', 'ootb-openstreetmap')}
					checked={!!touchZoom}
					onChange={() => setAttributes({touchZoom: !touchZoom})}
					help={!!touchZoom ? __('Enabled.', 'ootb-openstreetmap') : __('Disabled.', 'ootb-openstreetmap')}
				/>
				<ToggleControl
					label={__('Double-click zoom', 'ootb-openstreetmap')}
					checked={!!doubleClickZoom}
					onChange={() => setAttributes({doubleClickZoom: !doubleClickZoom})}
					help={!!doubleClickZoom ? __('Enabled.', 'ootb-openstreetmap') : __('Disabled.', 'ootb-openstreetmap')}
				/>
				<ToggleControl
					label={__('Scroll Wheel zoom', 'ootb-openstreetmap')}
					checked={!!scrollWheelZoom}
					onChange={() => setAttributes({scrollWheelZoom: !scrollWheelZoom})}
					help={!!scrollWheelZoom ? __('Enabled.', 'ootb-openstreetmap') : __('Disabled.', 'ootb-openstreetmap')}
				/>
				<RangeControl
					label={__('Minimum Zoom', 'ootb-openstreetmap')}
					value={minZoom}
					onChange={
						(value) => {
							setAttributes({
								minZoom: value,
								maxZoom: maxZoom <= minZoom ? minZoom + 1 : maxZoom
							})
						}
					}
					min={0}
					max={18}
				/>
				<RangeControl
					label={__('Maximum Zoom', 'ootb-openstreetmap')}
					value={maxZoom}
					onChange={
						(value) => {
							setAttributes({
								maxZoom: value,
								minZoom: minZoom >= maxZoom ? maxZoom - 1 : minZoom
							})
						}
					}
					min={0}
					max={18}
					help={__('Tip: setting the same minimum and maximum zoom practically locks zoom at that level.', 'ootb-openstreetmap')}
				/>

			</PanelBody>
		</InspectorControls>
	);
}
