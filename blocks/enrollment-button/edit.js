import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

export default function Edit() {
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <div className="wp-block-button">
        <div className="wp-block-button__link">Enroll Button</div>
      </div>
    </div>
  );
}
