jQuery(document).ready(function ($) {
  // Debug check to ensure API URL is properly defined
  if (
    typeof wpaaEnrollment === "undefined" ||
    typeof wpaaEnrollment.apiUrl === "undefined"
  ) {
    console.error("ERROR: wpaaEnrollment object or apiUrl is not defined!");
    alert(
      "Enrollment system configuration error. Please contact the administrator."
    );
    return;
  } else {
    console.log("REST API URL is configured as:", wpaaEnrollment.apiUrl);
  }

  // Handle enrollment button clicks
  $(".wpaa-enroll-button").on("click", function () {
    const $button = $(this);
    const courseId = $button.data("course");

    // Find the status element - might be a sibling or in parent container
    const $status = $button.siblings(".wpaa-enrollment-status").length
      ? $button.siblings(".wpaa-enrollment-status")
      : $button
          .closest(".wpaa-enrollment-action, .wpaa-enrollment-button-wrap")
          .find(".wpaa-enrollment-status, .wpaa-enrollment-status-message");

    // Disable button and show loading status
    $button.prop("disabled", true);
    $status.text("Enrolling...").show();

    // Send enrollment request to the REST API
    fetch(wpaaEnrollment.apiUrl + "/enroll/" + courseId, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": wpaaEnrollment.nonce,
      },
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((data) => {
            throw new Error(data.message || "Error enrolling in course");
          });
        }
        return response.json();
      })
      .then((data) => {
        // Show success message
        $status.text("Successfully enrolled!");

        // Replace button with "Continue Learning" button
        const $buttonContainer = $button.closest(
          ".wpaa-enrollment-action, .wpaa-enrollment-button-wrap"
        );

        // Handle different block types
        if ($buttonContainer.hasClass("wpaa-enrollment-action")) {
          // For the course-enrollment block
          // Update enrollment status message
          $buttonContainer
            .siblings(".wpaa-enrollment-status")
            .html(
              '<div class="wpaa-enrollment-message wpaa-enrolled">' +
                '<span class="dashicons dashicons-yes-alt"></span>' +
                "You are enrolled in this course" +
                "</div>"
            );
        }

        // After short delay, reload the page to show updated enrollment status
        setTimeout(function () {
          location.reload();
        }, 1500);
      })
      .catch((error) => {
        // Show error message
        $status.text(error.message || "Error enrolling in course");
        $button.prop("disabled", false);

        // Hide error message after 3 seconds
        setTimeout(function () {
          $status.fadeOut();
        }, 3000);
      });
  });

  // Handle unenroll button clicks in admin
  $(".unenroll-user").on("click", function (e) {
    e.preventDefault();

    const $button = $(this);
    const userId = $button.data("user");
    const courseId = $button.data("course");

    if (
      !confirm("Are you sure you want to unenroll this user from the course?")
    ) {
      return;
    }

    // Disable button and show loading state
    $button.prop("disabled", true).text("Unenrolling...");

    // Send REST API request to unenroll the user
    fetch(wpaaEnrollment.apiUrl + "/unenroll", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": wpaaEnrollment.nonce,
      },
      body: JSON.stringify({
        user_id: userId,
        course_id: courseId,
      }),
    })
      .then((response) => {
        if (!response.ok) {
          return response.json().then((data) => {
            throw new Error(data.message || "Error unenrolling user");
          });
        }
        return response.json();
      })
      .then((data) => {
        // Remove the user's row from the table
        $button.closest("tr").fadeOut(300, function () {
          $(this).remove();

          // Update the enrollment count
          const $enrollmentCount = $(".wpaa-admin-enrollment-count strong");
          let count = parseInt($enrollmentCount.text()) - 1;
          $enrollmentCount.text(count);

          // Update the text for singular/plural
          const $enrollmentText = $(".wpaa-admin-enrollment-count");
          if (count === 1) {
            $enrollmentText.html(
              "<p>There is <strong>1 user</strong> enrolled in this course.</p>"
            );
          } else {
            $enrollmentText.html(
              "<p>There are <strong>" +
                count +
                " users</strong> enrolled in this course.</p>"
            );
          }

          // If no users left, show "no users" message
          if (count === 0) {
            $(".wp-list-table").remove();
            $(".wpaa-admin-enrollments").append(
              "<p>No users are enrolled in this course yet.</p>"
            );
          }
        });
      })
      .catch((error) => {
        // Re-enable button and show error
        $button.prop("disabled", false).text("Unenroll");
        alert("Error: " + error.message);
      });
  });
});
