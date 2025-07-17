/**
 * Enrolled Student Count Block
 * Displays the number of students enrolled in a course
 */
import { registerBlockType } from "@wordpress/blocks";
import Edit from "./edit";
import metadata from "./block.json";
import "./style.scss";
import { __ } from "@wordpress/i18n";

registerBlockType(metadata.name, {
  ...metadata,
  title: __("Enrolled Student Count", "lithe-course"),
  description: __(
    "Displays the number of students enrolled in a course",
    "lithe-course"
  ),
  edit: Edit,
  save: function Save() {
    // Return null to use PHP render callback
    return null;
  },
});
