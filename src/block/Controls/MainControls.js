import ProviderControl from "./ProviderControl";
import getIcon from "../Helpers/getIcon";

// noinspection JSUnresolvedVariable
const {__} = wp.i18n;
// noinspection JSUnresolvedVariable
const {Fragment} = wp.element;
// noinspection JSUnresolvedVariable
const {RangeControl, Button} = wp.components;
// noinspection JSUnresolvedVariable
const {MediaUpload, MediaUploadCheck} = wp.blockEditor;

export default function MainControls({props}) {
	const {
		attributes: {
			mapHeight,
			defaultIcon,
			zoom,
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
		<Fragment>
			<ProviderControl props={props}/>
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
		</Fragment>
	);
}
