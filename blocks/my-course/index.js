import { registerBlockVariation } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";

const VARIATION_NAME = "lithe-course-my-course-list";

registerBlockVariation("core/query", {
  name: VARIATION_NAME,
  title: __("My Course", "lithe-course"),
  description: __("Displays a list of my courses", "lithe-course"),
  icon: "calendar-alt",
  category: "lithe-course",
  attributes: {
    namespace: VARIATION_NAME,
    align: "wide",
    query: {
      postType: "lithe_course",
      offset: 0,
      filterByEnrolled: true,
    },
  },
  isActive: ["namespace"],
  scope: ["inserter"],
  allowedControls: [],
  innerBlocks: [
    [
      "core/post-template",
      { layout: { type: "grid", columnCount: 3 } },
      [
        // group block
        [
          "core/group",
          {},
          [
            ["core/post-featured-image", { isLink: true }],
            ["core/post-author-name", {}],
            ["core/post-title", { level: 3, isLink: true }],
            ["core/post-excerpt", { length: 20 }],
          ],
        ],
      ],
    ],
  ],
});
