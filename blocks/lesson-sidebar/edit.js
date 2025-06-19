import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";
import {
  PanelBody,
  PanelRow,
  TabPanel,
  ColorPalette,
  RangeControl,
  Button,
} from "@wordpress/components";

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps({
    className: "lithe-course-modules-container",
  });
  const [isOpen, setIsOpen] = useState(true);
  const [activeTab, setActiveTab] = useState("title");

  // Get the border radius from styles if available
  const style = attributes.style || {};
  const borderRadius = style.border?.radius || attributes.borderRadius || "4px";
  const borderRadiusValue = parseInt(borderRadius, 10) || 4;

  // Apply custom styles based on attributes
  const moduleStyle = {
    borderRadius: borderRadius,
  };

  const moduleHeaderStyle = {
    backgroundColor: attributes.titleBackgroundColor,
    color: attributes.titleTextColor,
    borderRadius: `${borderRadius} ${borderRadius} 0 0`,
  };

  const moduleTitleStyle = {
    color: attributes.titleTextColor,
  };

  const lessonStyle = {
    color: attributes.lessonTextColor,
    backgroundColor: attributes.lessonBackgroundColor,
  };

  const toggleModule = () => {
    setIsOpen(!isOpen);
  };

  return (
    <>
      <InspectorControls>
        <PanelBody
          title={__("Lesson Sidebar Settings", "lithe-course")}
          initialOpen={true}
        >
          <TabPanel
            className="lithe-course-outline-tabs"
            activeClass="is-active"
            initialTabName={activeTab}
            onSelect={(tabName) => setActiveTab(tabName)}
            tabs={[
              {
                name: "title",
                title: __("Title", "lithe-course"),
                className: "title-tab",
              },
              {
                name: "lesson",
                title: __("Lesson", "lithe-course"),
                className: "lesson-tab",
              },
              {
                name: "border",
                title: __("Border", "lithe-course"),
                className: "border-tab",
              },
            ]}
          >
            {(tab) => {
              if (tab.name === "title") {
                return (
                  <>
                    <div className="components-panel__row">
                      <label>{__("Title Text Color", "lithe-course")}</label>
                    </div>
                    <ColorPalette
                      value={attributes.titleTextColor}
                      onChange={(color) =>
                        setAttributes({ titleTextColor: color })
                      }
                    />

                    <div className="components-panel__row">
                      <label>
                        {__("Title Background Color", "lithe-course")}
                      </label>
                    </div>
                    <ColorPalette
                      value={attributes.titleBackgroundColor}
                      onChange={(color) =>
                        setAttributes({ titleBackgroundColor: color })
                      }
                    />
                  </>
                );
              } else if (tab.name === "lesson") {
                return (
                  <>
                    <div className="components-panel__row">
                      <label>{__("Lesson Text Color", "lithe-course")}</label>
                    </div>
                    <ColorPalette
                      value={attributes.lessonTextColor}
                      onChange={(color) =>
                        setAttributes({ lessonTextColor: color })
                      }
                    />

                    <div className="components-panel__row">
                      <label>
                        {__("Lesson Background Color", "lithe-course")}
                      </label>
                    </div>
                    <ColorPalette
                      value={attributes.lessonBackgroundColor}
                      onChange={(color) =>
                        setAttributes({ lessonBackgroundColor: color })
                      }
                    />

                    <div className="components-panel__row">
                      <label>
                        {__("Current Lesson Color", "lithe-course")}
                      </label>
                    </div>
                    <ColorPalette
                      value={attributes.currentLessonColor}
                      onChange={(color) =>
                        setAttributes({ currentLessonColor: color })
                      }
                    />
                  </>
                );
              } else if (tab.name === "border") {
                return (
                  <>
                    <div className="components-panel__row">
                      <label>{__("Border Radius", "lithe-course")}</label>
                    </div>
                    <RangeControl
                      value={borderRadiusValue}
                      onChange={(value) => {
                        setAttributes({
                          style: {
                            ...style,
                            border: {
                              ...(style.border || {}),
                              radius: value + "px",
                            },
                          },
                        });
                      }}
                      min={0}
                      max={50}
                      step={1}
                      beforeIcon="square-alt"
                      afterIcon="button"
                    />
                    <Button
                      isSecondary
                      isSmall
                      onClick={() =>
                        setAttributes({
                          style: {
                            ...style,
                            border: {
                              ...(style.border || {}),
                              radius: "4px",
                            },
                          },
                        })
                      }
                    >
                      {__("Reset", "lithe-course")}
                    </Button>
                  </>
                );
              }
            }}
          </TabPanel>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="lithe-course-modules">
          <div className="lithe-module" data-id="preview" style={moduleStyle}>
            <div
              className="module-header"
              onClick={toggleModule}
              style={moduleHeaderStyle}
            >
              <h3 className="module-title" style={moduleTitleStyle}>
                Module Title
              </h3>
              <span
                className={`dashicons module-toggle ${
                  isOpen ? "dashicons-arrow-down" : "dashicons-arrow-right"
                }`}
                style={moduleTitleStyle}
              ></span>
            </div>
            <div className={`module-content ${isOpen ? "is-open" : ""}`}>
              <ul className="module-lessons">
                <li className="lesson-item">
                  <div className="lesson-content" style={lessonStyle}>
                    <label className="lesson-completion">
                      <input
                        type="checkbox"
                        className="lesson-completion-checkbox"
                        readOnly
                      />
                      <span
                        className="completion-status"
                        style={{
                          borderColor: attributes.lessonTextColor,
                          backgroundColor: "transparent",
                        }}
                      ></span>
                    </label>
                    <a href="#" style={{ color: attributes.lessonTextColor }}>
                      Lesson Item 1
                    </a>
                  </div>
                </li>
                <li className="lesson-item">
                  <div className="lesson-content" style={lessonStyle}>
                    <label className="lesson-completion">
                      <input
                        type="checkbox"
                        className="lesson-completion-checkbox"
                        readOnly
                      />
                      <span
                        className="completion-status"
                        style={{
                          borderColor: attributes.lessonTextColor,
                          backgroundColor: "transparent",
                        }}
                      ></span>
                    </label>
                    <a href="#" style={{ color: attributes.lessonTextColor }}>
                      Lesson Item 2
                    </a>
                  </div>
                </li>
                <li className="lesson-item current-lesson">
                  <div
                    className="lesson-content"
                    style={{
                      ...lessonStyle,
                      backgroundColor: attributes.currentLessonColor,
                    }}
                  >
                    <label className="lesson-completion">
                      <input
                        type="checkbox"
                        className="lesson-completion-checkbox"
                        checked
                        readOnly
                      />
                      <span
                        className="completion-status"
                        style={{
                          borderColor: attributes.lessonTextColor,
                          backgroundColor: attributes.lessonTextColor,
                        }}
                      ></span>
                    </label>
                    <a href="#" style={{ color: attributes.lessonTextColor }}>
                      Current Lesson
                    </a>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
