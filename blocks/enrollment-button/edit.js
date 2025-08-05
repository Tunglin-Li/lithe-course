import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

export default function Edit() {
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <div className="wp-block-button lithe-course-enrollment-button-wrap">
        <button className="wp-block-button__link wp-element-button lithe-course-enroll-button">
          {__("Enroll Now", "lithe-course")}
        </button>
      </div>
    </div>
  );
}
