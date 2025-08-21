import {
  Button,
  TextControl,
  Flex,
  FlexItem,
  Spinner,
} from "@wordpress/components";
import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

export default function AddLessonForm({ moduleId, onAdd, onCancel }) {
  const [title, setTitle] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!title.trim()) {
      alert(__("Lesson title is required.", "lithe-course"));
      return;
    }

    setIsSubmitting(true);
    try {
      const response = await apiFetch({
        path: "/lithecourse/v1/lesson",
        method: "POST",
        data: {
          module_id: moduleId,
          title: title.trim(),
        },
      });
      onAdd(response);
      setTitle("");
      onCancel();
    } catch (error) {
      console.error("Error creating lesson:", error);
      alert(__("Failed to create lesson. Please try again.", "lithe-course"));
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} style={{ marginTop: "12px" }}>
      <TextControl
        placeholder={__("Enter lesson title", "lithe-course")}
        value={title}
        onChange={setTitle}
        disabled={isSubmitting}
      />
      <Flex style={{ marginTop: "8px" }}>
        <FlexItem>
          <Button
            variant="primary"
            type="submit"
            disabled={isSubmitting || !title.trim()}
          >
            {isSubmitting ? <Spinner /> : __("Add Lesson", "lithe-course")}
          </Button>
        </FlexItem>
        <FlexItem>
          <Button
            variant="secondary"
            onClick={onCancel}
            disabled={isSubmitting}
          >
            {__("Cancel", "lithe-course")}
          </Button>
        </FlexItem>
      </Flex>
    </form>
  );
}
