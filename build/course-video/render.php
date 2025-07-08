<?php
/**
 * Block Name: Course Video
 * Description: Displays the course video
 */

// Get the course ID from the current post context
$course_id = get_the_ID();

// If we're not on a course page, try to get the course ID from the block attributes
if (get_post_type() !== 'lithe_course') {
    $course_id = isset($attributes['courseId']) ? $attributes['courseId'] : null;
    
    // Fallback to ACF field if using it
    if (!$course_id && function_exists('get_field')) {
        $course_id = get_field('courseId');
    }
}

if (!$course_id) {
    return;
}

// Get the video data
$video_data = get_post_meta($course_id, '_video', true);

if (empty($video_data) || empty($video_data['video_url'])) {
    ?>
    <div <?php echo get_block_wrapper_attributes(['class' => 'lithe-course-video']); ?>>
        <div class="video-placeholder">
            <div class="video-placeholder-content">
                <svg width="48" height="48" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <path d="M18.7 3H5.3C4 3 3 4 3 5.3v13.4C3 20 4 21 5.3 21h13.4c1.3 0 2.3-1 2.3-2.3V5.3C21 4 20 3 18.7 3zm.8 15.7c0 .4-.4.8-.8.8H5.3c-.4 0-.8-.4-.8-.8V5.3c0-.4.4-.8.8-.8h13.4c.4 0 .8.4.8.8v13.4zM10 15l5-3-5-3v6z"/>
                </svg>
                <p><?php echo esc_html__('Course Video', 'lithe-course'); ?></p>
                <p class="video-placeholder-note">
                    <?php echo esc_html__('No video available yet', 'lithe-course'); ?>
                </p>
            </div>
        </div>
    </div>
    <?php
    return;
}

// Determine the embed URL based on the platform
$embed_url = '';
$video_id = $video_data['video_id'] ?? '';

if (!empty($video_id)) {
    switch ($video_data['video_platform']) {
        case 'youtube':
            $embed_url = "https://www.youtube.com/embed/" . esc_attr($video_id);
            break;
        case 'vimeo':
            $embed_url = "https://player.vimeo.com/video/" . esc_attr($video_id);
            break;
        case 'bunnycdn':
            $embed_url = "https://iframe.mediadelivery.net/embed/" . esc_attr($video_id);
            break;
        default:
            // If platform is not recognized, try to use the video_url directly
            $embed_url = esc_url($video_data['video_url']);
    }
} else {
    // Fallback to video_url if video_id is not available
    $embed_url = esc_url($video_data['video_url']);
}
?>

<div <?php echo get_block_wrapper_attributes(['class' => 'lithe-course-video']); ?>>
    <div class="video-container">
        <iframe 
            src="<?php echo $embed_url; ?>" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>
    </div>
</div> 