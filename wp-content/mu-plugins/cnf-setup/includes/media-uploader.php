<?php
/**
 * Media Uploader
 *
 * Uploads media files from media-library/ folder to WordPress.
 *
 * @package CNF_Setup
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CNF Media Uploader Class
 *
 * Programmatically uploads images, videos, and documents to WordPress media library.
 */
class CNF_Media_Uploader {

    /**
     * Schema data
     *
     * @var array
     */
    private $schema;

    /**
     * Media library directory
     *
     * @var string
     */
    private $media_dir;

    /**
     * Constructor
     *
     * @param array $schema Schema data
     */
    public function __construct($schema) {
        $this->schema = $schema;
        $this->media_dir = CNF_SETUP_DIR . '/../../../public/uploads/';
    }

    /**
     * Upload All Media
     *
     * Uploads all media files defined in schema.
     */
    public function upload_all() {
        $media_items = isset($this->schema['mediaLibrary']) ? $this->schema['mediaLibrary'] : array();

        if (empty($media_items)) {
            error_log('CNF Setup: No media items found in schema (this is optional)');
            return true;
        }

        error_log('CNF Setup: Found ' . count($media_items) . ' media items to upload');

        if (empty($media_items)) {
            error_log('CNF Setup: No media items to upload');
            return true;
        }

        foreach ($media_items as $media) {
            $this->upload_media_item($media);
        }

        return true;
    }

    /**
     * Upload Single Media Item
     *
     * @param array $media Media configuration
     */
    private function upload_media_item($media) {
        $filename = isset($media['filename']) ? $media['filename'] : '';
        $title = isset($media['title']) ? $media['title'] : '';
        $alt_text = isset($media['alt_text']) ? $media['alt_text'] : '';
        $caption = isset($media['caption']) ? $media['caption'] : '';
        $description = isset($media['description']) ? $media['description'] : '';

        if (empty($filename)) {
            error_log('CNF Setup: Media filename is empty, skipping');
            return false;
        }

        // Build full file path
        $file_path = $this->media_dir . $filename;

        if (!file_exists($file_path)) {
            error_log("CNF Setup: Media file not found: {$file_path}");
            return false;
        }

        // Check if file already uploaded
        $existing_attachment = $this->get_attachment_by_filename($filename);
        if ($existing_attachment) {
            error_log("CNF Setup: Media '{$filename}' already uploaded, skipping");
            return $existing_attachment;
        }

        // Upload file
        $attachment_id = $this->upload_file($file_path, $title, $alt_text, $caption, $description);

        if ($attachment_id) {
            error_log("CNF Setup: Uploaded media '{$filename}' with ID {$attachment_id}");
        }

        return $attachment_id;
    }

    /**
     * Upload File to WordPress
     *
     * @param string $file_path Full file path
     * @param string $title File title
     * @param string $alt_text Alt text
     * @param string $caption Caption
     * @param string $description Description
     * @return int|false Attachment ID or false on failure
     */
    private function upload_file($file_path, $title, $alt_text, $caption, $description) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Get file info
        $filename = basename($file_path);
        $filetype = wp_check_filetype($filename, null);

        // Prepare upload
        $upload_dir = wp_upload_dir();
        $upload_path = $upload_dir['path'] . '/' . $filename;

        // Check if file already exists in uploads directory
        if (file_exists($upload_path)) {
            error_log("CNF Setup: File already exists in uploads: {$filename}");
            // Return existing attachment ID
            return $this->get_attachment_by_filename($filename);
        }

        // Copy file to uploads directory
        if (!copy($file_path, $upload_path)) {
            error_log("CNF Setup: Failed to copy file to uploads: {$filename}");
            return false;
        }

        // Create attachment
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => $title ?: sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
            'post_content' => $description,
            'post_excerpt' => $caption,
            'post_status' => 'inherit',
        );

        // Insert attachment
        $attachment_id = wp_insert_attachment($attachment, $upload_path);

        if (is_wp_error($attachment_id)) {
            error_log("CNF Setup: Failed to create attachment: " . $attachment_id->get_error_message());
            return false;
        }

        // Generate attachment metadata
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        // Set alt text
        if (!empty($alt_text)) {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt_text);
        }

        return $attachment_id;
    }

    /**
     * Get Attachment by Filename
     *
     * Checks if file already exists in media library.
     *
     * @param string $filename Filename
     * @return int|false Attachment ID or false if not found
     */
    private function get_attachment_by_filename($filename) {
        global $wpdb;

        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
            '%' . $wpdb->esc_like($filename)
        ));

        return $attachment_id ? (int) $attachment_id : false;
    }

    /**
     * Bulk Upload from Directory
     *
     * Uploads all files from media-library/ directory.
     */
    public function bulk_upload_from_directory() {
        if (!is_dir($this->media_dir)) {
            error_log("CNF Setup: Media directory not found: {$this->media_dir}");
            return false;
        }

        $files = glob($this->media_dir . '/*');

        if (empty($files)) {
            error_log('CNF Setup: No files found in media directory');
            return false;
        }

        $uploaded_count = 0;

        foreach ($files as $file_path) {
            if (!is_file($file_path)) {
                continue;
            }

            $filename = basename($file_path);

            // Skip if already uploaded
            if ($this->get_attachment_by_filename($filename)) {
                continue;
            }

            // Generate title from filename
            $title = sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME));
            $alt_text = $title;

            $attachment_id = $this->upload_file($file_path, $title, $alt_text, '', '');

            if ($attachment_id) {
                $uploaded_count++;
                error_log("CNF Setup: Bulk uploaded: {$filename} (ID: {$attachment_id})");
            }
        }

        error_log("CNF Setup: Bulk upload completed. Uploaded {$uploaded_count} files.");

        return $uploaded_count;
    }
}
