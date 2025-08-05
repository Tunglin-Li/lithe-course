document.addEventListener("DOMContentLoaded", function () {
  // Accordion functionality
  const modules = document.querySelectorAll(".lithe-module");
  let currentLessonFound = false;

  // First pass - look for current lesson
  modules.forEach((module) => {
    const hasCurrentLesson = module.querySelector(".current-lesson") !== null;
    if (hasCurrentLesson) {
      currentLessonFound = true;
    }
  });

  // Second pass - open appropriate module
  modules.forEach((module, index) => {
    const header = module.querySelector(".module-header");
    const content = module.querySelector(".module-content");
    const toggle = module.querySelector(".module-toggle");

    const hasCurrentLesson = module.querySelector(".current-lesson") !== null;

    // Only open the module with current lesson, or first module if no current lesson
    if (hasCurrentLesson || (!currentLessonFound && index === 0)) {
      content.classList.add("is-open");
      toggle.classList.remove("dashicons-arrow-right");
      toggle.classList.add("dashicons-arrow-down");
    } else {
      content.classList.remove("is-open");
      toggle.classList.remove("dashicons-arrow-down");
      toggle.classList.add("dashicons-arrow-right");
    }

    header.addEventListener("click", function () {
      // Toggle current module only
      content.classList.toggle("is-open");
      toggle.classList.toggle("dashicons-arrow-down");
      toggle.classList.toggle("dashicons-arrow-right");
    });
  });
});
