jQuery(document).ready(function ($) {
  // Handle bulk action module selection
  $("#bulk-action-selector-top, #bulk-action-selector-bottom").on(
    "change",
    function () {
      var $bulkModuleSelect = $("#bulk-module-id");
      if ($(this).val() === "assign_to_module") {
        $bulkModuleSelect.show().insertAfter($(this));
      } else {
        $bulkModuleSelect.hide();
      }
    }
  );

  // Validate bulk action before submit
  $("form#posts-filter").on("submit", function (e) {
    var $bulkAction =
      $("#bulk-action-selector-top").val() ||
      $("#bulk-action-selector-bottom").val();

    if ($bulkAction === "assign_to_module") {
      var $moduleId = $("#bulk-module-id").val();
      if (!$moduleId) {
        e.preventDefault();
        alert("Please select a module to assign the lessons to.");
        return false;
      }
    }
  });
});
