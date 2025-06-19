<?php
/**
 * Course Metadata Block Renderer
 * Displays various types of course metadata based on the selected type
 */

// Get attributes
$course_id = $attributes['courseId'] ?? get_the_ID();
$meta_type = $attributes['metaType'] ?? 'features';
$list_style = $attributes['listStyle'] ?? 'disc';
$columns = isset($attributes['columns']) ? (int) $attributes['columns'] : 1;
$columns = max(1, min(2, $columns)); // Ensure columns is between 1 and 2

// Determine the meta key and labels based on meta type
$meta_config = [
    'features' => [
        'meta_key' => '_features',
        'css_class' => 'features-list',
        'item_class' => 'feature-item',
        'empty_text' => __('No features available.', 'lithe-course'),
    ],
    'prerequisites' => [
        'meta_key' => '_requirements',
        'css_class' => 'prerequisites-list',
        'item_class' => 'prerequisite-item',
        'empty_text' => __('No prerequisites available.', 'lithe-course'),
    ],
    'learnings' => [
        'meta_key' => '_learnings',
        'css_class' => 'learnings-list',
        'item_class' => 'learning-item',
        'empty_text' => __('No learning objectives available.', 'lithe-course'),
    ],
    'suitableFor' => [
        'meta_key' => '_suitable',
        'css_class' => 'suitable-list',
        'item_class' => 'suitable-item',
        'empty_text' => __('No suitable audience information available.', 'lithe-course'),
    ],
];

// Get the metadata
$meta_items = [];
if (isset($meta_config[$meta_type])) {
    $config = $meta_config[$meta_type];
    $meta_items = get_post_meta($course_id, $config['meta_key'], true) ?: [];
}

// Calculate the column class based on the number of columns (only used for learnings)
$column_class = '';
if ($meta_type === 'learnings' && $columns === 2) {
    $column_class = 'two-columns';
} else {
    $column_class = 'one-column';
}

// Apply spacing and other block supports
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'list-style-' . esc_attr($list_style) . ($column_class ? ' ' . $column_class : ''),
]);

$config = $meta_config[$meta_type] ?? $meta_config['features'];
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if (!empty($meta_items)) : ?>
        <ul class="<?php echo esc_attr($config['css_class']); ?>">
            <?php foreach ($meta_items as $item) : ?>
                <li class="<?php echo esc_attr($config['item_class']); ?>">
                    <?php echo esc_html($item['text']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p class="no-items"><?php echo esc_html($config['empty_text']); ?></p>
    <?php endif; ?>
</div> 