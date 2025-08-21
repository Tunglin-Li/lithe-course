document.addEventListener("DOMContentLoaded", function () {
  // Handle enrollment button clicks
  const enrollButtons = document.querySelectorAll(".lithecourse-enroll-button");

  enrollButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      const courseId = this.dataset.course;

      // Find the status element - might be a sibling or in parent container
      const statusElement =
        this.parentElement.querySelector(".lithecourse-enrollment-status") ||
        this.closest(
          ".lithecourse-enrollment-action, .lithecourse-enrollment-button-wrap"
        )?.querySelector(
          ".lithecourse-enrollment-status, .lithecourse-enrollment-status-message"
        );

      // Disable button and show loading status
      this.disabled = true;
      if (statusElement) {
        statusElement.textContent = "Enrolling...";
        statusElement.style.display = "block";
      }

      // Get REST API configuration from wp_localize_script data
      const restConfig = window.litheCourseEnrollment;

      if (!restConfig || !restConfig.apiUrl) {
        console.error(
          "ERROR: litheCourseEnrollment object or apiUrl is not defined!"
        );
        alert(
          "Enrollment system configuration error. Please contact the administrator."
        );
        return;
      }

      // Send enrollment request to the REST API
      fetch(restConfig.apiUrl + "/enroll/" + courseId, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": restConfig.nonce,
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
          if (statusElement) {
            statusElement.textContent = "Successfully enrolled!";
          }

          // Handle different block types
          const buttonContainer = this.closest(
            ".lithecourse-enrollment-action, .lithecourse-enrollment-button-wrap"
          );

          if (
            buttonContainer &&
            buttonContainer.classList.contains("lithecourse-enrollment-action")
          ) {
            // For the course-enrollment block
            const enrollmentStatus =
              buttonContainer.parentElement.querySelector(
                ".lithecourse-enrollment-status"
              );
            if (enrollmentStatus) {
              enrollmentStatus.innerHTML =
                '<div class="lithecourse-enrollment-message lithecourse-enrolled">' +
                '<span class="dashicons dashicons-yes-alt"></span>' +
                "You are enrolled in this course" +
                "</div>";
            }
          }

          // After short delay, reload the page to show updated enrollment status
          setTimeout(() => {
            location.reload();
          }, 1500);
        })
        .catch((error) => {
          // Show error message
          if (statusElement) {
            statusElement.textContent =
              error.message || "Error enrolling in course";
          }
          this.disabled = false;

          // Hide error message after 3 seconds
          setTimeout(() => {
            if (statusElement) {
              statusElement.style.display = "none";
            }
          }, 3000);
        });
    });
  });

  // Handle unenroll button clicks in admin
  const unenrollButtons = document.querySelectorAll(".unenroll-user");

  unenrollButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      const userId = this.dataset.user;
      const courseId = this.dataset.course;

      if (
        !confirm("Are you sure you want to unenroll this user from the course?")
      ) {
        return;
      }

      // Disable button and show loading state
      this.disabled = true;
      this.textContent = "Unenrolling...";

      const restConfig = window.litheCourseEnrollment;

      // Send REST API request to unenroll the user
      fetch(restConfig.apiUrl + "/unenroll", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": restConfig.nonce,
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
          const row = this.closest("tr");
          row.style.transition = "opacity 0.3s";
          row.style.opacity = "0";

          setTimeout(() => {
            row.remove();

            // Update the enrollment count
            const enrollmentCountElement = document.querySelector(
              ".lithecourse-admin-enrollment-count strong"
            );
            if (enrollmentCountElement) {
              let count = parseInt(enrollmentCountElement.textContent) - 1;
              enrollmentCountElement.textContent = count;

              // Update the text for singular/plural
              const enrollmentTextElement = document.querySelector(
                ".lithecourse-admin-enrollment-count"
              );
              if (enrollmentTextElement) {
                if (count === 1) {
                  enrollmentTextElement.innerHTML =
                    "<p>There is <strong>1 user</strong> enrolled in this course.</p>";
                } else {
                  enrollmentTextElement.innerHTML =
                    "<p>There are <strong>" +
                    count +
                    " users</strong> enrolled in this course.</p>";
                }
              }

              // If no users left, show "no users" message
              if (count === 0) {
                const listTable = document.querySelector(".wp-list-table");
                if (listTable) {
                  listTable.remove();
                }
                const enrollmentsContainer = document.querySelector(
                  ".lithecourse-admin-enrollments"
                );
                if (enrollmentsContainer) {
                  enrollmentsContainer.innerHTML +=
                    "<p>No users are enrolled in this course yet.</p>";
                }
              }
            }
          }, 300);
        })
        .catch((error) => {
          // Re-enable button and show error
          this.disabled = false;
          this.textContent = "Unenroll";
          alert("Error: " + error.message);
        });
    });
  });
});
