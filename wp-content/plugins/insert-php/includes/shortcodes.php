<?php
	
	/**
	 * A base shortcode for all lockers
	 *
	 * @since 1.0.0
	 */
	class WINP_SnippetShortcode extends Wbcr_FactoryShortcodes321_Shortcode {
		
		public $shortcode_name = 'wbcr_php_snippet';
		// -------------------------------------------------------------------------------------
		// Includes assets
		// -------------------------------------------------------------------------------------
		
		public $assets_in_header = true;

		// -------------------------------------------------------------------------------------
		// Content render
		// -------------------------------------------------------------------------------------
		
		public function html($attr, $content)
		{
			$id = isset($attr['id'])
				? (int)$attr['id']
				: null;

			if( !$id ) {
				echo '<span style="color:red">' . __('[wbcr_php_snippet]: PHP snippets error (not passed the snippet ID)', 'insert-php') . '</span>';

				return;
			}

			$snippet_meta = get_post_meta($id, '');

			if( empty($snippet_meta) ) {
				return;
			}

			$is_activate = isset($snippet_meta[$this->plugin->getPrefix() . 'snippet_activate']) && $snippet_meta[$this->plugin->getPrefix() . 'snippet_activate'][0];

			$snippet_code = isset($snippet_meta[$this->plugin->getPrefix() . 'snippet_code'])
				? $snippet_meta[$this->plugin->getPrefix() . 'snippet_code']
				: null;

			$snippet_scope = isset($snippet_meta[$this->plugin->getPrefix() . 'snippet_scope'])
				? $snippet_meta[$this->plugin->getPrefix() . 'snippet_scope'][0]
				: null;

			if( !$is_activate || empty($snippet_code) || $snippet_scope != 'shortcode' ) {
				return;
			}

			eval($snippet_code[0]);
		}
	}