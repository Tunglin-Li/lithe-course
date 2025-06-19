import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl, RangeControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";

export default function Edit({ attributes, setAttributes }) {
  const { metaType, listStyle, columns } = attributes;

  const metaTypeOptions = [
    { label: __("Features", "lithe-course"), value: "features" },
    { label: __("Prerequisites", "lithe-course"), value: "prerequisites" },
    { label: __("What You Will Learn", "lithe-course"), value: "learnings" },
    { label: __("Who This Is For", "lithe-course"), value: "suitableFor" },
  ];

  const listStyleOptions = [
    { label: __("None", "lithe-course"), value: "none" },
    { label: __("Disc", "lithe-course"), value: "disc" },
    { label: __("Circle", "lithe-course"), value: "circle" },
    { label: __("Square", "lithe-course"), value: "square" },
    { label: __("Decimal", "lithe-course"), value: "decimal" },
    {
      label: __("Decimal Leading Zero", "lithe-course"),
      value: "decimal-leading-zero",
    },
    { label: __("Lower Alpha", "lithe-course"), value: "lower-alpha" },
    { label: __("Lower Roman", "lithe-course"), value: "lower-roman" },
    { label: __("Upper Alpha", "lithe-course"), value: "upper-alpha" },
    { label: __("Upper Roman", "lithe-course"), value: "upper-roman" },
  ];

  // Determine sample data based on the selected meta type
  const getSampleItems = () => {
    switch (metaType) {
      case "features":
        return [
          __("Sample feature 1", "lithe-course"),
          __("Sample feature 2", "lithe-course"),
          __("Sample feature 3", "lithe-course"),
        ];
      case "prerequisites":
        return [
          __("Sample prerequisite 1", "lithe-course"),
          __("Sample prerequisite 2", "lithe-course"),
        ];
      case "learnings":
        return [
          __("Sample learning objective 1", "lithe-course"),
          __("Sample learning objective 2", "lithe-course"),
          __("Sample learning objective 3", "lithe-course"),
          __("Sample learning objective 4", "lithe-course"),
        ];
      case "suitableFor":
        return [
          __("Sample audience 1", "lithe-course"),
          __("Sample audience 2", "lithe-course"),
        ];
      default:
        return [__("Sample item", "lithe-course")];
    }
  };

  // Get CSS classes based on meta type
  const getMetaConfig = () => {
    const configs = {
      features: {
        cssClass: "features-list",
        itemClass: "feature-item",
        title: __("Course Features", "lithe-course"),
      },
      prerequisites: {
        cssClass: "prerequisites-list",
        itemClass: "prerequisite-item",
        title: __("Course Prerequisites", "lithe-course"),
      },
      learnings: {
        cssClass: "learnings-list",
        itemClass: "learning-item",
        title: __("What You Will Learn", "lithe-course"),
      },
      suitableFor: {
        cssClass: "suitable-list",
        itemClass: "suitable-item",
        title: __("Who This Course Is For", "lithe-course"),
      },
    };
    return configs[metaType] || configs.features;
  };

  const config = getMetaConfig();
  const sampleItems = getSampleItems();

  // Calculate column class (only for learnings)
  const columnClass =
    metaType === "learnings" && columns === 2 ? "two-columns" : "one-column";

  // Pass listStyle as an extra class
  const blockProps = useBlockProps({
    className: `list-style-${listStyle} ${columnClass}`,
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__("Metadata Settings", "lithe-course")}>
          <SelectControl
            label={__("Metadata Type", "lithe-course")}
            value={metaType}
            options={metaTypeOptions}
            onChange={(value) => setAttributes({ metaType: value })}
          />

          <SelectControl
            label={__("List Style", "lithe-course")}
            value={listStyle}
            options={listStyleOptions}
            onChange={(value) => setAttributes({ listStyle: value })}
          />

          {metaType === "learnings" && (
            <RangeControl
              label={__("Columns", "lithe-course")}
              value={columns}
              onChange={(value) => setAttributes({ columns: value })}
              min={1}
              max={2}
            />
          )}
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <ul className={config.cssClass}>
          {sampleItems.map((item, index) => (
            <li key={index} className={config.itemClass}>
              {item}
            </li>
          ))}
        </ul>
      </div>
    </>
  );
}
