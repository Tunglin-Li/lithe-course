import { registerPlugin } from "@wordpress/plugins";
import { PluginDocumentSettingPanel, PluginSidebar } from "@wordpress/editor";
import { Panel } from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import MetaFieldManager from "./MetaFieldManager";
import CourseStructure from "./CourseStructure";
import "./style.scss";

const CourseSettingsPanel = () => {
  // Get current post type
  const currentPostType = useSelect((select) => {
    return select("core/editor").getCurrentPostType();
  }, []);

  // Only show this panel for course post type
  if (currentPostType !== "lithe_course") {
    return null;
  }

  return (
    <>
      <PluginDocumentSettingPanel
        name="course-settings-panel"
        title="Course Settings"
        className="course-settings-panel"
      >
        <Panel>
          <MetaFieldManager
            fieldKey="_features"
            title={__("Course Features", "lithe-course")}
            addButtonText={__("Add Feature", "lithe-course")}
            placeholder={__("Enter feature description", "lithe-course")}
            emptyMessage={__("No features added yet", "lithe-course")}
          />

          <MetaFieldManager
            fieldKey="_learnings"
            title={__("What You Will Learn", "lithe-course")}
            addButtonText={__("Add Learning", "lithe-course")}
            placeholder={__("Enter what students will learn", "lithe-course")}
            emptyMessage={__("No learning outcomes added yet", "lithe-course")}
          />

          <MetaFieldManager
            fieldKey="_suitable"
            title={__("Who This Course Is For", "lithe-course")}
            addButtonText={__("Add Target Audience", "lithe-course")}
            placeholder={__(
              "Enter target audience description",
              "lithe-course"
            )}
            emptyMessage={__("No target audience defined yet", "lithe-course")}
          />

          <MetaFieldManager
            fieldKey="_requirements"
            title={__("Prerequisites", "lithe-course")}
            addButtonText={__("Add Requirement", "lithe-course")}
            placeholder={__("Enter course requirement", "lithe-course")}
            emptyMessage={__("No requirements specified yet", "lithe-course")}
          />
        </Panel>
      </PluginDocumentSettingPanel>
      <PluginDocumentSettingPanel
        name="course-structure-panel"
        title="Course Structure"
        className="course-structure-panel"
      ></PluginDocumentSettingPanel>
    </>
  );
};

const CourseStructurePanel = () => {
  return (
    <PluginSidebar name="course-structure-panel" title="Course Structure">
      <Panel>
        <CourseStructure />
      </Panel>
    </PluginSidebar>
  );
};

registerPlugin("course-settings-panel", {
  render: CourseSettingsPanel,
  icon: "admin-generic",
});

registerPlugin("course-structure-panel", {
  render: CourseStructurePanel,
  icon: "welcome-learn-more",
});
