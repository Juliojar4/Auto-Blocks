import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, RangeControl } from '@wordpress/components';

registerBlockType('sage/heading', {
    edit: ({ attributes, setAttributes }) => {
        const { content, fontClass, textColor, marginBottom, fontFamily, enableAnimation, animationDelay, animationType } = attributes;
        const blockProps = useBlockProps();
        
        const fontOptions = [
            { label: '4.5rem', value: 'h1-forza' },
            { label: '1.875rem', value: 'submead-medium-forza' }
        ];
        
        const colorOptions = [
            { label: 'Black', value: 'black' },
            { label: 'White', value: 'white' }
        ];
        
        const marginOptions = [
            { label: 'None', value: 'mb-0' },
            { label: 'Small (8px)', value: 'mb-2' },
            { label: 'Medium (16px)', value: 'mb-4' },
            { label: 'Large (24px)', value: 'mb-6' },
        ];
        
        const fontFamilyOptions = [
            { label: 'Forza', value: 'forza' },
            { label: 'Druk', value: 'druk' }
        ];
        
        const animationTypeOptions = [
            { label: 'Fade Up', value: 'fade-up' },
            { label: 'Fade Down', value: 'fade-down' },
            { label: 'Fade Left', value: 'fade-left' },
            { label: 'Fade Right', value: 'fade-right' },
            { label: 'Fade', value: 'fade' },
        ];
        
 

        return (
            <>
            <InspectorControls>
                <PanelBody title="Font Settings" initialOpen={true}>
                <SelectControl
                    label="Font Style"
                    value={fontClass}
                    options={fontOptions}
                    onChange={(newFontClass) => setAttributes({ fontClass: newFontClass })}
                    help="Choose the font style for this heading"
                />
                
                <SelectControl
                    label="Font Family"
                    value={fontFamily}
                    options={fontFamilyOptions}
                    onChange={(newFamily) => setAttributes({ fontFamily: newFamily })}
                    help="Choose the font family"
                />
                
                <SelectControl
                    label="Text Color"
                    value={textColor}
                    options={colorOptions}
                    onChange={(newColor) => setAttributes({ textColor: newColor })}
                    help="Choose the text color (black or white)"
                />
                
                <SelectControl
                    label="Margin Bottom"
                    value={marginBottom}
                    options={marginOptions}
                    onChange={(newMargin) => setAttributes({ marginBottom: newMargin })}
                    help="Choose the bottom spacing"
                />
                </PanelBody>
                
                <PanelBody title="Animation Settings" initialOpen={false}>
                <ToggleControl
                    label="Enable Fade Animation"
                    checked={enableAnimation}
                    onChange={(value) => setAttributes({ enableAnimation: value })}
                    help={enableAnimation ? 'Animation enabled for each line' : 'No animation'}
                />
                
                {enableAnimation && (
                    <>
                    <SelectControl
                        label="Animation Type"
                        value={animationType}
                        options={animationTypeOptions}
                        onChange={(value) => setAttributes({ animationType: value })}
                        help="Choose the animation effect"
                    />
                    
                    <RangeControl
                        label="Delay Between Lines (ms)"
                        value={animationDelay}
                        onChange={(value) => setAttributes({ animationDelay: value })}
                        min={0}
                        max={500}
                        step={50}
                        help={`Delay: ${animationDelay}ms between each line`}
                    />
                    </>
                )}
                </PanelBody>
            </InspectorControls>
            
            <div {...blockProps} >
                <h3 className="text-base color-[#575757] !font-sans font-bold mb-2">Heading</h3>
                <RichText
                tagName="h2"
                
                value={content}
                onChange={(newContent) => setAttributes({ content: newContent })}
                placeholder="Enter your heading..."
                />
            </div>
            </>
        );
    },
    
    save: () => null // Server-side rendering
});