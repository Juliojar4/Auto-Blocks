import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl, RangeControl } from '@wordpress/components';

registerBlockType('sage/paragraph', {
    edit: ({ attributes, setAttributes }) => {
        const { content, fontClass, textColor, marginBottom, fontFamily, enableAnimation, animationDelay, animationType } = attributes;
 
        
        const fontOptions = [
            { label: 'Paragraph Normal (1.25rem)', value: 'paragraph-normal' },
            { label: 'Paragraph Small (1rem)', value: 'paragraph-small' }
        ];
        
        const colorOptions = [
            { label: 'Black', value: 'black' },
            { label: 'White', value: 'white' },
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
                            help="Choose the font style for this paragraph"
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
                            help={enableAnimation ? 'Fade-up animation enabled' : 'No animation'}
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
                                    label="Animation Delay (ms)"
                                    value={animationDelay}
                                    onChange={(value) => setAttributes({ animationDelay: value })}
                                    min={0}
                                    max={2000}
                                    step={100}
                                    help={`Delay: ${animationDelay}ms before animation starts`}
                                />
                            </>
                        )}
                    </PanelBody>
                </InspectorControls>
                
                <div className="">
                     <RichText
                        tagName="p"
                        className={`${fontClass} ${textColor === 'white' ? 'text-white' : 'text-black'} ${marginBottom} font-${fontFamily}`}
                        value={content}
                        onChange={(newContent) => setAttributes({ content: newContent })}
                        placeholder="Enter your content..."
                    />
                </div>
            </>
        );
    },
    
    save: () => null // Server-side rendering
});