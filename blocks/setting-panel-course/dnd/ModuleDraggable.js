import { __ } from "@wordpress/i18n";
import { useSortable } from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";

export default function ModuleDraggable({ module, children }) {
  const {
    attributes,
    listeners,
    setNodeRef,
    transform,
    transition,
    isDragging,
  } = useSortable({
    id: `module-${module.id}`,
    data: {
      type: "module",
      module,
    },
  });

  const style = {
    transform: CSS.Transform.toString(transform),
    transition,
    opacity: isDragging ? 0.5 : 1,
  };

  return (
    <div ref={setNodeRef} style={style}>
      {children({
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
          title: __("Drag to reorder module", "lithe-course"),
        },
        isDragging,
      })}
    </div>
  );
}
