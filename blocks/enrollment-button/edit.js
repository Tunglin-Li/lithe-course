import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

export default function Edit() {
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <div className="wp-block-button wpaa-enrollment-button-wrap">
        <a href="#" className="wp-block-button__link wp-element-button">
          {__("Enroll Now", "lithe-course")}
        </a>
      </div>
    </div>
  );
}
