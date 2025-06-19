import React from "react";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import { __ } from "@wordpress/i18n";

export default function Draggable({
  id,
  children,
  data,
  style = {},
  ...props
}) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({
    id: id,
    data: data,
  });

  const sortableStyle = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
    ...style,
  };

  return (
    <div ref={setNodeRef} style={sortableStyle} {...props}>
      {typeof children === "function"
        ? children({
            dragHandleProps: {
              ...attributes,
              ...listeners,
              style: {
                cursor: isDragging ? "grabbing" : "grab",
                display: "flex",
                alignItems: "center",
                padding: "2px",
                color: "#666",
                borderRadius: "2px",
                transition: "background-color 0.2s",
              },
              title: __("Drag to reorder lesson", "lithe-course"),
            },
            isDragging,
          })
        : children}
    </div>
  );
}
