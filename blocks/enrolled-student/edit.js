import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, ToggleControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { Icon, people } from "@wordpress/icons";

export default function Edit({ attributes, setAttributes }) {
  const { textFormat, showIcon } = attributes;

  const blockProps = useBlockProps({
    className: "lithecourse-enrolled-student-count",
  });

  // Sample display for the editor
  const sampleCount = 42;
  const displayText = textFormat.replace("{count}", sampleCount);

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Display Settings", "lithe-course")}>
          <TextControl
            label={__("Text Format", "lithe-course")}
            value={textFormat}
            onChange={(value) => setAttributes({ textFormat: value })}
            help={__(
              "Use {count} as placeholder for the number of enrolled students",
              "lithe-course"
            )}
          />

          <ToggleControl
            label={__("Show Icon", "lithe-course")}
            checked={showIcon}
            onChange={(value) => setAttributes({ showIcon: value })}
          />
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="lithecourse-enrolled-student-display">
          {showIcon && (
            <Icon icon={people} className="lithecourse-enrolled-student-icon" />
          )}
          <span className="lithecourse-enrolled-student-text">
            {displayText}
          </span>
        </div>
      </div>
    </>
  );
}
