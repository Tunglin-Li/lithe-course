import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import metadata from "./block.json";
import Edit from "./edit";
import "./style.css";

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save: function Save() {
    // Return null to use PHP render callback
    return null;
  },
});
