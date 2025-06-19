import { __ } from "@wordpress/i18n";
import { useState } from "@wordpress/element";
import { Card, CardHeader, CardBody, Button } from "@wordpress/components";
import { RichText, useBlockProps, InnerBlocks } from "@wordpress/block-editor";
import { chevronDown, chevronUp } from "@wordpress/icons";
import { AnimatePresence, motion } from "framer-motion";
import { compose } from "@wordpress/compose";
import { withDispatch } from "@wordpress/data";

function Edit() {
  const [isOpen, setIsOpen] = useState(false);
  const blockProps = useBlockProps();

  return (
    <Card>
      <CardHeader>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            width: "100%",
          }}
        >
          <strong>Course Feature</strong>
          <Button
            variant="secondary"
            onClick={() => setIsOpen(!isOpen)}
            aria-expanded={isOpen}
            icon={isOpen ? chevronUp : chevronDown}
          />
        </div>
      </CardHeader>
      <AnimatePresence initial={false}>
        {isOpen && (
          <motion.div
            key="content"
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.3, ease: "easeInOut" }}
            style={{ overflow: "hidden" }}
          >
            <CardBody>
              <InnerBlocks templateLock={false} />
            </CardBody>
          </motion.div>
        )}
      </AnimatePresence>
    </Card>
  );
}

// This is a hack which forces the template to appear valid.
// See https://github.com/WordPress/gutenberg/issues/11681
const enforceTemplateValidity = withDispatch((dispatch, props) => {
  dispatch("core/block-editor").setTemplateValidity(true);
});

export default compose(enforceTemplateValidity)(Editor);
