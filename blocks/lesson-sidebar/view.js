document.addEventListener("DOMContentLoaded", function () {
  // Get configuration from localized script
  const config = window.litheLessonSidebar;

  if (!config) {
    console.error("Lesson sidebar configuration not found");
    return;
  }

  // Accordion functionality
  const modules = document.querySelectorAll(".lithe-module");
  const isPublicCourse = config.isPublicCourse;
  let currentLessonFound = false;

  // First pass - look for current lesson
  modules.forEach((module) => {
    const hasCurrentLesson = module.querySelector(".current-lesson") !== null;
    if (hasCurrentLesson) {
      currentLessonFound = true;
    }
  });

  // Second pass - set up modules based on course type
  modules.forEach((module, index) => {
    const header = module.querySelector(".module-header");
    const content = module.querySelector(".module-content");
    const toggle = module.querySelector(".module-toggle");

    if (isPublicCourse) {
      // For public courses, keep all modules open and still allow accordion functionality
      content.classList.add("is-open");
      if (toggle) {
        toggle.classList.remove("dashicons-arrow-right");
        toggle.classList.add("dashicons-arrow-down");
      }

      header.addEventListener("click", function () {
        // Toggle current module
        content.classList.toggle("is-open");
        if (toggle) {
          toggle.classList.toggle("dashicons-arrow-down");
          toggle.classList.toggle("dashicons-arrow-right");
        }
      });
    } else {
      // For non-public courses, use original accordion functionality
      const hasCurrentLesson = module.querySelector(".current-lesson") !== null;

      // Only open the module with current lesson, or first module if no current lesson
      if (hasCurrentLesson || (!currentLessonFound && index === 0)) {
        content.classList.add("is-open");
        if (toggle) {
          toggle.classList.remove("dashicons-arrow-right");
          toggle.classList.add("dashicons-arrow-down");
        }
      } else {
        content.classList.remove("is-open");
        if (toggle) {
          toggle.classList.remove("dashicons-arrow-down");
          toggle.classList.add("dashicons-arrow-right");
        }
      }

      header.addEventListener("click", function () {
        // Toggle current module only
        content.classList.toggle("is-open");
        if (toggle) {
          toggle.classList.toggle("dashicons-arrow-down");
          toggle.classList.toggle("dashicons-arrow-right");
        }
      });
    }
  });

  // Lesson completion functionality
  const checkboxes = document.querySelectorAll(".lesson-completion-checkbox");
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      const lessonId = this.dataset.lessonId;
      const completed = this.checked;
      const completionStatus =
        this.parentElement.querySelector(".completion-status");

      // Show loading state
      this.disabled = true;
      this.parentElement.classList.add("loading");

      // Send AJAX request
      fetch(config.ajaxUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "lithecourse_update_lesson_completion",
          nonce: config.nonce,
          lesson_id: lessonId,
          completed: completed,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Update UI immediately
            this.checked = completed;

            // Update completion status styling
            const lessonTextColor = config.lessonTextColor;
            const defaultColor = "#2271b1";
            const borderColor = lessonTextColor || defaultColor;

            if (completed) {
              completionStatus.style.backgroundColor = borderColor;
              completionStatus.style.borderColor = borderColor;
            } else {
              completionStatus.style.backgroundColor = "transparent";
              completionStatus.style.borderColor = borderColor;
            }

            this.parentElement.classList.remove("loading");
          } else {
            // Revert on error
            this.checked = !completed;
            alert("Failed to update completion status");
          }
        })
        .catch((error) => {
          // Revert on error
          this.checked = !completed;
          alert("Error updating completion status");
        })
        .finally(() => {
          this.disabled = false;
          this.parentElement.classList.remove("loading");
        });
    });
  });
});
