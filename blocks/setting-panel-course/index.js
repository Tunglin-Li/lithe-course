import { registerPlugin } from "@wordpress/plugins";
import { PluginDocumentSettingPanel, PluginSidebar } from "@wordpress/editor";
import { useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import CourseInformation from "./CourseInformation";
import CourseStructure from "./CourseStructure";
import CourseSetting from "./CourseSetting";
import CourseVideo from "./CourseVideo";
import EnrolledStudent from "./EnrolledStudent";
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
        className="course-settings-panel"
        title={__("Course Settings", "lithe-course")}
      >
        <CourseSetting />
      </PluginDocumentSettingPanel>
      <PluginDocumentSettingPanel
        name="course-video-panel"
        className="course-video-panel"
        title={__("Course Video", "lithe-course")}
      >
        <CourseVideo />
      </PluginDocumentSettingPanel>
      <PluginDocumentSettingPanel
        name="course-information-panel"
        className="course-information-panel"
        title={__("Course Information", "lithe-course")}
      >
        <CourseInformation
          fieldKey="_features"
          title={__("Course Features", "lithe-course")}
          addButtonText={__("Add Feature", "lithe-course")}
          placeholder={__("Enter feature description", "lithe-course")}
          emptyMessage={__("No features added yet", "lithe-course")}
        />

        <CourseInformation
          fieldKey="_learnings"
          title={__("What You Will Learn", "lithe-course")}
          addButtonText={__("Add Learning", "lithe-course")}
          placeholder={__("Enter what students will learn", "lithe-course")}
          emptyMessage={__("No learning outcomes added yet", "lithe-course")}
        />

        <CourseInformation
          fieldKey="_suitable"
          title={__("Who This Course Is For", "lithe-course")}
          addButtonText={__("Add Target Audience", "lithe-course")}
          placeholder={__("Enter target audience description", "lithe-course")}
          emptyMessage={__("No target audience defined yet", "lithe-course")}
        />

        <CourseInformation
          fieldKey="_requirements"
          title={__("Prerequisites", "lithe-course")}
          addButtonText={__("Add Requirement", "lithe-course")}
          placeholder={__("Enter course requirement", "lithe-course")}
          emptyMessage={__("No requirements specified yet", "lithe-course")}
        />
      </PluginDocumentSettingPanel>
      <PluginDocumentSettingPanel
        name="enrolled-students-panel"
        className="enrolled-students-panel"
        title={__("Enrolled Students", "lithe-course")}
      >
        <EnrolledStudent />
      </PluginDocumentSettingPanel>
    </>
  );
};

const CourseStructurePanel = () => {
  return (
    <PluginSidebar name="course-structure-panel" title="Course Structure">
      <CourseStructure />
    </PluginSidebar>
  );
};

registerPlugin("course-information-panel", {
  render: CourseSettingsPanel,
  icon: "admin-generic",
});

registerPlugin("course-structure-panel", {
  render: CourseStructurePanel,
  icon: "welcome-learn-more",
});
