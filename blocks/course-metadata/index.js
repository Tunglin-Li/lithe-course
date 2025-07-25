/**
 * Course Metadata Block
 * Displays various types of course metadata based on selection
 */
import { registerBlockType } from "@wordpress/blocks";
import Edit from "./edit";
import metadata from "./block.json";
import "./style.scss";
import { __ } from "@wordpress/i18n";

registerBlockType(metadata.name, {
  ...metadata,
  title: __("Course Metadata", "lithe-course"),
  description: __("Displays various course metadata", "lithe-course"),
  edit: Edit,
  save: function Save() {
    // Return null to use PHP render callback
    return null;
  },
});
