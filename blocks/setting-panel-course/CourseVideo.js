import { useSelect } from "@wordpress/data";
import { useState, useEffect } from "@wordpress/element";
import { TextControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useEntityProp } from "@wordpress/core-data";

const videoPlatforms = {
  youtube: {
    pattern:
      /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i,
    embed_url: "https://www.youtube.com/embed/%s",
  },
  vimeo: {
    pattern: /(?:vimeo\.com\/)([0-9]+)/i,
    embed_url: "https://player.vimeo.com/video/%s",
  },
  bunnycdn: {
    pattern: /(?:bunnycdn\.com\/video\/)([^"&?\/\s]+)/i,
    embed_url: "https://iframe.mediadelivery.net/embed/%s",
  },
};

export default function CourseVideo() {
  // Get current post type
  const currentPostType = useSelect((select) => {
    return select("core/editor").getCurrentPostType();
  }, []);

  // Use the same approach as CourseInformation
  const [meta, setMeta] = useEntityProp("postType", currentPostType, "meta");
  const videoData = meta._video || {};

  // Local state for video URL
  const [videoUrl, setVideoUrl] = useState("");
  const [error, setError] = useState("");
  const [isInitialized, setIsInitialized] = useState(false);

  // Initialize local state with meta when video data changes (only once)
  useEffect(() => {
    if (!isInitialized && videoData) {
      setVideoUrl(videoData.video_url || "");
      setIsInitialized(true);
    }
  }, [videoData, isInitialized]);

  // Sync local state to meta (debounced for performance)
  useEffect(() => {
    if (!isInitialized) return;

    const timeoutId = setTimeout(() => {
      const currentVideoData = videoData || {};

      // Only save if there's actually a difference
      if (videoUrl !== (currentVideoData.video_url || "")) {
        const newVideoData = {
          ...currentVideoData,
          video_url: videoUrl,
        };

        // Extract platform and ID if URL is valid
        for (const [platform, config] of Object.entries(videoPlatforms)) {
          if (config.pattern.test(videoUrl)) {
            const matches = videoUrl.match(config.pattern);
            newVideoData.video_platform = platform;
            newVideoData.video_id = matches[1];
            break;
          }
        }

        setMeta({ ...meta, _video: newVideoData });
      }
    }, 1500);

    return () => clearTimeout(timeoutId);
  }, [videoUrl, videoData, setMeta, meta, isInitialized]);

  const validateVideoUrl = (url) => {
    if (!url) {
      setError("");
      return true;
    }

    for (const [platform, config] of Object.entries(videoPlatforms)) {
      if (config.pattern.test(url)) {
        setError("");
        return true;
      }
    }

    setError(
      __(
        "Invalid video URL. Please enter a valid YouTube, Vimeo, or BunnyCDN URL.",
        "lithe-course"
      )
    );
    return false;
  };

  const handleVideoUrlChange = (url) => {
    setVideoUrl(url);
    validateVideoUrl(url);
  };

  return (
    <div className="lithecourse-video-input">
      <TextControl
        label={__("Video URL", "lithe-course")}
        value={videoUrl}
        onChange={handleVideoUrlChange}
        help={__(
          "Enter a valid YouTube, Vimeo, or BunnyCDN URL.",
          "lithe-course"
        )}
      />
      {error && (
        <div
          className="components-notice is-error"
          style={{ marginTop: "8px" }}
        >
          {error}
        </div>
      )}
    </div>
  );
}
