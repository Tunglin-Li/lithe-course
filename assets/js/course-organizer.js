jQuery(document).ready(function ($) {
  function initializeSortable() {
    // Make modules sortable within courses
    $(".lithe-modules-container").sortable({
      items: ".lithe-module",
      connectWith: ".lithe-modules-container, .wpaa-unassigned-modules",
      placeholder: "lithe-module wpaa-droppable-hover",
      start: function (e, ui) {
        ui.item.addClass("wpaa-dragging");
      },
      stop: function (e, ui) {
        ui.item.removeClass("wpaa-dragging");
        updateStructure();
      },
    });

    // Make lessons sortable within modules
    $(".lithe-lessons-container").sortable({
      items: ".lithe-lesson",
      connectWith: ".lithe-lessons-container, .wpaa-unassigned-lessons",
      placeholder: "lithe-lesson wpaa-droppable-hover",
      start: function (e, ui) {
        ui.item.addClass("wpaa-dragging");
      },
      stop: function (e, ui) {
        ui.item.removeClass("wpaa-dragging");
        updateStructure();
      },
    });

    // Make unassigned modules sortable
    $(".wpaa-unassigned-modules").sortable({
      items: ".lithe-module",
      connectWith: ".lithe-modules-container",
      placeholder: "lithe-module wpaa-droppable-hover",
      start: function (e, ui) {
        ui.item.addClass("wpaa-dragging");
      },
      stop: function (e, ui) {
        ui.item.removeClass("wpaa-dragging");
        updateStructure();
      },
    });

    // Make unassigned lessons sortable
    $(".wpaa-unassigned-lessons").sortable({
      items: ".lithe-lesson",
      connectWith: ".lithe-lessons-container",
      placeholder: "lithe-lesson wpaa-droppable-hover",
      start: function (e, ui) {
        ui.item.addClass("wpaa-dragging");
      },
      stop: function (e, ui) {
        ui.item.removeClass("wpaa-dragging");
        updateStructure();
      },
    });
  }

  function updateStructure() {
    var structure = {};

    // Gather course structure
    $(".lithe-course").each(function () {
      var courseId = $(this).data("id");
      structure[courseId] = {
        modules: [],
      };

      $(this)
        .find(".lithe-module")
        .each(function (moduleIndex) {
          var moduleId = $(this).data("id");
          var moduleData = {
            id: moduleId,
            lessons: [],
          };

          $(this)
            .find(".lithe-lesson")
            .each(function (lessonIndex) {
              moduleData.lessons.push($(this).data("id"));
            });

          structure[courseId].modules.push(moduleData);
        });
    });

    // Send to server
    $.ajax({
      url: wpaaOrganizer.ajaxurl,
      method: "POST",
      data: {
        action: "update_course_structure",
        nonce: wpaaOrganizer.nonce,
        structure: JSON.stringify(structure),
      },
      success: function (response) {
        if (!response.success) {
          console.error("Failed to update course structure");
        }
      },
      error: function () {
        console.error("Ajax request failed");
      },
    });
  }

  // Initialize sortable functionality
  initializeSortable();
});
