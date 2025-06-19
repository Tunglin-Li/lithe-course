import React from "react";
import { useDroppable } from "@dnd-kit/core";

export default function Droppable({ id, children, style = {}, ...props }) {
  const { isOver, setNodeRef } = useDroppable({
    id: id,
  });

  const droppableStyle = {
    ...style,
    ...(isOver && {}),
  };

  return (
    <div ref={setNodeRef} style={droppableStyle} {...props}>
      {children}
    </div>
  );
}
