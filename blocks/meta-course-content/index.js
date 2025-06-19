/**
 * Course Feature Block
 * Displays a single course feature
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
