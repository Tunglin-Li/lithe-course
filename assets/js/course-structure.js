/**
 * Course Structure Metabox JavaScript
 * Handles collapsible modules and sortable functionality in the course structure metabox
 */
jQuery(document).ready(function ($) {
  const courseStructure = {
    init: function () {
      this.bindEvents();
      this.initSortable();
    },

    bindEvents: function () {
      $(".lithe-course-structure")
        .on("click", ".wpaa-add-module-button", this.showModuleForm)
        .on("click", ".wpaa-cancel-module", this.hideModuleForm)
        .on("click", ".wpaa-save-module", this.saveModule)
        .on("click", ".delete-module", this.deleteModule)
        .on("keypress", ".wpaa-new-module-title", this.handleEnterKey)
        .on("click", ".edit-module", this.showModuleEditForm)
        .on("click", ".cancel-module-edit", this.hideModuleEditForm)
        .on("click", ".save-module-title", this.saveModuleTitle)
        .on(
          "keypress",
          ".lithe-module-title-input",
          this.handleModuleTitleEnterKey
        )
        .on("click", ".wpaa-add-lesson-button", this.showLessonForm)
        .on("click", ".cancel-lesson", this.hideLessonForm)
        .on("click", ".save-lesson", this.saveLesson)
        .on("click", ".delete-lesson", this.deleteLesson)
        .on("keypress", ".wpaa-new-lesson-title", this.handleLessonEnterKey)
        .on("click", ".lithe-module-toggle", this.toggleModule);
    },

    initSortable: function () {
      // Make modules sortable
      $(".lithe-modules-list").sortable({
        handle: ".handle",
        placeholder: "lithe-module-item-placeholder",
        update: this.updateModuleOrder,
      });

      // Make lessons sortable within and between modules
      $(".lithe-lessons-list")
        .sortable({
          handle: ".handle",
          connectWith: ".lithe-lessons-list",
          placeholder: "lithe-lesson-item-placeholder",
          update: function (event, ui) {
            // Only trigger if the update is from the receiving list
            if (this === ui.item.parent()[0]) {
              const $lesson = ui.item;
              const $newModule = $lesson.closest(".lithe-module-item");
              const newModuleId = $newModule
                .find(".lithe-lessons-list")
                .data("module-id");
              const lessonId = $lesson.data("id");

              // Get all lessons in the new order
              const lessonOrder = $newModule
                .find(".lithe-lessons-list .lithe-lesson-item")
                .map(function () {
                  return $(this).data("id");
                })
                .get();

              // Update the order
              $.ajax({
                url: wpaaStructure.ajaxurl,
                type: "POST",
                data: {
                  action: "update_lesson_order",
                  nonce: wpaaStructure.nonce,
                  lesson_id: lessonId,
                  module_id: newModuleId,
                  position: $lesson.index(),
                  lesson_order: lessonOrder,
                },
                error: function () {
                  alert("Error updating lesson order");
                  // Revert the sort if there's an error
                  $(this).sortable("cancel");
                },
              });
            }
          },
        })
        .disableSelection();
    },

    showModuleForm: function () {
      $(".lithe-module-form").slideDown();
      $(".wpaa-new-module-title").focus();
    },

    hideModuleForm: function () {
      $(".lithe-module-form").slideUp();
      $(".wpaa-new-module-title").val("");
    },

    handleEnterKey: function (e) {
      if (e.which === 13) {
        e.preventDefault();
        $(".wpaa-save-module").click();
      }
    },

    handleModuleTitleEnterKey: function (e) {
      if (e.which === 13) {
        e.preventDefault();
        $(this)
          .closest(".lithe-module-edit-form")
          .find(".save-module-title")
          .click();
      }
    },

    showModuleEditForm: function () {
      const $moduleItem = $(this).closest(".lithe-module-item");
      const $titleWrapper = $moduleItem.find(".lithe-module-title-wrapper");

      $titleWrapper.find(".lithe-module-title").hide();
      $titleWrapper.find(".lithe-module-edit-form").show();
      $titleWrapper.find(".lithe-module-title-input").focus();
    },

    hideModuleEditForm: function () {
      const $moduleItem = $(this).closest(".lithe-module-item");
      const $titleWrapper = $moduleItem.find(".lithe-module-title-wrapper");

      $titleWrapper.find(".lithe-module-edit-form").hide();
      $titleWrapper.find(".lithe-module-title").show();
    },

    saveModuleTitle: function () {
      const $moduleItem = $(this).closest(".lithe-module-item");
      const $titleWrapper = $moduleItem.find(".lithe-module-title-wrapper");
      const $titleInput = $titleWrapper.find(".lithe-module-title-input");
      const title = $titleInput.val().trim();

      if (!title) {
        alert(wpaaStructure.i18n.addModuleTitle);
        return;
      }

      $.ajax({
        url: wpaaStructure.ajaxurl,
        type: "POST",
        data: {
          action: "update_module_title",
          nonce: wpaaStructure.nonce,
          module_id: $moduleItem.data("id"),
          title: title,
        },
        success: function (response) {
          if (response.success) {
            $titleWrapper
              .find(".lithe-module-title")
              .text(response.data.title)
              .show();
            $titleWrapper.find(".lithe-module-edit-form").hide();
          } else {
            alert(response.data);
          }
        },
        error: function () {
          alert("Error updating module title");
        },
      });
    },

    saveModule: function () {
      const title = $(".wpaa-new-module-title").val().trim();
      if (!title) {
        alert(wpaaStructure.i18n.addModuleTitle);
        return;
      }

      const courseId = $(".lithe-modules-list").data("course-id");

      $.ajax({
        url: wpaaStructure.ajaxurl,
        type: "POST",
        data: {
          action: "add_new_module",
          nonce: wpaaStructure.nonce,
          title: title,
          course_id: courseId,
        },
        success: function (response) {
          if (response.success) {
            const $modulesList = $(".lithe-modules-list");
            const $noModules = $(".wpaa-no-modules");

            if ($noModules.length) {
              $noModules.remove();
            }

            $modulesList.append(response.data.html);
            courseStructure.hideModuleForm();
          } else {
            alert(response.data);
          }
        },
        error: function () {
          alert("Error adding module");
        },
      });
    },

    deleteModule: function () {
      if (!confirm(wpaaStructure.i18n.confirmDelete)) {
        return;
      }

      const $module = $(this).closest(".lithe-module-item");
      const moduleId = $module.data("id");

      $.ajax({
        url: wpaaStructure.ajaxurl,
        type: "POST",
        data: {
          action: "delete_module",
          nonce: wpaaStructure.nonce,
          module_id: moduleId,
        },
        success: function (response) {
          if (response.success) {
            $module.slideUp(function () {
              $(this).remove();

              // Show "no modules" message if this was the last module
              if ($(".lithe-module-item").length === 0) {
                $(".lithe-modules-list").append(
                  '<p class="wpaa-no-modules">' +
                    "No modules found. Add a new module to get started." +
                    "</p>"
                );
              }
            });
          } else {
            alert(response.data);
          }
        },
        error: function () {
          alert("Error deleting module");
        },
      });
    },

    updateModuleOrder: function () {
      const moduleOrder = $(".lithe-modules-list").sortable("toArray", {
        attribute: "data-id",
      });
      const courseId = $(".lithe-modules-list").data("course-id");

      $.ajax({
        url: wpaaStructure.ajaxurl,
        type: "POST",
        data: {
          action: "update_module_order",
          nonce: wpaaStructure.nonce,
          module_order: moduleOrder,
          course_id: courseId,
        },
        error: function () {
          alert("Error updating module order");
        },
      });
    },

    showLessonForm: function () {
      const $moduleItem = $(this).closest(".lithe-module-item");
      $moduleItem.find(".lithe-lesson-form").show();
      $moduleItem.find(".wpaa-new-lesson-title").focus();
      $moduleItem.find(".wpaa-add-lesson-button").hide();
    },

    hideLessonForm: function () {
      const $moduleItem = $(this).closest(".lithe-module-item");
      $moduleItem.find(".lithe-lesson-form").hide();
      $moduleItem.find(".wpaa-new-lesson-title").val("");
      $moduleItem.find(".wpaa-add-lesson-button").show();
    },

    handleLessonEnterKey: function (e) {
      if (e.which === 13) {
        e.preventDefault();
        $(this).closest(".lithe-lesson-form").find(".save-lesson").click();
      }
    },

    saveLesson: function () {
      const $moduleItem = $(this).closest(".lithe-module-item");
      const $titleInput = $moduleItem.find(".wpaa-new-lesson-title");
      const title = $titleInput.val().trim();
      const moduleId = $moduleItem
        .find(".lithe-lessons-list")
        .data("module-id");

      if (!title) {
        alert("Please enter a title for the lesson");
        return;
      }

      $.ajax({
        url: wpaaStructure.ajaxurl,
        type: "POST",
        data: {
          action: "add_new_lesson",
          nonce: wpaaStructure.nonce,
          title: title,
          module_id: moduleId,
        },
        success: function (response) {
          if (response.success) {
            $moduleItem.find(".lithe-lessons-list").append(response.data.html);
            $moduleItem.find(".lithe-lesson-form").hide();
            $moduleItem.find(".wpaa-new-lesson-title").val("");
            $moduleItem.find(".wpaa-add-lesson-button").show();
          } else {
            alert(response.data);
          }
        },
        error: function () {
          alert("Error adding lesson");
        },
      });
    },

    deleteLesson: function () {
      if (!confirm("Are you sure you want to delete this lesson?")) {
        return;
      }

      const $lesson = $(this).closest(".lithe-lesson-item");
      const lessonId = $lesson.data("id");

      $.ajax({
        url: wpaaStructure.ajaxurl,
        type: "POST",
        data: {
          action: "delete_lesson",
          nonce: wpaaStructure.nonce,
          lesson_id: lessonId,
        },
        success: function (response) {
          if (response.success) {
            $lesson.slideUp(function () {
              $(this).remove();

              const $lessonsList = $lesson.closest(".lithe-lessons-list");
              if ($lessonsList.find(".lithe-lesson-item").length === 0) {
                $lessonsList.append(
                  '<li class="wpaa-no-lessons">' +
                    "No lessons found. Add a new lesson to get started." +
                    "</li>"
                );
              }
            });
          } else {
            alert(response.data);
          }
        },
        error: function () {
          alert("Error deleting lesson");
        },
      });
    },

    toggleModule: function () {
      const $moduleItem = $(this).closest(".lithe-module-item");
      $moduleItem.find(".lithe-module-content").slideToggle();
      $(this).toggleClass("lithe-module-open");
    },
  };

  courseStructure.init();

  // Toggle module content when header is clicked
  $(".module-header").on("click", function (e) {
    // Don't toggle if clicking on the drag handle
    if (
      $(e.target).hasClass("module-drag-handle") ||
      $(e.target).closest(".module-drag-handle").length
    ) {
      return;
    }

    var $moduleItem = $(this).closest(".module-item");
    var $moduleContent = $moduleItem.find(".module-content");
    var $toggleIcon = $(this).find(".module-toggle .dashicons");

    // Toggle the content display
    $moduleContent.slideToggle(300, function () {
      // Update the toggle icon based on the content visibility
      if ($moduleContent.is(":visible")) {
        $toggleIcon
          .removeClass("dashicons-arrow-right-alt2")
          .addClass("dashicons-arrow-down-alt2");
        $moduleItem.addClass("module-open");
      } else {
        $toggleIcon
          .removeClass("dashicons-arrow-down-alt2")
          .addClass("dashicons-arrow-right-alt2");
        $moduleItem.removeClass("module-open");
      }
    });
  });

  // Make the module headers look clickable
  $(".module-header").css("cursor", "pointer");

  // Initialize sortable for modules
  if ($("#lithe-modules-list").length) {
    $("#lithe-modules-list").sortable({
      handle: ".module-drag-handle",
      placeholder: "ui-sortable-placeholder",
      tolerance: "pointer",
      axis: "y",
      cursor: "move",
      opacity: 0.7,
      update: function (event, ui) {
        updateModuleOrder();
      },
    });
  }

  // Function to update module order via AJAX
  function updateModuleOrder() {
    var moduleOrder = [];

    // Get the order of modules
    $(".module-item").each(function (index) {
      moduleOrder.push({
        term_id: $(this).data("module-id"),
        order: index,
      });
    });

    // Show loading indicator
    $("#wpaa-order-status")
      .removeClass("notice-success notice-error")
      .addClass("notice-info")
      .find("p")
      .text("Updating module order...")
      .end()
      .show();

    // Send AJAX request to update order
    $.ajax({
      url: wpaaStructure.ajaxUrl,
      type: "POST",
      data: {
        action: "wpaa_update_module_order",
        module_order: moduleOrder,
        course_id: $("#lithe-modules-list").data("course-id"),
        nonce: wpaaStructure.nonce,
      },
      success: function (response) {
        if (response.success) {
          $("#wpaa-order-status")
            .removeClass("notice-info notice-error")
            .addClass("notice-success")
            .find("p")
            .text(
              response.data && response.data.message
                ? response.data.message
                : wpaaStructure.orderUpdated
            );

          // Hide the message after 3 seconds
          setTimeout(function () {
            $("#wpaa-order-status").fadeOut();
          }, 3000);

          // If no modules were updated, log a message to console for debugging
          if (response.data && response.data.updated_count === 0) {
            console.log(
              "No modules were updated. Check term_order meta values."
            );
          }
        } else {
          $("#wpaa-order-status")
            .removeClass("notice-info notice-success")
            .addClass("notice-error")
            .find("p")
            .text(response.data || wpaaStructure.orderFailed);
        }
      },
      error: function () {
        $("#wpaa-order-status")
          .removeClass("notice-info notice-success")
          .addClass("notice-error")
          .find("p")
          .text(wpaaStructure.orderFailed);
      },
    });
  }
});
