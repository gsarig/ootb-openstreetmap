// noinspection JSUnresolvedVariable
const {__, _x} = wp.i18n;
// noinspection JSUnresolvedVariable
const {Fragment} = wp.element;
// noinspection JSUnresolvedVariable
const {RangeControl, ToggleControl} = wp.components;

export default function BehaviorControls({props}) {
    const {
        attributes: {
            minZoom,
            maxZoom,
            dragging,
            touchZoom,
            doubleClickZoom,
            scrollWheelZoom,
        },
        setAttributes,
    } = props;

    // noinspection JSXNamespaceValidation
    return (
        <Fragment>
            <ToggleControl
                label={__('Map dragging', 'ootb-openstreetmap')}
                checked={!!dragging}
                onChange={() => setAttributes({dragging: !dragging})}
                help={!!dragging ? _x('Enabled.', 'Map dragging setting', 'ootb-openstreetmap') : _x('Disabled.', 'Map dragging setting', 'ootb-openstreetmap')}
            />
            <ToggleControl
                label={__('Touch zoom', 'ootb-openstreetmap')}
                checked={!!touchZoom}
                onChange={() => setAttributes({touchZoom: !touchZoom})}
                help={!!touchZoom ? _x('Enabled.', 'Touch zoom setting', 'ootb-openstreetmap') : _x('Disabled.', 'Touch zoom setting', 'ootb-openstreetmap')}
            />
            <ToggleControl
                label={__('Double-click zoom', 'ootb-openstreetmap')}
                checked={!!doubleClickZoom}
                onChange={() => setAttributes({doubleClickZoom: !doubleClickZoom})}
                help={!!doubleClickZoom ? _x('Enabled.', 'Double-click zoom setting', 'ootb-openstreetmap') : _x('Disabled.', 'Double-click zoom setting', 'ootb-openstreetmap')}
            />
            <ToggleControl
                label={__('Scroll Wheel zoom', 'ootb-openstreetmap')}
                checked={!!scrollWheelZoom}
                onChange={() => setAttributes({scrollWheelZoom: !scrollWheelZoom})}
                help={!!scrollWheelZoom ? _x('Enabled.', 'Scroll Wheel zoom setting', 'ootb-openstreetmap') : _x('Disabled.', 'Scroll Wheel zoom setting', 'ootb-openstreetmap')}
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
        </Fragment>
    );
}
