import { PluginDocumentSettingPanel } from "@wordpress/editor";
import { useSelect, useDispatch } from "@wordpress/data";
import { useState, useEffect } from "@wordpress/element";
import {
  Button,
  SelectControl,
  TextControl,
  Card,
} from "@wordpress/components";
import { motion, Reorder } from "framer-motion";
import apiFetch from "@wordpress/api-fetch";

const icons = {
  video: "dashicons-video-alt3",
  exercise: "dashicons-welcome-learn-more",
  article: "dashicons-media-text",
  download: "dashicons-download",
  mobile: "dashicons-smartphone",
  lifetime: "dashicons-clock",
  certificate: "dashicons-awards",
  custom: "dashicons-star-filled",
};

const FeatureItem = ({ feature, index, onRemove, onChange }) => (
  <motion.div
    layout
    initial={{ opacity: 0, y: 20 }}
    animate={{ opacity: 1, y: 0 }}
    exit={{ opacity: 0, y: -20 }}
    transition={{ duration: 0.2 }}
  >
    <Card className="feature-card">
      <div className="feature-row">
        <div className="feature-handle">
          <span className="dashicons dashicons-menu"></span>
        </div>
        <SelectControl
          label="Icon"
          value={feature.icon}
          options={Object.entries(icons).map(([value, label]) => ({
            label: value.charAt(0).toUpperCase() + value.slice(1),
            value,
          }))}
          onChange={(value) => onChange(index, "icon", value)}
        />
        <TextControl
          label="Feature Text"
          value={feature.text}
          onChange={(value) => onChange(index, "text", value)}
        />
        <Button
          isDestructive
          onClick={() => onRemove(index)}
          className="remove-feature"
        >
          Remove
        </Button>
      </div>
    </Card>
  </motion.div>
);

export const CourseFeaturesPanel = () => {
  const postId = useSelect((select) =>
    select("core/editor").getCurrentPostId()
  );
  const [features, setFeatures] = useState([]);
  const [error, setError] = useState("");

  useEffect(() => {
    // Fetch features data when component mounts
    apiFetch({ path: `/wp/v2/lithe_course/${postId}` })
      .then((post) => {
        if (post.meta && post.meta._features) {
          setFeatures(post.meta._features || []);
        }
      })
      .catch((error) => {
        console.error("Error fetching features data:", error);
        setError("Failed to load features");
      });
  }, [postId]);

  const handleAddFeature = () => {
    setFeatures([...features, { icon: "custom", text: "" }]);
  };

  const handleRemoveFeature = (index) => {
    setFeatures(features.filter((_, i) => i !== index));
  };

  const handleFeatureChange = (index, field, value) => {
    const newFeatures = [...features];
    newFeatures[index] = {
      ...newFeatures[index],
      [field]: value,
    };
    setFeatures(newFeatures);
  };

  const handleReorder = (newOrder) => {
    setFeatures(newOrder);
  };

  const handleSaveFeatures = () => {
    apiFetch({
      path: `/wp/v2/lithe_course/${postId}`,
      method: "POST",
      data: {
        meta: {
          _features: features,
        },
      },
    }).catch((error) => {
      console.error("Error saving features:", error);
      setError("Failed to save features");
    });
  };

  return (
    <PluginDocumentSettingPanel
      name="course-features-panel"
      title="Course Features"
      className="course-features-panel"
    >
      <div className="course-features-input">
        <Reorder.Group axis="y" values={features} onReorder={handleReorder}>
          {features.map((feature, index) => (
            <Reorder.Item key={index} value={feature}>
              <FeatureItem
                feature={feature}
                index={index}
                onRemove={handleRemoveFeature}
                onChange={handleFeatureChange}
              />
            </Reorder.Item>
          ))}
        </Reorder.Group>
        <Button isPrimary onClick={handleAddFeature} className="add-feature">
          Add Feature
        </Button>
        <Button
          isPrimary
          onClick={handleSaveFeatures}
          className="save-features"
        >
          Save Features
        </Button>
        {error && (
          <div
            className="components-notice is-error"
            style={{ marginTop: "8px" }}
          >
            {error}
          </div>
        )}
      </div>
    </PluginDocumentSettingPanel>
  );
};
