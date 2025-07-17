import { useSelect, useDispatch } from "@wordpress/data";
import { useState, useEffect } from "@wordpress/element";
import { TextControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

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
  const postId = useSelect((select) =>
    select("core/editor").getCurrentPostId()
  );
  const { editPost } = useDispatch("core/editor");
  const [videoUrl, setVideoUrl] = useState("");
  const [error, setError] = useState("");

  useEffect(() => {
    // Fetch video data when component mounts
    apiFetch({ path: `/wp/v2/lithe_course/${postId}` })
      .then((post) => {
        if (post.meta && post.meta._video) {
          setVideoUrl(post.meta._video.video_url || "");
        }
      })
      .catch((error) => {
        console.error("Error fetching video data:", error);
      });
  }, [postId]);

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

    if (validateVideoUrl(url)) {
      // Prepare video data
      const videoData = {
        video_url: url,
      };

      // Extract platform and ID if URL is valid
      for (const [platform, config] of Object.entries(videoPlatforms)) {
        if (config.pattern.test(url)) {
          const matches = url.match(config.pattern);
          videoData.video_platform = platform;
          videoData.video_id = matches[1];
          break;
        }
      }

      // Update post meta
      apiFetch({
        path: `/wp/v2/lithe_course/${postId}`,
        method: "POST",
        data: {
          meta: {
            _video: videoData,
          },
        },
      }).catch((error) => {
        console.error("Error updating video data:", error);
        setError(__("Failed to save video URL", "lithe-course"));
      });
    }
  };

  return (
    <div className="course-video-input">
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
