<?php

if(!class_exists('MaxGalleryOptions')) {
  
  class MaxGalleryOptions {
    private $_post_id;

    public function __construct($post_id) {
      $this->_post_id = $post_id;
    }

    public function get_post_id() {
      return $this->_post_id;
    }

    public function get_post_meta($meta_key) {
      return get_post_meta($this->get_post_id(), $meta_key, true);
    }

    public function delete_post_meta($meta_key) {
      delete_post_meta($this->get_post_id(), $meta_key);
    }

    public function save_post_meta($meta_key) {
      $post_id = $this->get_post_id();

      $meta_old_value = get_post_meta($post_id, $meta_key, true);
      $meta_new_value = isset($_POST[$meta_key]) ? stripslashes($_POST[$meta_key]) : '';

      // If the option is the saves count, we need to check if we have a template value. If we do, then
      // we increment the saves count; otherwise, we reset the saves count back to -1 to ensure gallery
      // options get their proper default values.
      if ($meta_key == $this->saves_count_key) {
        $meta_new_value = ($this->get_template() != '') ? ((int)$meta_new_value) + 1 : -1;
      }

      update_post_meta($post_id, $meta_key, $meta_new_value, $meta_old_value);
    }

    // These options are common to all templates
    public $custom_scripts_enabled_default = '';
    public $custom_scripts_enabled_key = 'maxgallery_custom_scripts_enabled';
    public $custom_scripts_url_key = 'maxgallery_custom_scripts_url';
    public $custom_styles_enabled_default = '';
    public $custom_styles_enabled_key = 'maxgallery_custom_styles_enabled';
    public $custom_styles_url_key = 'maxgallery_custom_styles_url';
    public $description_enabled_default = '';
    public $description_enabled_key = 'maxgallery_description_enabled';
    public $description_position_default = 'above';
    public $description_position_key = 'maxgallery_description_position';
    public $description_text_key = 'maxgallery_description_text';
    public $reset_options_default = '';
    public $reset_options_key = 'maxgallery_reset_options';
    public $saves_count_default = -1;
    public $saves_count_key = 'maxgallery_saves_count';
    public $template_key = 'maxgallery_template';
    public $type_key = 'maxgallery_type';

    public function get_custom_scripts_enabled() {
      $value = $this->get_post_meta($this->custom_scripts_enabled_key); 
      if ($value == '') {
        $value = $this->custom_scripts_enabled_default;
      }

      return $value;
    }

    public function get_custom_scripts_url() {
      return $this->get_post_meta($this->custom_scripts_url_key);
    }

    public function get_custom_styles_enabled() {
      $value = $this->get_post_meta($this->custom_styles_enabled_key); 
      if ($value == '') {
        $value = $this->custom_styles_enabled_default;
      }

      return $value;
    }

    public function get_custom_styles_url() {
      return $this->get_post_meta($this->custom_styles_url_key);
    }

    public function get_description_enabled() {
      $value = $this->get_post_meta($this->description_enabled_key); 
      if ($value == '') {
        $value = $this->description_enabled_default;
      }

      return $value;
    }

    public function get_description_position() {
      $value = $this->get_post_meta($this->description_position_key);
      if ($value == '') {
        $value = $this->description_position_default;
      }

      return $value;
    }

    public function get_description_text() {
      return $this->get_post_meta($this->description_text_key);
    }

    public function get_reset_options() {
      $value = $this->get_post_meta($this->reset_options_key); 
      if ($value == '') {
        $value = $this->reset_options_default;
      }

      return $value;
    }

    public function get_saves_count() {
      $value = $this->get_post_meta($this->saves_count_key); 
      if ($value == '') {
        $value = $this->saves_count_default;
      }

      return $value;
    }

    public function get_template() {
      return $this->get_post_meta($this->template_key);
    }

    public function get_type() {
      return $this->get_post_meta($this->type_key);
    }

    public function is_new_gallery() {
      // Use get_post_meta() instead of get_type() because get_type() will return
      // the default if it's an empty string, but we want to know if it's actually
      // an empty string to know if this is a new gallery or not.
      return ($this->get_post_meta($this->type_key) == '') ? true : false;
    }

    public function is_image_gallery() {
      return ($this->get_type() == 'image') ? true : false;
    }

    public function is_video_gallery() {
      return ($this->get_type() == 'video') ? true : false;
    }

    public function is_reset_options() {
      if (isset($_POST[$this->reset_options_key]) && $_POST[$this->reset_options_key] == 'on') {
        return true;
      }

      return false;
    }

    public function save_options($options = null) {
      if ($this->is_new_gallery()) {
        $this->save_post_meta($this->type_key);
      }
      else {
        // Get the base options and merge in the options that were passed in, if any
        $base_options = $this->base_options();
        $all_options = isset($options) ? array_merge($base_options, $options) : $base_options;

        foreach ($all_options as $option) {
          if ($this->is_reset_options()) {
            // Check to reset saves count back to 0 (instead of deleting it)
            if ($option == $this->saves_count_key) {
              update_post_meta($this->get_post_id(), $option, 0);
            }
            elseif ($option != $this->template_key) { // Don't reset the template
              $this->delete_post_meta($option);
            }
          }
          else {
            $this->save_post_meta($option);
          }
        }
      }
    }

    private function base_options() {
      return array(
        $this->template_key, // IMPORTANT: MUST ALWAYS COME FIRST
        $this->custom_scripts_enabled_key,
        $this->custom_scripts_url_key,
        $this->custom_styles_enabled_key,
        $this->custom_styles_url_key,
        $this->description_enabled_key,
        $this->description_position_key,
        $this->description_text_key,
        $this->saves_count_key
      );
    }
  }
}
?>