import { registerPlugin } from "@wordpress/plugins";
import { CourseVideoPanel } from "./course-video";
import { CourseFeaturesPanel } from "./course-features";

registerPlugin("course-video-panel", {
  render: CourseVideoPanel,
  icon: "video-alt3",
});

// registerPlugin("course-features-panel", {
//   render: CourseFeaturesPanel,
//   icon: "star-filled",
// });
