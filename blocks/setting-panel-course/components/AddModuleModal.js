import { Button, TextControl, Modal, Spinner } from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

export default function AddModuleModal({ courseId, isOpen, onClose, onAdd }) {
  const [title, setTitle] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!title.trim()) {
      alert(__("Module title is required.", "lithe-course"));
      return;
    }

    setIsSubmitting(true);
    try {
      const response = await apiFetch({
        path: "/lithe-course/v1/module",
        method: "POST",
        data: {
          course_id: courseId,
          title: title.trim(),
        },
      });
      onAdd(response);
      setTitle("");
      onClose();
    } catch (error) {
      console.error("Error creating module:", error);
      alert(__("Failed to create module. Please try again.", "lithe-course"));
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    isOpen && (
      <Modal
        title={__("Add New Module", "lithe-course")}
        onRequestClose={() => !isSubmitting && onClose()}
      >
        <form onSubmit={handleSubmit}>
          <TextControl
            label={__("Module Title", "lithe-course")}
            placeholder={__("Enter module title", "lithe-course")}
            value={title}
            onChange={setTitle}
            disabled={isSubmitting}
          />
          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: "8px",
              marginTop: "16px",
            }}
          >
            <div>
              <Button
                variant="primary"
                type="submit"
                disabled={isSubmitting || !title.trim()}
              >
                {isSubmitting ? <Spinner /> : __("Add Module", "lithe-course")}
              </Button>
            </div>
            <div>
              <Button
                variant="secondary"
                onClick={onClose}
                disabled={isSubmitting}
              >
                {__("Cancel", "lithe-course")}
              </Button>
            </div>
          </div>
        </form>
      </Modal>
    )
  );
}
