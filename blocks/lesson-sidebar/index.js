import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import metadata from "./block.json";
import Edit from "./edit.js";
import "./style.scss";

registerBlockType(metadata.name, {
  ...metadata,
  title: __("Lesson Sidebar", "lithe-course"),
  description: __("Displays a list of course modules", "lithe-course"),
  edit: Edit,
  save: function Save() {
    // Return null to use PHP render callback
    return null;
  },
});
