import { useState, useEffect } from "@wordpress/element";
import {
  TextControl,
  Button,
  Modal,
  Flex,
  FlexItem,
  FlexBlock,
  Card,
  PanelRow,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { trash, plus, edit } from "@wordpress/icons";
import { Reorder } from "framer-motion";
import { useEntityProp } from "@wordpress/core-data";
import { useSelect } from "@wordpress/data";

export default function CourseInformation({
  fieldKey,
  title,
  addButtonText,
  placeholder = "Enter description",
  emptyMessage = "No items added yet",
}) {
  // Modal state
  const [isOpen, setOpen] = useState(false);

  const openModal = () => setOpen(true);
  const closeModal = () => setOpen(false);

  // Get current post type
  const currentPostType = useSelect((select) => {
    return select("core/editor").getCurrentPostType();
  }, []);

  // Use the same approach as our block
  const [meta, setMeta] = useEntityProp("postType", currentPostType, "meta");
  const fieldData = meta[fieldKey];

  // Local state for smooth reordering
  const [localItems, setLocalItems] = useState(fieldData || []);

  // Check if we have valid content
  const hasContent =
    localItems &&
    localItems.length > 0 &&
    localItems.some((item) => item.text && item.text.trim() !== "");
  const isEmpty = !hasContent;

  // Sync local state with meta when field data changes
  useEffect(() => {
    // Add IDs to items from WordPress (they don't have IDs in the database)
    const itemsWithIds = (fieldData || []).map((item, index) => ({
      ...item,
      id: item.id || `existing-${index}-${Date.now()}`,
    }));
    setLocalItems(itemsWithIds);
  }, [fieldData]);

  // Sync local state to meta (debounced for performance)
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      // Remove id properties before saving to WordPress (only keep text)
      const cleanItems = localItems.map(({ id, ...item }) => item);
      if (JSON.stringify(cleanItems) !== JSON.stringify(fieldData || [])) {
        setMeta({ ...meta, [fieldKey]: cleanItems });
      }
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [localItems, meta, fieldData, setMeta, fieldKey]);

  // Add a new item
  const addItem = () => {
    const newItems = [...localItems, { text: "", id: Date.now() }];
    setLocalItems(newItems);
  };

  // Remove an item
  const removeItem = (index) => {
    const newItems = [...localItems];
    newItems.splice(index, 1);
    setLocalItems(newItems);
  };

  // Handle reordering (now just updates local state)
  const handleReorder = (newOrder) => {
    setLocalItems(newOrder);
  };

  // Update item text
  const updateItemText = (index, value) => {
    const newItems = [...localItems];
    newItems[index] = { ...newItems[index], text: value };
    setLocalItems(newItems);
  };

  return (
    <PanelRow>
      <Flex justify="space-between" align="center">
        <div>
          <Flex align="center">
            <FlexItem>
              {isEmpty ? (
                <span style={{ color: "#d63638" }}>⚠️</span>
              ) : (
                <span style={{ color: "#00a32a" }}>✅</span>
              )}
            </FlexItem>
            <FlexItem>
              <strong style={{ color: isEmpty ? "#999" : "#1e1e1e" }}>
                {title}
              </strong>
            </FlexItem>
          </Flex>
        </div>
        <Button icon={edit} onClick={openModal} />
      </Flex>

      {/* Modal for editing items */}
      {isOpen && (
        <Modal
          title={title}
          onRequestClose={closeModal}
          size="large"
          style={{ maxWidth: "600px" }}
        >
          <div>
            <Flex justify="space-between" align="center" gap={4}>
              <div>
                <h3 style={{ margin: 0 }}>
                  {__("Manage", "lithe-course")} {title}
                </h3>
                <p
                  style={{
                    margin: "4px 0 0",
                    color: "#666",
                    fontSize: "14px",
                  }}
                >
                  {__(
                    "Drag to reorder, click + to add new items",
                    "lithe-course"
                  )}
                </p>
              </div>
              <Button variant="primary" icon={plus} onClick={addItem}>
                {addButtonText}
              </Button>
            </Flex>

            <div style={{ marginBottom: "20px" }}>
              {localItems && localItems.length > 0 ? (
                <Reorder.Group
                  axis="y"
                  values={localItems}
                  onReorder={handleReorder}
                >
                  {localItems.map((item, index) => (
                    <Reorder.Item
                      key={item.id || item.text || index}
                      value={item}
                      style={{ marginBottom: "12px" }}
                    >
                      <Card
                        style={{
                          cursor: "grab",
                        }}
                      >
                        <Flex gap={3} align="center">
                          <FlexItem
                            style={{
                              display: "flex",
                              alignItems: "center",
                              cursor: "grab",
                              color: "#999",
                              fontSize: "16px",
                              lineHeight: "1",
                              padding: "4px",
                              borderRadius: "4px",
                              minWidth: "24px",
                              justifyContent: "center",
                            }}
                          >
                            ⋮⋮
                          </FlexItem>
                          <FlexBlock>
                            <TextControl
                              value={item.text}
                              onChange={(value) => updateItemText(index, value)}
                              placeholder={placeholder}
                              className="lithe-course-input-text"
                            />
                          </FlexBlock>
                          <FlexItem>
                            <Button
                              isDestructive
                              icon={trash}
                              onClick={() => removeItem(index)}
                              label={__("Remove item", "lithe-course")}
                              style={{
                                minWidth: "32px",
                                height: "32px",
                                borderRadius: "4px",
                              }}
                            />
                          </FlexItem>
                        </Flex>
                      </Card>
                    </Reorder.Item>
                  ))}
                </Reorder.Group>
              ) : (
                <div
                  style={{
                    textAlign: "center",
                    color: "#666",
                    border: "2px dashed #ddd",
                    borderRadius: "8px",
                    padding: "20px",
                    marginTop: "16px",
                  }}
                >
                  <p>{emptyMessage}</p>
                </div>
              )}
            </div>

            <Flex justify="flex-end">
              <Button variant="secondary" onClick={closeModal}>
                {__("Done", "lithe-course")}
              </Button>
            </Flex>
          </div>
        </Modal>
      )}
    </PanelRow>
  );
}
