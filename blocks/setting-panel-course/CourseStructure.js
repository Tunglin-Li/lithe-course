import {
  Card,
  CardBody,
  Button,
  Notice,
  Spinner,
  PanelBody,
  Flex,
  FlexItem,
} from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import { createPortal } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { useSelect } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";
import {
  DndContext,
  DragOverlay,
  closestCenter,
  pointerWithin,
  rectIntersection,
  getFirstCollision,
  useSensors,
  useSensor,
  MouseSensor,
  TouchSensor,
  KeyboardSensor,
  MeasuringStrategy,
} from "@dnd-kit/core";
import {
  SortableContext,
  verticalListSortingStrategy,
  arrayMove,
} from "@dnd-kit/sortable";
import ModuleItem from "./components/ModuleItem";
import AddModuleModal from "./components/AddModuleModal";

// Main CourseStructure Component
export default function CourseStructure() {
  const [modules, setModules] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showAddModule, setShowAddModule] = useState(false);
  const [allExpanded, setAllExpanded] = useState(true);

  // Drag overlay state
  const [activeId, setActiveId] = useState(null);
  const [activeLesson, setActiveLesson] = useState(null);
  const [activeModule, setActiveModule] = useState(null);
  const [clonedModules, setClonedModules] = useState(null);

  // Effect to handle smooth transitions after cross-module moves
  useEffect(() => {
    requestAnimationFrame(() => {
      // This helps with smooth animations after cross-module moves
    });
  }, [modules]);

  // Sensors configuration
  const sensors = useSensors(
    useSensor(MouseSensor),
    useSensor(TouchSensor),
    useSensor(KeyboardSensor)
  );

  // Get the current post ID from the editor
  const postId = useSelect((select) => {
    const editor = select("core/editor");
    if (!editor) return null;
    return editor.getCurrentPostId();
  }, []);

  // Load course structure on component mount
  useEffect(() => {
    if (postId) {
      loadCourseStructure();
    }
  }, [postId]);

  const loadCourseStructure = async () => {
    if (!postId) return;

    setLoading(true);
    setError(null);

    try {
      const response = await apiFetch({
        path: `/lithe-course/v1/course/${postId}/structure`,
      });
      setModules(response);
    } catch (error) {
      console.error("Error loading course structure:", error);
      setError(
        __(
          "Failed to load course structure. Please refresh the page and try again.",
          "lithe-course"
        )
      );
    } finally {
      setLoading(false);
    }
  };

  const handleAddModule = (newModule) => {
    setModules([...modules, newModule]);
  };

  const handleUpdateModule = (moduleId, updatedModule) => {
    setModules(
      modules.map((module) => (module.id === moduleId ? updatedModule : module))
    );
  };

  const handleDeleteModule = (moduleId) => {
    setModules(modules.filter((module) => module.id !== moduleId));
  };

  const handleExpandAll = () => {
    setAllExpanded(true);
  };

  const handleCollapseAll = () => {
    setAllExpanded(false);
  };

  // Helper function to check if an ID is a module
  const isModule = (id) => {
    return id.toString().startsWith("module-");
  };

  // Helper function to get module ID from the prefixed string
  const getModuleIdFromString = (id) => {
    return parseInt(id.toString().replace("module-", ""));
  };

  // Helper function to find which module contains a lesson
  const findContainer = (lessonId) => {
    for (const module of modules) {
      if (module.lessons?.some((lesson) => lesson.id === lessonId)) {
        return module.id;
      }
    }
    return null;
  };

  // Helper function to find a lesson across all modules
  const findLesson = (lessonId) => {
    for (const module of modules) {
      const lesson = module.lessons?.find((l) => l.id === lessonId);
      if (lesson) {
        return lesson;
      }
    }
    return null;
  };

  // Helper function to find a module by ID
  const findModule = (moduleId) => {
    const actualModuleId = isModule(moduleId)
      ? getModuleIdFromString(moduleId)
      : moduleId;
    return modules.find((m) => m.id === actualModuleId);
  };

  // Custom collision detection strategy optimized for multiple containers
  const collisionDetectionStrategy = (args) => {
    // Handle module dragging
    if (activeId && isModule(activeId)) {
      return closestCenter({
        ...args,
        droppableContainers: args.droppableContainers.filter((container) => {
          const id = container.id;
          // Only include other modules for module-to-module collision
          return isModule(id);
        }),
      });
    }

    // Handle lesson dragging (existing logic)
    if (activeId && findContainer(activeId)) {
      return closestCenter({
        ...args,
        droppableContainers: args.droppableContainers.filter((container) => {
          const id = container.id;
          // Include module containers and lesson items
          return id.toString().startsWith("module-") || findContainer(id);
        }),
      });
    }

    // Start by finding any intersecting droppable
    const pointerIntersections = pointerWithin(args);
    const intersections =
      pointerIntersections.length > 0
        ? pointerIntersections
        : rectIntersection(args);

    let overId = getFirstCollision(intersections, "id");

    if (overId != null) {
      // If dropping on a module container, find the closest lesson within that module
      if (overId.toString().startsWith("module-")) {
        const moduleId = parseInt(overId.toString().replace("module-", ""));
        const module = modules.find((m) => m.id === moduleId);

        if (module && module.lessons && module.lessons.length > 0) {
          // Return the closest lesson within the module
          overId =
            closestCenter({
              ...args,
              droppableContainers: args.droppableContainers.filter(
                (container) =>
                  module.lessons.some((lesson) => lesson.id === container.id)
              ),
            })[0]?.id || overId;
        }
      }

      return [{ id: overId }];
    }

    return [];
  };

  const handleDragStart = (event) => {
    const { active } = event;
    const activeIdValue = active.id;

    setActiveId(activeIdValue);
    setClonedModules(modules);

    if (isModule(activeIdValue)) {
      // Dragging a module
      const module = findModule(activeIdValue);
      setActiveModule(module);
      setActiveLesson(null);
    } else {
      // Dragging a lesson
      const lesson = findLesson(activeIdValue);
      setActiveLesson(lesson);
      setActiveModule(null);
    }
  };

  const handleDragOver = (event) => {
    const { active, over } = event;

    if (!over) {
      return;
    }

    const overId = over.id;
    const activeId = active.id;

    // Handle module dragging
    if (isModule(activeId)) {
      return; // Module reordering is handled in handleDragEnd
    }

    // Handle lesson dragging (existing logic)
    // Don't handle if dragging a module container
    if (activeId.toString().startsWith("module-")) {
      return;
    }

    const activeContainer = findContainer(activeId);
    let overContainer = findContainer(overId);

    // Check if dropping on a module container
    if (overId.toString().startsWith("module-")) {
      overContainer = parseInt(overId.toString().replace("module-", ""));
    }

    if (!activeContainer || !overContainer) {
      return;
    }

    if (activeContainer !== overContainer) {
      // Cross-module move during drag
      setModules((prevModules) => {
        const activeModule = prevModules.find((m) => m.id === activeContainer);
        const overModule = prevModules.find((m) => m.id === overContainer);

        if (!activeModule || !overModule) {
          return prevModules;
        }

        const activeLessons = activeModule.lessons || [];
        const overLessons = overModule.lessons || [];

        const activeIndex = activeLessons.findIndex(
          (lesson) => lesson.id === activeId
        );
        const lessonToMove = activeLessons[activeIndex];

        if (!lessonToMove) {
          return prevModules;
        }

        let newIndex = overLessons.length;

        // If dropping on a specific lesson, calculate proper position
        if (!overId.toString().startsWith("module-")) {
          const overIndex = overLessons.findIndex(
            (lesson) => lesson.id === overId
          );
          if (overIndex !== -1) {
            // Check if we should insert above or below the target lesson
            const isBelowOverItem =
              over &&
              active.rect.current.translated &&
              active.rect.current.translated.top >
                over.rect.top + over.rect.height;

            newIndex = isBelowOverItem ? overIndex + 1 : overIndex;
          }
        }

        return prevModules.map((module) => {
          if (module.id === activeContainer) {
            return {
              ...module,
              lessons: module.lessons.filter(
                (lesson) => lesson.id !== activeId
              ),
            };
          } else if (module.id === overContainer) {
            const newLessons = [...overLessons];
            newLessons.splice(newIndex, 0, lessonToMove);
            return {
              ...module,
              lessons: newLessons,
            };
          }
          return module;
        });
      });
    } else if (activeId !== overId) {
      // Intra-module reordering during drag
      setModules((prevModules) => {
        return prevModules.map((module) => {
          if (module.id === activeContainer) {
            const lessons = module.lessons || [];
            const oldIndex = lessons.findIndex(
              (lesson) => lesson.id === activeId
            );
            const newIndex = lessons.findIndex(
              (lesson) => lesson.id === overId
            );

            if (oldIndex !== -1 && newIndex !== -1) {
              const newLessons = arrayMove(lessons, oldIndex, newIndex);
              return {
                ...module,
                lessons: newLessons,
              };
            }
          }
          return module;
        });
      });
    }
  };

  const handleDragEnd = async (event) => {
    const { active, over } = event;
    const activeId = active.id;

    // Reset drag state
    setActiveId(null);
    setActiveLesson(null);
    setActiveModule(null);

    if (!over) {
      setClonedModules(null);
      return;
    }

    const overId = over.id;

    try {
      // Handle module reordering
      if (isModule(activeId) && isModule(overId)) {
        const activeModuleId = getModuleIdFromString(activeId);
        const overModuleId = getModuleIdFromString(overId);

        if (activeModuleId !== overModuleId) {
          setModules((items) => {
            const oldIndex = items.findIndex(
              (item) => item.id === activeModuleId
            );
            const newIndex = items.findIndex(
              (item) => item.id === overModuleId
            );

            const newOrder = arrayMove(items, oldIndex, newIndex);

            // Save module order to server
            const moduleOrder = newOrder.map((module) => module.id);
            apiFetch({
              path: `/lithe-course/v1/course/${postId}/module-order`,
              method: "PUT",
              data: {
                module_order: moduleOrder,
              },
            }).catch((error) => {
              console.error("Error saving module order:", error);
              alert(
                __(
                  "Failed to save module order. Please try again.",
                  "lithe-course"
                )
              );
              // Reload to revert changes
              loadCourseStructure();
            });

            return newOrder;
          });
        }
        setClonedModules(null);
        return;
      }

      // Handle lesson dragging (existing logic)
      const activeContainer = findContainer(activeId);
      let overContainer = findContainer(overId);

      // Check if dropping on a module container
      if (overId.toString().startsWith("module-")) {
        overContainer = parseInt(overId.toString().replace("module-", ""));
      }

      // Get the original lesson order before any changes for comparison
      const originalModule = clonedModules?.find((m) =>
        m.lessons?.some((lesson) => lesson.id === activeId)
      );
      const originalLessonOrder =
        originalModule?.lessons?.map((lesson) => lesson.id) || [];

      if (!activeContainer || !overContainer) {
        setClonedModules(null);
        return;
      }

      // For cross-module detection, we need to check the original container from clonedModules
      const originalActiveContainer = clonedModules?.find((m) =>
        m.lessons?.some((lesson) => lesson.id === activeId)
      )?.id;

      // Use original container for cross-module detection since activeContainer may have changed during drag
      const isCrossModuleMove =
        originalActiveContainer && originalActiveContainer !== overContainer;

      if (isCrossModuleMove) {
        // Cross-module move - save to server
        const targetModule = modules.find((m) => m.id === overContainer);
        const lessonIndex =
          targetModule?.lessons?.findIndex(
            (lesson) => lesson.id === activeId
          ) || 0;

        await apiFetch({
          path: `/lithe-course/v1/lesson/${activeId}/move`,
          method: "PUT",
          data: {
            module_id: overContainer,
            position: lessonIndex,
          },
          headers: {
            "X-WP-Nonce": window.wpApiSettings?.nonce || "",
          },
        });
      } else {
        // Same module - check if lesson order has actually changed
        const currentModule = modules.find((m) => m.id === activeContainer);
        if (currentModule && currentModule.lessons) {
          const currentLessonOrder = currentModule.lessons.map(
            (lesson) => lesson.id
          );

          // Check if the order has actually changed
          const orderChanged =
            JSON.stringify(originalLessonOrder) !==
            JSON.stringify(currentLessonOrder);

          if (orderChanged) {
            await apiFetch({
              path: `/lithe-course/v1/module/${activeContainer}/lesson-order`,
              method: "PUT",
              data: {
                lesson_order: currentLessonOrder,
              },
            });
          }
        }
      }
    } catch (error) {
      console.error("Detailed error saving changes:", error);
      console.error("Error message:", error.message);
      console.error("Error code:", error.code);
      console.error("Error data:", error.data);
      alert(__("Failed to save changes. Please try again.", "lithe-course"));
      // Reload to revert changes
      loadCourseStructure();
    }

    setClonedModules(null);
  };

  const handleDragCancel = () => {
    if (clonedModules) {
      // Reset modules to their original state in case lessons have been
      // dragged across modules during the drag operation
      setModules(clonedModules);
    }

    setActiveId(null);
    setActiveLesson(null);
    setActiveModule(null);
    setClonedModules(null);
  };

  if (loading) {
    return (
      <div style={{ textAlign: "center", padding: "20px" }}>
        <Spinner />
        <p>{__("Loading course structure...", "lithe-course")}</p>
      </div>
    );
  }

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={collisionDetectionStrategy}
      measuring={{
        droppable: {
          strategy: MeasuringStrategy.Always,
        },
      }}
      onDragStart={handleDragStart}
      onDragOver={handleDragOver}
      onDragEnd={handleDragEnd}
      onDragCancel={handleDragCancel}
    >
      <PanelBody>
        <div className="lithe-course-structure">
          {error && (
            <Notice status="error" isDismissible={false}>
              {error}
            </Notice>
          )}

          {modules.length > 0 && (
            <div style={{ marginBottom: "16px" }}>
              <Flex gap={2} justify="space-between">
                <Flex gap={2}>
                  <FlexItem>
                    <Button size="small" onClick={handleExpandAll}>
                      {__("Expand All", "lithe-course")}
                    </Button>
                  </FlexItem>
                  <FlexItem>
                    <Button size="small" onClick={handleCollapseAll}>
                      {__("Collapse All", "lithe-course")}
                    </Button>
                  </FlexItem>
                </Flex>
              </Flex>
            </div>
          )}

          {/* Sortable Context for module cards */}
          <SortableContext
            items={modules.map((module) => `module-${module.id}`)}
            strategy={verticalListSortingStrategy}
          >
            <div style={{ marginBottom: "16px" }}>
              {modules.length > 0 ? (
                modules.map((module) => (
                  // Draggable for each module
                  <ModuleItem
                    key={module.id}
                    module={module}
                    onUpdate={handleUpdateModule}
                    onDelete={handleDeleteModule}
                    forceExpanded={allExpanded}
                  />
                ))
              ) : (
                <Card>
                  <CardBody>
                    <p
                      style={{
                        textAlign: "center",
                        color: "#666",
                        fontStyle: "italic",
                        margin: 0,
                      }}
                    >
                      {__(
                        "No modules found. Add a new module to get started.",
                        "lithe-course"
                      )}
                    </p>
                  </CardBody>
                </Card>
              )}
            </div>
          </SortableContext>

          <Button variant="primary" onClick={() => setShowAddModule(true)}>
            {__("Add New Module", "lithe-course")}
          </Button>

          <AddModuleModal
            courseId={postId}
            isOpen={showAddModule}
            onClose={() => setShowAddModule(false)}
            onAdd={handleAddModule}
          />
        </div>

        {/* Global Drag Overlay */}
        {createPortal(
          <DragOverlay>
            {activeId && activeModule ? (
              <Card
                style={{
                  marginBottom: "16px",
                  border: "2px solid #007cba",
                  boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
                  cursor: "grabbing",
                }}
              >
                <div
                  style={{
                    padding: "8px",
                    fontSize: "12px",
                    fontWeight: "500",
                  }}
                >
                  {activeModule.title}
                </div>
              </Card>
            ) : activeId && activeLesson ? (
              <div
                style={{
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "space-between",
                  padding: "10px",
                  border: "2px solid #007cba",
                  borderRadius: "4px",
                  backgroundColor: "#ffffff",
                  boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
                  minWidth: "200px",
                  cursor: "grabbing",
                }}
              >
                <div
                  style={{
                    flex: 1,
                    fontSize: "12px",
                    lineHeight: "1.4",
                    fontWeight: "500",
                  }}
                >
                  <span>{activeLesson.title}</span>
                </div>
              </div>
            ) : null}
          </DragOverlay>,
          document.body
        )}
      </PanelBody>
    </DndContext>
  );
}
