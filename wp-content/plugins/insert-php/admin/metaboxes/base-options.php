<?php
	
	class WINP_BaseOptionsMetaBox extends Wbcr_FactoryMetaboxes400_FormMetabox {
		
		/**
		 * A visible title of the metabox.
		 *
		 * Inherited from the class FactoryMetabox.
		 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $title;
		

		/**
		 * The priority within the context where the boxes should show ('high', 'core', 'default' or 'low').
		 *
		 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
		 * Inherited from the class FactoryMetabox.
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $priority = 'core';
		
		public $cssClass = 'factory-bootstrap-401 factory-fontawesome-000';

		protected $errors = array();
		protected $source_channel;
		protected $facebook_group_id;
		protected $paginate_url;
		
		public function __construct($plugin)
		{
			parent::__construct($plugin);
			
			$this->title = __('Base options', 'insert-php');

			add_action('admin_head', array($this, 'removeMediaButton'));
			add_action('admin_enqueue_scripts', array($this, 'deregisterDefaultEditorResourses'));

			add_action('admin_footer-post.php', array($this, 'printCodeEditorScripts'), 99);
			add_action('admin_footer-post-new.php', array($this, 'printCodeEditorScripts'), 99);
		}


		/**
		 * Configures a metabox.
		 *
		 * @since 1.0.0
		 * @param Factory401_ScriptList $scripts A set of scripts to include.
		 * @param Factory401_StyleList $styles A set of style to include.
		 * @return void
		 */
		public function configure($scripts, $styles)
		{
			//method must be overriden in the derived classed.
			$styles->add(WINP_PLUGIN_URL . '/admin/assets/css/general.css');

			$this->styles->add(WINP_PLUGIN_URL . '/admin/assets/css/codemirror.css');

			$code_editor_theme = $this->plugin->getOption('code_editor_theme');

			if( !empty($code_editor_theme) && $code_editor_theme != 'default' ) {
				$this->styles->add(WINP_PLUGIN_URL . '/admin/assets/css/cmthemes/' . $code_editor_theme . '.css');
			}

			$this->scripts->addToHeader(WINP_PLUGIN_URL . '/admin/assets/js/codemirror.js');
		}

		/**
		 * Remove media button
		 */
		public function removeMediaButton()
		{
			global $post;

			if( empty($post) || $post->post_type !== WINP_SNIPPETS_POST_TYPE ) {
				return;
			}
			remove_action('media_buttons', 'media_buttons');
		}

		/**
		 * Deregister other CodeMirror styles
		 */
		public function deregisterDefaultEditorResourses()
		{
			global $post;

			if( empty($post) || $post->post_type !== WINP_SNIPPETS_POST_TYPE ) {
				return;
			}

			/* Remove other CodeMirror styles */
			wp_deregister_style('codemirror');
		}

		public function printCodeEditorScripts()
		{
			global $post;

			if( empty($post) || $post->post_type !== WINP_SNIPPETS_POST_TYPE ) {
				return;
			}

			$code_editor_theme = $this->plugin->getOption('code_editor_theme');

			?>
			<script>
				/* Loads CodeMirror on the snippet editor */
				(function() {

					var atts = [];

					atts['mode'] = 'text/x-php';
					atts['matchBrackets'] = true;
					atts['viewportMargin'] = Infinity;

					atts['extraKeys'] = {
						'Ctrl-Enter': function(cm) {
							document.getElementById('<?= $this->plugin->getPrefix() ?>snippet_code').submit();
						}
					};

					atts['indentWithTabs'] = <?php $this->printBool($this->plugin->getOption('code_editor_indent_with_tabs', false)) ?>;
					atts['tabSize'] = <?= (int)$this->plugin->getOption('code_editor_tab_size', 4) ?>;
					atts['indentUnit'] = <?= (int)$this->plugin->getOption('code_editor_indent_unit', 2) ?>;
					atts['lineNumbers'] = <?php $this->printBool($this->plugin->getOption('code_editor_line_numbers', false)) ?>;
					atts['lineWrapping'] = <?php $this->printBool($this->plugin->getOption('code_editor_wrap_lines', false)) ?>;
					atts['autoCloseBrackets'] = <?php $this->printBool($this->plugin->getOption('code_editor_auto_close_brackets', false)) ?>;
					atts['highlightSelectionMatches'] = <?php $this->printBool($this->plugin->getOption('code_editor_highlight_selection_matches', false)) ?>;

					<?php if(!empty($code_editor_theme) && $code_editor_theme != 'default'): ?>
					atts['theme'] = '<?= esc_attr($code_editor_theme) ?>';
					<?php endif; ?>

					CodeMirror.fromTextArea(document.getElementById('<?= $this->plugin->getPrefix() ?>snippet_code'), atts);
				})();

				jQuery(document).ready(function($) {
					$('.wp-editor-tabs').remove();
				});
			</script>
		<?php
		}

		/**
		 * @param bool $bool_val
		 */
		protected function printBool($bool_val)
		{
			echo $bool_val
				? 'true'
				: 'false';
		}

		/**
		 * Configures a form that will be inside the metabox.
		 *
		 * @see Wbcr_FactoryMetaboxes400_FormMetabox
		 * @since 1.0.0
		 *
		 * @param FactoryForms402_Form $form A form object to configure.
		 * @return void
		 */
		public function form($form)
		{
			$items[] = array(
				'type' => 'textarea',
				'name' => 'snippet_code',
				'title' => __('Enter the code for your snippet', 'insert-php'),
				'hint' => __('Enter the PHP code, without opening and closing tags.', 'insert-php'),
				'filter_value' => array($this, 'codeSnippetFilterValue')
			);

			$items[] = array(
				'type' => 'dropdown',
				'way' => 'buttons',
				'name' => 'snippet_scope',
				'data' => array(
					array('evrywhere', __('Run everywhere', 'insert-php')),
					array('shortcode', __('Where there is a shortcode', 'insert-php'))

				),
				'title' => __('Where to execute the code?', 'insert-php'),
				'hint' => __('If you select the "Run everywhere" option, after activating the widget, the php code will be launched on all pages of your site. Another option works only where you have set a shortcode snippet (widgets, post).', 'insert-php'),
				'default' => 'shortcode'
			);

			$items[] = array(
				'type' => 'textarea',
				'name' => 'snippet_description',
				'title' => __('Description', 'insert-php'),
				'hint' => __('You can write a short note so that you can always remember why this code or your colleague was able to apply this code in his works.', 'bizpanda'),
				'tinymce' => array(
					'height' => 150,
					'plugins' => ''
				),
				'default' => ''
			);

			$form->add($items);
		}

		/**
		 * Validate the snippet code before saving to database
		 *
		 * @param Code_Snippet $snippet
		 *
		 * @return bool true if code produces errors
		 */
		private function validateCode($snippet_code)
		{
			global $post;

			$snippet_code = stripslashes($snippet_code);

			if( empty($snippet_code) ) {
				return false;
			}

			ob_start(array($this, 'codeErrorCallback'));

			$result = eval($snippet_code);

			ob_end_clean();

			do_action('wbcr_inp_after_execute_snippet', $post->ID, $snippet_code, $result);

			return false === $result;
		}

		private function codeErrorCallback($out)
		{
			$error = error_get_last();

			if( is_null($error) ) {
				return $out;
			}

			$m = '<h3>' . __("Don't Panic", 'code-snippets') . '</h3>';
			$m .= '<p>' . sprintf(__('The code snippet you are trying to save produced a fatal error on line %d:', 'code_snippets'), $error['line']) . '</p>';
			$m .= '<strong>' . $error['message'] . '</strong>';
			$m .= '<p>' . __('The previous version of the snippet is unchanged, and the rest of this site should be functioning normally as before.', 'code-snippets') . '</p>';
			$m .= '<p>' . __('Please use the back button in your browser to return to the previous page and try to fix the code error.', 'code-snippets');
			$m .= ' ' . __('If you prefer, you can close this page and discard the changes you just made. No changes will be made to this site.', 'code-snippets') . '</p>';

			return $m;
		}

		/**
		 * @param string $value
		 * @param string $raw_value
		 * @return mixed
		 */
		public function codeSnippetFilterValue($value, $raw_value)
		{
			global $post;

			$snippet_code = $this->prepareCode($raw_value);

			$is_default_activate = WINP_Plugin::app()->getOption('activate_by_default', true);

			if( !$this->validateCode($snippet_code) && $is_default_activate && current_user_can('manage_options') ) {
				WINP_Helper::updateMetaOption($post->ID, 'snippet_activate', true);
			} else {
				WINP_Helper::updateMetaOption($post->ID, 'snippet_activate', false);
			}

			return $snippet_code;
		}

		/**
		 * Prepare the code by removing php tags from beginning and end
		 * @param  string $code
		 * @return string
		 */
		private function prepareCode($code)
		{
			/* Remove <?php and <? from beginning of snippet */
			$code = preg_replace('|^[\s]*<\?(php)?|', '', $code);

			/* Remove ?> from end of snippet */
			$code = preg_replace('|\?>[\s]*$|', '', $code);

			return $code;
		}

		public function onSavingForm($postId)
		{
			/*if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
				return;
			}*/
		}
	}