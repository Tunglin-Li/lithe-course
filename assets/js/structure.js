jQuery(document).ready(function ($) {
  const courseSelect = $("#course-select");
  const modulesList = $("#modules-list");
  const unassignedList = $("#unassigned-list");

  // Initialize sortable
  function initSortable() {
    modulesList.sortable({
      handle: ".module-handle",
      placeholder: "placeholder",
      axis: "y",
    });

    $(".module-lessons").sortable({
      connectWith: ".module-lessons, #unassigned-list",
      placeholder: "placeholder",
      items: ".lesson-item",
    });

    unassignedList.sortable({
      connectWith: ".module-lessons",
      placeholder: "placeholder",
      items: ".lesson-item",
    });
  }

  // Load course structure on page load
  function loadStructure() {
    $.ajax({
      url: wpaaStructure.ajaxurl,
      type: "POST",
      data: {
        action: "get_course_structure",
        course_id: wpaaStructure.courseId,
        nonce: wpaaStructure.nonce,
      },
      success: function (response) {
        if (response.success) {
          renderStructure(response.data);
          initSortable();
        }
      },
    });
  }

  // Render course structure
  function renderStructure(structure) {
    modulesList.empty();
    unassignedList.empty();

    structure.modules.forEach(function (module) {
      const moduleHtml = `
                <div class="module-item" data-id="${module.id}">
                    <div class="module-handle">
                        <span class="dashicons dashicons-menu"></span>
                        ${module.title}
                    </div>
                    <div class="module-lessons">
                        ${module.lessons
                          .map(
                            (lesson) => `
                            <div class="lesson-item" data-id="${lesson.id}">
                                <span class="dashicons dashicons-menu"></span>
                                ${lesson.title}
                            </div>
                        `
                          )
                          .join("")}
                    </div>
                </div>
            `;
      modulesList.append(moduleHtml);
    });

    structure.unassigned.forEach(function (lesson) {
      const lessonHtml = `
                <div class="lesson-item" data-id="${lesson.id}">
                    <span class="dashicons dashicons-menu"></span>
                    ${lesson.title}
                </div>
            `;
      unassignedList.append(lessonHtml);
    });
  }

  // Save structure
  $("#save-structure").on("click", function () {
    const structure = [];
    modulesList.find(".module-item").each(function () {
      const moduleId = $(this).data("id");
      const lessons = [];

      $(this)
        .find(".lesson-item")
        .each(function () {
          lessons.push({
            id: $(this).data("id"),
            title: $(this).text().trim(),
          });
        });

      structure.push({
        id: moduleId,
        lessons: lessons,
      });
    });

    $.ajax({
      url: wpaaStructure.ajaxurl,
      type: "POST",
      data: {
        action: "save_course_structure",
        course_id: wpaaStructure.courseId,
        structure: JSON.stringify(structure),
        nonce: wpaaStructure.nonce,
      },
      success: function (response) {
        if (response.success) {
          alert(response.data);
        } else {
          alert("Save failed");
        }
      },
    });
  });

  // Load structure on page load
  loadStructure();
});
