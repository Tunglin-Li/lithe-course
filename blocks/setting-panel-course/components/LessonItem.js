import { Icon, Button } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import { dragHandle } from "@wordpress/icons";

export default function LessonItem({
  lesson,
  moduleId,
  onDelete,
  dragHandleProps,
  isDragging,
}) {
  const [isDeleting, setIsDeleting] = useState(false);

  // Helper function to decode HTML entities
  const decodeHtmlEntities = (str) => {
    const textarea = document.createElement("textarea");
    textarea.innerHTML = str;
    return textarea.value;
  };

  const handleDelete = async (e) => {
    // Stop event propagation to prevent dragging when clicking delete
    e.stopPropagation();

    if (
      !confirm(
        __("Are you sure you want to delete this lesson?", "lithe-course")
      )
    ) {
      return;
    }

    setIsDeleting(true);
    try {
      await apiFetch({
        path: `/lithe-course/v1/lesson/${lesson.id}`,
        method: "DELETE",
      });
      onDelete(lesson.id);
    } catch (error) {
      console.error("Error deleting lesson:", error);
      alert(__("Failed to delete lesson. Please try again.", "lithe-course"));
    } finally {
      setIsDeleting(false);
    }
  };

  const handleEdit = (e) => {
    // Stop event propagation to prevent dragging when clicking edit
    e.stopPropagation();
  };

  return (
    <div
      style={{
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",
        padding: "10px",
        border: "1px solid #ddd",
        borderRadius: "4px",
        marginBottom: "8px",
        backgroundColor: "#f9f9f9",
        transition: "all 0.2s ease",
        gap: "2px",
      }}
    >
      {/* Left side - Drag handle */}
      <div
        style={{
          display: "flex",
          alignItems: "center",
        }}
      >
        {/* Drag handle */}
        <div {...dragHandleProps}>
          <Icon icon={dragHandle} size={16} />
        </div>
      </div>

      {/* Lesson title */}
      <div
        style={{
          flex: 1,
          fontSize: "12px",
          lineHeight: "1.4",
          minWidth: 0, // Prevents flex item from overflowing
        }}
      >
        <span>{lesson.title}</span>
      </div>

      {/* Right side - Edit and delete icons */}
      <div
        style={{
          display: "flex",
          alignItems: "center",
          gap: "2px",
        }}
      >
        <div>
          <Button
            icon="edit"
            label={__("Edit", "lithe-course")}
            href={decodeHtmlEntities(lesson.edit_link)}
            disabled={isDeleting}
            size="small"
            onClick={handleEdit}
            style={{
              minWidth: "auto",
              padding: "4px",
              width: "24px",
              height: "24px",
            }}
          />
        </div>
        <div>
          <Button
            icon="trash"
            label={__("Delete", "lithe-course")}
            onClick={handleDelete}
            disabled={isDeleting}
            size="small"
            isDestructive
            style={{
              minWidth: "auto",
              padding: "4px",
              width: "24px",
              height: "24px",
            }}
          />
        </div>
      </div>
    </div>
  );
}
