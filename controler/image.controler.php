<?php
/**
 * Image Controller
 * Handles image resizing, cropping, and thumbnail generation
 * PHP 8 compatible
 */

class Image {
    public $_new_img_name = null;
    
    /**
     * Validate and save uploaded image
     */
    public function isImage($img) {
        if (!isset($img['tmp_name']) || empty($img['tmp_name'])) {
            return false;
        }
        
        $img_name = $img['name'];
        $tmp_name = $img['tmp_name'];
        $img_explode = explode('.', $img_name);
        $img_ext = strtolower(end($img_explode));
        $extensions = ["jpeg", "png", "jpg", "gif"];
        
        if (!in_array($img_ext, $extensions)) {
            return false;
        }
        
        $type = @exif_imagetype($tmp_name);
        if ($type === false) {
            return false;
        }
        
        $im = $this->createImageFromFile($tmp_name, $type);
        if ($im === false) {
            return false;
        }
        
        $output_filename = uniqid('THODZ_', true);
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $this->_new_img_name = $output_filename . '.jpg';
                imagejpeg($im, '../images/' . $this->_new_img_name, 85);
                break;
            case IMAGETYPE_PNG:
                $this->_new_img_name = $output_filename . '.png';
                imagepng($im, '../images/' . $this->_new_img_name, 8);
                break;
            case IMAGETYPE_GIF:
                $this->_new_img_name = $output_filename . '.gif';
                imagegif($im, '../images/' . $this->_new_img_name);
                break;
            default:
                imagedestroy($im);
                return false;
        }
        
        imagedestroy($im);
        return true;
    }
    
    /**
     * Create image resource from file based on type
     */
    private function createImageFromFile($filename, $type = null) {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }
        
        if ($type === null) {
            $type = @exif_imagetype($filename);
        }
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($filename);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($filename);
            case IMAGETYPE_GIF:
                return @imagecreatefromgif($filename);
            default:
                // Try each format
                $im = @imagecreatefromjpeg($filename);
                if ($im) return $im;
                $im = @imagecreatefrompng($filename);
                if ($im) return $im;
                $im = @imagecreatefromgif($filename);
                if ($im) return $im;
                return false;
        }
    }
    
    /**
     * Crop image to exact dimensions (square crop from center)
     */
    public function crop_image($original_file_name, $cropped_file_name, $max_width, $max_height) {
        if (!file_exists($original_file_name) || !is_readable($original_file_name)) {
            return false;
        }
        
        $original_image = $this->createImageFromFile($original_file_name);
        if (!$original_image) {
            return false;
        }
        
        $original_width = imagesx($original_image);
        $original_height = imagesy($original_image);
        
        // Calculate the scaling ratio to cover the target dimensions
        $ratio_w = $max_width / $original_width;
        $ratio_h = $max_height / $original_height;
        $ratio = max($ratio_w, $ratio_h);
        
        // Calculate new dimensions
        $new_width = (int) round($original_width * $ratio);
        $new_height = (int) round($original_height * $ratio);
        
        // Create scaled image
        $scaled_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG
        imagealphablending($scaled_image, false);
        imagesavealpha($scaled_image, true);
        
        imagecopyresampled(
            $scaled_image, $original_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $original_width, $original_height
        );
        
        imagedestroy($original_image);
        
        // Calculate crop position (center crop)
        $x = (int) round(($new_width - $max_width) / 2);
        $y = (int) round(($new_height - $max_height) / 2);
        
        // Create final cropped image
        $cropped_image = imagecreatetruecolor($max_width, $max_height);
        imagealphablending($cropped_image, false);
        imagesavealpha($cropped_image, true);
        
        imagecopyresampled(
            $cropped_image, $scaled_image,
            0, 0, $x, $y,
            $max_width, $max_height,
            $max_width, $max_height
        );
        
        imagedestroy($scaled_image);
        
        // Save as JPEG
        $result = imagejpeg($cropped_image, $cropped_file_name, 90);
        imagedestroy($cropped_image);
        
        return $result;
    }
    
    /**
     * Resize image maintaining aspect ratio (fit within dimensions)
     */
    public function resize_image($original_file_name, $resized_file_name, $max_width, $max_height) {
        if (!file_exists($original_file_name) || !is_readable($original_file_name)) {
            return false;
        }
        
        $original_image = $this->createImageFromFile($original_file_name);
        if (!$original_image) {
            return false;
        }
        
        $original_width = imagesx($original_image);
        $original_height = imagesy($original_image);
        
        // Calculate scaling ratio to fit within max dimensions
        $ratio_w = $max_width / $original_width;
        $ratio_h = $max_height / $original_height;
        $ratio = min($ratio_w, $ratio_h, 1); // Don't upscale
        
        // Calculate new dimensions
        $new_width = (int) round($original_width * $ratio);
        $new_height = (int) round($original_height * $ratio);
        
        // Ensure minimum dimensions
        $new_width = max(1, $new_width);
        $new_height = max(1, $new_height);
        
        // Create resized image
        $resized_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG
        imagealphablending($resized_image, false);
        imagesavealpha($resized_image, true);
        
        imagecopyresampled(
            $resized_image, $original_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $original_width, $original_height
        );
        
        imagedestroy($original_image);
        
        // Save as JPEG
        $result = imagejpeg($resized_image, $resized_file_name, 90);
        imagedestroy($resized_image);
        
        return $result;
    }
    
    /**
     * Get or create profile thumbnail (square crop)
     */
    public function get_thumb_profile($filename) {
        // Handle empty or invalid filename
        if (empty($filename)) {
            return './images/user_male.jpg';
        }
        
        // Check if file exists
        if (!file_exists($filename)) {
            // Try with different path prefixes
            $clean_filename = preg_replace('/^\.+\//', '', $filename);
            $alt_paths = [
                $filename,
                './' . $clean_filename,
                '../' . $clean_filename,
            ];
            
            $found = false;
            foreach ($alt_paths as $path) {
                if (file_exists($path)) {
                    $filename = $path;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return './images/user_male.jpg';
            }
        }
        
        $thumbnail = $filename . "_profile_thumb.jpg";
        
        // Return existing thumbnail
        if (file_exists($thumbnail)) {
            return $thumbnail;
        }
        
        // Create new thumbnail
        $result = $this->crop_image($filename, $thumbnail, 600, 600);
        
        if ($result && file_exists($thumbnail)) {
            return $thumbnail;
        }
        
        return $filename;
    }
    
    /**
     * Get or create post thumbnail (square crop)
     */
    public function get_thumb_post($filename) {
        // Handle empty or invalid filename
        if (empty($filename)) {
            return './images/no_image.jpg';
        }
        
        // Check if file exists
        if (!file_exists($filename)) {
            // Try with different path prefixes
            $clean_filename = preg_replace('/^\.+\//', '', $filename);
            $alt_paths = [
                $filename,
                './' . $clean_filename,
                '../' . $clean_filename,
            ];
            
            $found = false;
            foreach ($alt_paths as $path) {
                if (file_exists($path)) {
                    $filename = $path;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return './images/no_image.jpg';
            }
        }
        
        $thumbnail = $filename . "_post_thumb.jpg";
        
        // Return existing thumbnail
        if (file_exists($thumbnail)) {
            return $thumbnail;
        }
        
        // Create new thumbnail
        $result = $this->crop_image($filename, $thumbnail, 600, 600);
        
        if ($result && file_exists($thumbnail)) {
            return $thumbnail;
        }
        
        return $filename;
    }
}
