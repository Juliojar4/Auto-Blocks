import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { 
    InnerBlocks, 
    useBlockProps, 
    InspectorControls
} from '@wordpress/block-editor';
import { 
    PanelBody, 
    ToggleControl,
    SelectControl,
    RangeControl
} from '@wordpress/components';

registerBlockType('sage/container', {
    edit: ({ attributes, setAttributes }) => {
        const { addBottomBorder, backgroundColor, useGrid, paddingTop, paddingBottom, fullWidth } = attributes;

        const blockProps = useBlockProps();
        
        // Background color options
        const backgroundColors = [
            { label: 'Transparent', value: 'transparent' },
            { label: 'White', value: 'white' },
            { label: 'Black', value: 'black' },
            { label: 'Gray 50', value: 'gray-50' },
            { label: 'Gray 100', value: 'gray-100' },
            { label: 'Gray 200', value: 'gray-200' },
            { label: 'Purple 50', value: 'purple-50' },
            { label: 'Purple 100', value: 'purple-100' },
            { label: 'Blue 50', value: 'blue-50' },
            { label: 'Blue 100', value: 'blue-100' },
        ];

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Container Settings" initialOpen={true}>
                        <SelectControl
                            label="Background Color"
                            value={backgroundColor}
                            options={backgroundColors}
                            onChange={(value) => setAttributes({ backgroundColor: value })}
                            help="Choose a background color for the container"
                        />
                        
                        <ToggleControl
                            label="Use 2 Column Grid (1fr 1fr)"
                            help={useGrid ? 'Content will be arranged in 2 equal columns' : 'Content will stack vertically'}
                            checked={useGrid}
                            onChange={(value) => setAttributes({ useGrid: value })}
                        />
                        
                        <ToggleControl
                            label="Add Bottom Border"
                            checked={addBottomBorder}
                            onChange={(value) => setAttributes({ addBottomBorder: value })}
                        />
                        
                        <ToggleControl
                            label="Full Width"
                            help={fullWidth ? 'Container will span full viewport width' : 'Container will use max-width constraint'}
                            checked={fullWidth}
                            onChange={(value) => setAttributes({ fullWidth: value })}
                        />
                    </PanelBody>
                    
                    <PanelBody title="Spacing" initialOpen={false}>
                        <RangeControl
                            label="Padding Top"
                            value={parseInt(paddingTop)}
                            onChange={(value) => setAttributes({ paddingTop: value.toString() })}
                            min={0}
                            max={200}
                            step={10}
                            help={`Top padding: ${paddingTop}px`}
                        />
                        
                        <RangeControl
                            label="Padding Bottom"
                            value={parseInt(paddingBottom)}
                            onChange={(value) => setAttributes({ paddingBottom: value.toString() })}
                            min={0}
                            max={200}
                            step={10}
                            help={`Bottom padding: ${paddingBottom}px`}
                        />
                    </PanelBody>
                </InspectorControls>
                <div {...blockProps} className={`container-block-editor border-2 border-blue-200 border-dashed heading-block-editor bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 mb-4`}>
                    <div className={`py-5`}>
                        <div className="mb-4 space-y-2 text-sm text-gray-600">
                            <p className="font-bold">Container Settings:</p>
                            <p>Background: <span className="px-2 py-1 text-xs bg-gray-100 rounded">{backgroundColor}</span></p>
                            <p>Layout: <span className="px-2 py-1 text-xs bg-gray-100 rounded">{useGrid ? '2 Columns Grid' : 'Single Column'}</span></p>
                            <p>Width: <span className="px-2 py-1 text-xs bg-gray-100 rounded">{fullWidth ? 'Full Width' : 'Contained'}</span></p>
                            <p>Border: <span className="px-2 py-1 text-xs bg-gray-100 rounded">{addBottomBorder ? 'Yes' : 'No'}</span></p>
                            <p>Padding: <span className="px-2 py-1 text-xs bg-gray-100 rounded">Top: {paddingTop}px / Bottom: {paddingBottom}px</span></p>
                        </div>
                        <div className={`${useGrid ? 'grid-container' : ''}`}>
                            <InnerBlocks 
                                placeholder={__('Add any blocks here...', 'sage')}
                                
                            />
                        </div>
                    </div>
                </div>
            </>
        );
    },
    
save: () => {
    const blockProps = useBlockProps.save({ className: 'container-block' });
    return (
      <div {...blockProps}>
        <InnerBlocks.Content />
      </div>
    );
  },
});