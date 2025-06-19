import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import {
  Card,
  CardHeader,
  CardBody,
  Button,
  TextControl,
  Flex,
  FlexItem,
  FlexBlock,
} from "@wordpress/components";
import { useBlockProps } from "@wordpress/block-editor";
import { chevronDown, chevronUp, plus, trash } from "@wordpress/icons";
import { AnimatePresence, motion, Reorder } from "framer-motion";
import { useEntityProp } from "@wordpress/core-data";

export default function Edit() {
  // Attributes for the block wrapper
  const blockProps = useBlockProps();

  // Determine the curent post type in the editor context
  const currentPostType = useSelect((select) => {
    return select("core/editor").getCurrentPostType();
  }, []);

  // Fetch the meta as an object and the setMeta function
  const [meta, setMeta] = useEntityProp("postType", currentPostType, "meta");
  const { _features } = meta;

  // Local state for smooth reordering
  const [localFeatures, setLocalFeatures] = useState(_features || []);

  // Sync local state with meta when _features changes
  useEffect(() => {
    setLocalFeatures(_features || []);
  }, [_features]);

  // Sync local state to meta (debounced for performance)
  useEffect(() => {
    const timeoutId = setTimeout(() => {
      if (JSON.stringify(localFeatures) !== JSON.stringify(_features || [])) {
        setMeta({ ...meta, _features: localFeatures });
      }
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [localFeatures, meta, _features, setMeta]);

  // Add a new feature
  const addFeature = () => {
    const newFeatures = [...localFeatures, { text: "", id: Date.now() }];
    setLocalFeatures(newFeatures);
  };

  // Remove a feature
  const removeFeature = (index) => {
    const newFeatures = [...localFeatures];
    newFeatures.splice(index, 1);
    setLocalFeatures(newFeatures);
  };

  // Handle reordering (now just updates local state)
  const handleReorder = (newOrder) => {
    setLocalFeatures(newOrder);
  };

  // Update feature text
  const updateFeatureText = (index, value) => {
    const newFeatures = [...localFeatures];
    newFeatures[index] = { ...newFeatures[index], text: value };
    setLocalFeatures(newFeatures);
  };

  return (
    <div {...blockProps}>
      <Card>
        <CardHeader>
          <Flex justify="space-between" align="center">
            <h3>{__("Course Features", "lithe-course")}</h3>
            <Button variant="primary" icon={plus} onClick={addFeature}>
              {__("Add Feature", "lithe-course")}
            </Button>
          </Flex>
        </CardHeader>

        <CardBody>
          <div style={{ marginBottom: "16px" }}>
            {localFeatures && localFeatures.length > 0 ? (
              <Reorder.Group
                axis="y"
                values={localFeatures}
                onReorder={handleReorder}
                style={{ listStyle: "none", padding: 0, margin: 0 }}
              >
                {localFeatures.map((feature, index) => (
                  <Reorder.Item
                    key={feature.id || feature.text || index}
                    value={feature}
                    style={{ marginBottom: "8px" }}
                  >
                    <Flex
                      style={{
                        marginBottom: "8px",
                        backgroundColor: "#f9f9f9",
                        padding: "12px",
                        borderRadius: "4px",
                        border: "1px solid #ddd",
                        cursor: "grab",
                      }}
                    >
                      <FlexItem
                        style={{
                          display: "flex",
                          alignItems: "center",
                          marginRight: "8px",
                          cursor: "grab",
                        }}
                      >
                        <span
                          style={{
                            fontSize: "16px",
                            color: "#666",
                            lineHeight: "1",
                          }}
                        >
                          ⋮⋮
                        </span>
                      </FlexItem>
                      <FlexBlock>
                        <TextControl
                          value={feature.text}
                          onChange={(value) => updateFeatureText(index, value)}
                          placeholder={__(
                            "Enter feature description",
                            "lithe-course"
                          )}
                        />
                      </FlexBlock>
                      <FlexItem>
                        <Button
                          isDestructive
                          icon={trash}
                          onClick={() => removeFeature(index)}
                          style={{ marginLeft: "8px" }}
                        >
                          {__("Remove", "lithe-course")}
                        </Button>
                      </FlexItem>
                    </Flex>
                  </Reorder.Item>
                ))}
              </Reorder.Group>
            ) : (
              <p>
                {__(
                  'No features added yet. Click "Add Feature" to get started.',
                  "lithe-course"
                )}
              </p>
            )}
          </div>
        </CardBody>
      </Card>
    </div>
  );
}
