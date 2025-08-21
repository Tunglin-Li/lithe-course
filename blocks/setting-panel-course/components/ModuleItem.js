import {
  Card,
  CardHeader,
  CardBody,
  Button,
  TextControl,
  Icon,
} from "@wordpress/components";
import { dragHandle } from "@wordpress/icons";
import { useState, useEffect } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";
import LessonItem from "./LessonItem";
import AddLessonForm from "./AddLessonForm";
import {
  SortableContext,
  verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import Droppable from "../dnd/Droppable.js";
import Draggable from "../dnd/LessonDraggable.js";
import ModuleDraggable from "../dnd/ModuleDraggable.js";

export default function ModuleItem({
  module,
  onUpdate,
  onDelete,
  forceExpanded,
  onExpandedChange,
  isModuleDraggingDisabled,
}) {
  const [isExpanded, setIsExpanded] = useState(true);
  const [isEditing, setIsEditing] = useState(false);
  const [editTitle, setEditTitle] = useState(module.title);
  const [isUpdating, setIsUpdating] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);
  const [showAddLesson, setShowAddLesson] = useState(false);

  // Use lessons directly from props instead of local state to avoid conflicts
  const lessons = module.lessons || [];

  // Sync with forceExpanded prop
  useEffect(() => {
    if (forceExpanded !== undefined) {
      setIsExpanded(forceExpanded);
    }
  }, [forceExpanded]);

  // Notify parent when expanded state changes
  useEffect(() => {
    if (onExpandedChange) {
      onExpandedChange(module.id, isExpanded);
    }
  }, [isExpanded, module.id, onExpandedChange]);

  const handleSaveTitle = async () => {
    if (!editTitle.trim()) {
      alert(__("Module title is required.", "lithe-course"));
      return;
    }

    setIsUpdating(true);
    try {
      await apiFetch({
        path: `/lithecourse/v1/module/${module.id}`,
        method: "PUT",
        data: {
          title: editTitle.trim(),
        },
      });
      onUpdate(module.id, { ...module, title: editTitle.trim() });
      setIsEditing(false);
    } catch (error) {
      console.error("Error updating module:", error);
      alert(
        __("Failed to update module title. Please try again.", "lithe-course")
      );
    } finally {
      setIsUpdating(false);
    }
  };

  const handleCancelEdit = () => {
    setEditTitle(module.title);
    setIsEditing(false);
  };

  const handleDelete = async () => {
    if (
      !confirm(
        __(
          "Are you sure you want to delete this module? This will also delete all lessons within this module.",
          "lithe-course"
        )
      )
    ) {
      return;
    }

    setIsDeleting(true);
    try {
      await apiFetch({
        path: `/lithecourse/v1/module/${module.id}`,
        method: "DELETE",
      });
      onDelete(module.id);
    } catch (error) {
      console.error("Error deleting module:", error);
      alert(__("Failed to delete module. Please try again.", "lithe-course"));
    } finally {
      setIsDeleting(false);
    }
  };

  const handleAddLesson = (newLesson) => {
    // Update the parent's module state instead of local state
    const updatedModule = {
      ...module,
      lessons: [...lessons, newLesson],
    };
    onUpdate(module.id, updatedModule);
  };

  const handleDeleteLesson = (lessonId) => {
    // Update the parent's module state instead of local state
    const updatedModule = {
      ...module,
      lessons: lessons.filter((lesson) => lesson.id !== lessonId),
    };
    onUpdate(module.id, updatedModule);
  };

  const renderModuleContent = (dragHandleProps = {}, isDragging = false) => {
    return (
      <Card style={{ marginBottom: "16px" }}>
        <CardHeader style={{ padding: "8px" }}>
          <div
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              width: "100%",
            }}
          >
            {/* Left side - Drag handle and Expand icon */}
            <div
              style={{
                display: "flex",
                alignItems: "center",
                marginRight: "8px",
              }}
            >
              {/* Drag handle - only show when collapsed AND no modules are expanded */}
              {!isExpanded && !isModuleDraggingDisabled && (
                <div
                  {...dragHandleProps}
                  style={{
                    cursor: isDragging ? "grabbing" : "grab",
                    display: "flex",
                    alignItems: "center",
                    padding: "2px",
                    color: "#666",
                    borderRadius: "2px",
                    transition: "background-color 0.2s",
                    zIndex: 1,
                  }}
                  title={__("Drag to reorder module", "lithe-course")}
                >
                  <Icon icon={dragHandle} size={16} />
                </div>
              )}

              {/* Expand/collapse button */}
              <Button
                icon={isExpanded ? "arrow-down" : "arrow-right"}
                onClick={() => setIsExpanded(!isExpanded)}
                size="small"
                style={{
                  minWidth: "auto",
                  padding: "2px",
                  width: "20px",
                  height: "20px",
                }}
              />
            </div>

            {/* Middle - Title (takes up remaining space) */}
            <div
              style={{
                flex: 1,
                minWidth: 0, // Prevents flex item from overflowing
              }}
            >
              {isEditing ? (
                <div style={{ flex: 1 }}>
                  <TextControl
                    value={editTitle}
                    onChange={setEditTitle}
                    disabled={isUpdating}
                    style={{ margin: 0 }}
                    className="lithecourse-module-title-input"
                  />
                </div>
              ) : (
                <div
                  style={{
                    margin: 0,
                    fontWeight: "500",
                    fontSize: "12px",
                    overflowWrap: "break-word",
                  }}
                >
                  {module.title}
                </div>
              )}
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
                  icon={isEditing ? "saved" : "edit"}
                  label={
                    isEditing
                      ? __("Save", "lithe-course")
                      : __("Edit", "lithe-course")
                  }
                  onClick={
                    isEditing ? handleSaveTitle : () => setIsEditing(true)
                  }
                  disabled={
                    isDeleting ||
                    (isEditing && (!editTitle.trim() || isUpdating))
                  }
                  size="small"
                  style={{
                    minWidth: "auto",
                    padding: "4px",
                    width: "24px",
                    height: "24px",
                  }}
                />
              </div>
              {!isEditing && (
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
              )}
            </div>
          </div>
        </CardHeader>

        {isExpanded && (
          <CardBody style={{ padding: "10px" }}>
            <div style={{ marginBottom: "12px" }}>
              <strong>{__("Lessons:", "lithe-course")}</strong>
            </div>
            {/* Droppable for lesson list */}
            <Droppable id={`module-${module.id}`}>
              {/* SortableContext for lesson list */}
              <SortableContext
                items={lessons.map((lesson) => lesson.id)}
                strategy={verticalListSortingStrategy}
              >
                <div
                  style={{
                    minHeight: "40px",
                    borderRadius: "4px",
                  }}
                >
                  {lessons.length > 0 ? (
                    lessons.map((lesson) => (
                      // Draggable for each lesson
                      <Draggable key={lesson.id} id={lesson.id}>
                        {({ dragHandleProps, isDragging }) => (
                          <LessonItem
                            lesson={lesson}
                            moduleId={module.id}
                            onDelete={handleDeleteLesson}
                            dragHandleProps={dragHandleProps}
                            isDragging={isDragging}
                          />
                        )}
                      </Draggable>
                    ))
                  ) : (
                    <p style={{ fontStyle: "italic", color: "#666" }}>
                      {__(
                        "No lessons found. Add a new lesson to get started.",
                        "lithe-course"
                      )}
                    </p>
                  )}
                </div>
              </SortableContext>
            </Droppable>

            {showAddLesson ? (
              <AddLessonForm
                moduleId={module.id}
                onAdd={handleAddLesson}
                onCancel={() => setShowAddLesson(false)}
              />
            ) : (
              <Button
                variant="secondary"
                onClick={() => setShowAddLesson(true)}
                style={{ marginTop: "8px" }}
              >
                {__("Add New Lesson", "lithe-course")}
              </Button>
            )}
          </CardBody>
        )}
      </Card>
    );
  };

  // Conditionally wrap with ModuleDraggable only when collapsed AND no modules are expanded
  if (!isExpanded && !isModuleDraggingDisabled) {
    return (
      <ModuleDraggable module={module}>
        {({ dragHandleProps, isDragging }) =>
          renderModuleContent(dragHandleProps, isDragging)
        }
      </ModuleDraggable>
    );
  }

  // When expanded or when any module is expanded, render without drag functionality
  return renderModuleContent();
}
