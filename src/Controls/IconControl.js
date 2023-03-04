// noinspection NpmUsedModulesInstalled,JSUnresolvedVariable

import getIcon from '../Helpers/getIcon';
import {__} from '@wordpress/i18n';
import {MediaUpload, MediaUploadCheck} from '@wordpress/block-editor';
import {Button} from '@wordpress/components';
import hasIconOverride from "../Helpers/hasIconOverride";

export default function IconControl({props, index}) {
	const {
		attributes: {
			markers,
		},
		setAttributes,
	} = props;

	const isInMarker = typeof index !== 'undefined';
	const updateIcon = (value) => {
		if (isInMarker) {
			let updatedMarkers = [...markers];
			updatedMarkers[index].icon = value;
			setAttributes({
				markers: updatedMarkers
			});
		} else {
			setAttributes({
				defaultIcon: value
			});
		}
	}

	const setDefaultIcon = image => {
		updateIcon(image);
	}
	const restoreDefaultIcon = () => {
		updateIcon(null);
	}

	const icon = getIcon(props, index);
	return (
		<MediaUploadCheck>
			<div className="ootb-openstreetmap--icon">
				{
					!isInMarker ?
						<img
							src={icon.iconUrl}
							alt={__('Map Marker', 'ootb-openstreetmap')}
						/>
						: null
				}
				<div className="gmp-openstreetmap--buttons">
					<MediaUpload
						onSelect={setDefaultIcon}
						allowedTypes={['image']}
						value={icon}
						render={({open}) => (
							<Button
								onClick={open}
								icon="format-image"
								label={__('Change the icon', 'ootb-openstreetmap')}
								isSecondary
							>
								{
									!isInMarker ?
										__('Change', 'ootb-openstreetmap')
										: null
								}
							</Button>
						)}
					/>
					{hasIconOverride(props, icon?.iconUrl, index) ?
						<Button
							onClick={restoreDefaultIcon}
							icon="image-rotate"
							label={__('Restore the marker', 'ootb-openstreetmap')}
							isDestructive
						>
							{
								!isInMarker ?
									__('Restore', 'ootb-openstreetmap')
									: null
							}
						</Button>
						: null}

				</div>
			</div>
		</MediaUploadCheck>
	);
}
