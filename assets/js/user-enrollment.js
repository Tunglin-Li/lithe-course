jQuery(document).ready(function ($) {
  // Course filter functionality
  $("#lithe-course-filter").on("input", function () {
    const searchTerm = $(this).val().toLowerCase().trim();

    $(".wpaa-enrollment-item").each(function () {
      const courseTitle = $(this).data("course-title");

      if (courseTitle.indexOf(searchTerm) > -1 || searchTerm === "") {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  // Handle enrollment toggle checkboxes
  $(".wpaa-enrollment-toggle").on("change", function () {
    const $checkbox = $(this);
    const courseId = $checkbox.data("course-id");
    const userId = $checkbox.data("user-id");
    const enroll = $checkbox.is(":checked");

    // Get status element
    const $status = $checkbox
      .closest(".wpaa-enrollment-item")
      .find(".wpaa-enrollment-status");

    // Show loading state
    $status.text(
      enroll
        ? wpaaUserEnrollment.strings.enrolling
        : wpaaUserEnrollment.strings.unenrolling
    );

    // Disable the checkbox during the request
    $checkbox.prop("disabled", true);

    // Clear previous messages
    $("#wpaa-enrollment-messages").empty();

    // Send REST API request to toggle enrollment
    fetch(wpaaUserEnrollment.restUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": wpaaUserEnrollment.nonce,
      },
      body: JSON.stringify({
        user_id: userId,
        course_id: courseId,
        enroll: enroll,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((data) => {
            throw new Error(
              data.message || wpaaUserEnrollment.strings.errorToggling
            );
          });
        }
        return response.json();
      })
      .then((data) => {
        // Update status text
        $status.text(
          enroll
            ? wpaaUserEnrollment.strings.enrolled
            : wpaaUserEnrollment.strings.notEnrolled
        );

        // Update status class
        $status
          .removeClass("enrolled not-enrolled")
          .addClass(enroll ? "enrolled" : "not-enrolled");

        // Show success message
        $("#wpaa-enrollment-messages").html(
          '<div class="notice notice-success is-dismissible"><p>' +
            data.message +
            "</p></div>"
        );
      })
      .catch((error) => {
        // Revert checkbox state
        $checkbox.prop("checked", !enroll);

        // Update status text and class
        $status.text(
          !enroll
            ? wpaaUserEnrollment.strings.enrolled
            : wpaaUserEnrollment.strings.notEnrolled
        );

        $status
          .removeClass("enrolled not-enrolled")
          .addClass(!enroll ? "enrolled" : "not-enrolled");

        // Show error message
        $("#wpaa-enrollment-messages").html(
          '<div class="notice notice-error is-dismissible"><p>' +
            error.message +
            "</p></div>"
        );
      })
      .finally(() => {
        // Re-enable the checkbox
        $checkbox.prop("disabled", false);

        // Make WordPress notices dismissible
        if (
          typeof wp !== "undefined" &&
          wp.notices &&
          wp.notices.removeDismissed
        ) {
          $(".notice.is-dismissible").each(function () {
            var $el = $(this);
            var $button = $(
              '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>'
            );

            $button.on("click.wp-dismiss-notice", function (e) {
              e.preventDefault();
              $el.fadeTo(100, 0, function () {
                $el.slideUp(100, function () {
                  $el.remove();
                });
              });
            });

            $el.append($button);
          });
        }
      });
  });
});
