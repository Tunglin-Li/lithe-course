import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

export default function Edit() {
  const blockProps = useBlockProps();

  return (
    <div {...blockProps}>
      <div className="lithe-course-video">
        <div className="video-placeholder">
          <div className="video-placeholder-content">
            <span className="dashicons dashicons-video-alt3"></span>
            <p>{__("Course Video", "lithe-course")}</p>
            <p className="video-placeholder-note">
              {__("Video will be displayed here", "lithe-course")}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
