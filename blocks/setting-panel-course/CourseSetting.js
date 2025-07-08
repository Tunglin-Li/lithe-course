import { PanelRow, RadioControl } from "@wordpress/components";
import { useSelect, useDispatch } from "@wordpress/data";
import { __ } from "@wordpress/i18n";

export default function CourseSetting() {
  const { editPost } = useDispatch("core/editor");

  const courseType = useSelect((select) => {
    const meta = select("core/editor").getEditedPostAttribute("meta");
    return meta?._course_type || "free"; // Default to free
  }, []);

  const handleCourseTypeChange = (value) => {
    editPost({
      meta: {
        _course_type: value,
      },
    });
  };

  return (
    <>
      <PanelRow>
        <RadioControl
          label={__("Course Access Type", "lithe-course")}
          help={__(
            "Select how users can access this course content",
            "lithe-course"
          )}
          selected={courseType}
          options={[
            {
              label: __("Public (No Login Required)", "lithe-course"),
              value: "public",
              help: __(
                "Anyone can view course content without logging in",
                "lithe-course"
              ),
            },
            {
              label: __("Free (Login + Enrollment Required)", "lithe-course"),
              value: "free",
              help: __(
                "Users must log in and enroll to access content",
                "lithe-course"
              ),
            },
          ]}
          onChange={handleCourseTypeChange}
        />
      </PanelRow>
    </>
  );
}
