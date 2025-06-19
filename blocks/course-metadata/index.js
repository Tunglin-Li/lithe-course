/**
 * Course Metadata Block
 * Displays various types of course metadata based on selection
 */
import { registerBlockType } from "@wordpress/blocks";
import Edit from "./edit";
import metadata from "./block.json";
import "./style.scss";

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: function Save() {
    // Return null to use PHP render callback
    return null;
  },
});
