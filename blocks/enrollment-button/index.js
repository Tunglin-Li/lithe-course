import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import metadata from "./block.json";
import Edit from "./edit";
import "./style.scss";

registerBlockType(metadata.name, {
  ...metadata,
  title: __("Enrollment Button", "lithe-course"),
  description: __("Enroll in a course", "lithe-course"),
  edit: Edit,
  save: function Save() {
    // Return null to use PHP render callback
    return null;
  },
});
