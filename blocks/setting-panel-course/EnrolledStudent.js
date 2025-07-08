import { PanelRow, Button, Modal, Spinner } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";

export default function EnrolledStudent() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [studentsData, setStudentsData] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState(null);

  // Get current post ID
  const postId = useSelect(
    (select) => select("core/editor").getCurrentPostId(),
    []
  );

  const handleViewStudents = async () => {
    if (!postId) {
      setError(__("No course ID found.", "lithe-course"));
      return;
    }

    setIsModalOpen(true);
    setIsLoading(true);
    setError(null);

    try {
      const response = await apiFetch({
        path: `/lithe-course/v1/course/${postId}/students`,
        method: "GET",
      });

      if (response.success) {
        setStudentsData(response.data);
      } else {
        setError(__("Failed to load students data.", "lithe-course"));
      }
    } catch (err) {
      console.error("Error fetching students:", err);
      setError(__("Failed to load students data.", "lithe-course"));
    } finally {
      setIsLoading(false);
    }
  };

  const handleUnenroll = async (userId) => {
    if (
      !confirm(
        __("Are you sure you want to unenroll this user?", "lithe-course")
      )
    ) {
      return;
    }

    try {
      const response = await apiFetch({
        path: "/lithe-course/v1/unenroll",
        method: "POST",
        data: {
          user_id: userId,
          course_id: postId,
        },
      });

      if (response.success) {
        // Refresh the students data
        handleViewStudents();
      } else {
        alert(__("Failed to unenroll user.", "lithe-course"));
      }
    } catch (err) {
      console.error("Error unenrolling user:", err);
      alert(__("Failed to unenroll user.", "lithe-course"));
    }
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setStudentsData(null);
    setError(null);
  };

  return (
    <>
      <PanelRow>
        <Button variant="primary" onClick={handleViewStudents}>
          {__("View Students", "lithe-course")}
        </Button>
      </PanelRow>

      {isModalOpen && (
        <Modal
          title={__("Enrolled Students", "lithe-course")}
          onRequestClose={closeModal}
          size="large"
        >
          <div style={{ minHeight: "300px" }}>
            {isLoading && (
              <div
                style={{
                  display: "flex",
                  justifyContent: "center",
                  alignItems: "center",
                  height: "200px",
                }}
              >
                <Spinner />
                <span style={{ marginLeft: "8px" }}>
                  {__("Loading students...", "lithe-course")}
                </span>
              </div>
            )}

            {error && (
              <div
                style={{
                  padding: "16px",
                  backgroundColor: "#f9f9f9",
                  border: "1px solid #ddd",
                  borderRadius: "4px",
                }}
              >
                <p style={{ color: "#d63638", margin: 0 }}>{error}</p>
              </div>
            )}

            {studentsData && !isLoading && (
              <div>
                <div style={{ marginBottom: "16px" }}>
                  <p>
                    {studentsData.enrollment_count === 0
                      ? __(
                          "No users are enrolled in this course yet.",
                          "lithe-course"
                        )
                      : studentsData.enrollment_count === 1
                      ? __(
                          "There is 1 user enrolled in this course.",
                          "lithe-course"
                        )
                      : __(
                          `There are ${studentsData.enrollment_count} users enrolled in this course.`,
                          "lithe-course"
                        )}
                  </p>
                </div>

                {studentsData.enrolled_users.length > 0 && (
                  <table
                    style={{
                      width: "100%",
                      borderCollapse: "collapse",
                      border: "1px solid #ddd",
                    }}
                  >
                    <thead>
                      <tr style={{ backgroundColor: "#f9f9f9" }}>
                        <th
                          style={{
                            padding: "12px",
                            border: "1px solid #ddd",
                            textAlign: "left",
                          }}
                        >
                          {__("User", "lithe-course")}
                        </th>
                        <th
                          style={{
                            padding: "12px",
                            border: "1px solid #ddd",
                            textAlign: "left",
                          }}
                        >
                          {__("Email", "lithe-course")}
                        </th>
                        <th
                          style={{
                            padding: "12px",
                            border: "1px solid #ddd",
                            textAlign: "left",
                          }}
                        >
                          {__("Actions", "lithe-course")}
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      {studentsData.enrolled_users.map((user) => (
                        <tr key={user.id}>
                          <td
                            style={{
                              padding: "12px",
                              border: "1px solid #ddd",
                            }}
                          >
                            {user.display_name} (#{user.id})
                          </td>
                          <td
                            style={{
                              padding: "12px",
                              border: "1px solid #ddd",
                            }}
                          >
                            {user.user_email}
                          </td>
                          <td
                            style={{
                              padding: "12px",
                              border: "1px solid #ddd",
                            }}
                          >
                            <Button
                              variant="secondary"
                              isDestructive
                              size="small"
                              onClick={() => handleUnenroll(user.id)}
                            >
                              {__("Unenroll", "lithe-course")}
                            </Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                )}
              </div>
            )}
          </div>
        </Modal>
      )}
    </>
  );
}
