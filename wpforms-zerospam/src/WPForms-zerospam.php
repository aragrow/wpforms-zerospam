<?php


	/**
	* Main WPFormsZeroSpam class.
	*
	* @since 1.0.0
	* 
	*	This code is designed to integrate with the WPForms plugin and provide additional spam prevention features by blocking certain emails, domains, 
	*	and keywords. It also offers a settings page in the WordPress admin for configuring these blocking rules.
	*
	* Constructor:
	*	The class has a constructor method (__construct) that is called when an object of the class is created. This method initializes various settings, 
	*		includes necessary files (although the includes method is currently empty), and hooks into various WordPress actions.
	*
	*	Hooked Actions:
	*	The constructor hooks into several actions using add_action:
	*		admin_init: It checks whether the required plugins (WPForms Lite and Advanced Custom Fields) are activated.
	*		admin_menu: It creates a WordPress admin menu and sub-menus for the plugin.
	*		admin_init: It registers settings for the plugin.
	*		admin_enqueue_scripts: It registers and enqueues styles and scripts for the WordPress admin.
	*		wpforms_process_validate_email, wpforms_process_validate_text, wpforms_process_validate_textarea: These actions are hooked to block certain 
	*			email addresses, text, or textarea content during the WPForms validation process.
	*
	*	Menu Creation:
	*	The create_menu method creates a WordPress admin menu and sub-menus for the plugin. It uses the add_menu_page and add_submenu_page functions.
	*
	*	Settings and Option Pages:
	*
	*		The admin_page_contents_callback, admin_page_contents_block_emails_callback, admin_page_contents_block_domains_callback, and 
	*			admin_page_contents_block_keywords_callback methods 	define the content for various settings and option pages.
	*		The register_settings method registers settings for the plugin using the register_setting and add_settings_field functions.
	*	
	*	Settings Callbacks:
	*		The dropdown_callback, block_emails_callback, block_domains_callback, and block_keywords_callback methods are used as callbacks for the settings fields. 
	*		They define the HTML for input fields and display information about the number of blocked items.
	*	
	*	Serialization Callback:
	*		The serialize_callback method is used as a callback for sanitizing and serializing input data. It handles both array and comma-separated list formats.
	*
	*	Scripts and Styles:
	*		The register_scripts, load_scripts methods are used to register and enqueue scripts and styles for the WordPress admin.
	*
	*	Spam Handling:
	*		The block_email_address, block_email_text methods are hooked into WPForms validation to check and block certain email addresses or text content.
	*		The is_domain_block, is_email_block, and is_keyword_block methods check if a domain, email, or keyword is blocked based on the plugin's settings.
	*		The sendToSpam method modifies the email address if it matches certain criteria, effectively marking it as spam.
	*
	*	Plugin Activation:
	*		The install method is intended for handling plugin installation upon activation, but it is currently empty.
	*
	*	Initialization:
	*		The wpforms_zerospam function creates an instance of the WPForms_ZeroSpam class when called.
	* 
	* 
	* 
	* 
	*/

	 class WPForms_ZeroSpam {

		/**
		 * Primary class constructor.
		 *
		 * @since 1.2.2
		 */
		public function __construct() {
	
			//var_dump('Exec WPForms_ZeroSpam->__contruct');exit;

			$this->includes();
			
			add_action( 'admin_init', [$this, 'check_some_other_plugin'] );
			add_action( 'admin_menu', [$this, 'create_menu'] );
			add_action( 'admin_init', [$this, 'register_settings'] );
			add_action( 'admin_enqueue_scripts', [$this, 'register_scripts'] );
			add_action( 'admin_enqueue_scripts', [$this, 'load_scripts' ] );
	
			add_action( 'wpforms_process_validate_email', [$this , 'block_email_address'], 10, 3 );
			add_action( 'wpforms_process_validate_text', [$this , 'block_email_text'], 10, 3 );
			add_action( 'wpforms_process_validate_textarea', [$this , 'block_email_text'], 10, 3 );
			
		}
	

		public function check_some_other_plugin() {

			if (!is_plugin_active('wpforms-lite/wpforms.php')) {
				// WPForms Lite is not activated, send an error message
				wp_die('Error: WPForms Lite plugin is not activated. Please activate it to use this feature.');
			}
			
		}

		public function create_menu() {

			error_log('Exec wpfzs->create_menu');

			$page_title = __('WPForms - Zero Span', 'wpfzs');
			$menu_title = __('WPF Zero Span', 'wpfzs');
			$menu_slug = 'wpfzs';
			$capability = 'manage_options';
			$function =  [$this, 'admin_page_contents_callback'];
			$icon_url = '';
			$position = 6;

			add_menu_page( $page_title, 
						   $menu_title, 
						   $capability, 
						   $menu_slug, 
						   $function, 
						   $icon_url, 
						   $position);

			$sub_parent_slug = $menu_slug;
			$sub_page_title = __('Block Domains', 'wpfzs');;
			$sub_menu_title = __($sub_page_title, 'wpfzs');;
			$sub_menu_slug = $sub_parent_slug.'-bd';
			$sub_function =  [$this, 'admin_page_contents_block_domains_callback'];

			add_submenu_page(
				$sub_parent_slug,         
				$sub_page_title,       
				$sub_menu_title,     
				$capability,
				$sub_menu_slug,
				$sub_function
			);

			$sub_parent_slug = $menu_slug;
			$sub_page_title = __('Block Emails', 'wpfzs');;
			$sub_menu_title = __($sub_page_title, 'wpfzs');;
			$sub_menu_slug = $sub_parent_slug.'-be';
			$sub_function =  [$this, 'admin_page_contents_block_emails_callback'];

			add_submenu_page(
				$sub_parent_slug,         
				$sub_page_title,       
				$sub_menu_title,     
				$capability,
				$sub_menu_slug,
				$sub_function
			);

			$sub_parent_slug = $menu_slug;
			$sub_page_title = __('Block Keywords', 'wpfzs');;
			$sub_menu_title = __($sub_page_title, 'wpfzs');;
			$sub_menu_slug = $sub_parent_slug.'-bk';
			$sub_function =  [$this, 'admin_page_contents_block_keywords_callback'];

			add_submenu_page(
				$sub_parent_slug,         
				$sub_page_title,       
				$sub_menu_title,     
				$capability,
				$sub_menu_slug,
				$sub_function
			);

		}

		public function admin_page_contents_callback() {
			// Callback function to display content goes here
			?>
			<div class="wrap wpfzs">
				<h2 class="wpfzs primary"><?php echo __('WPForms - Zero Spam - Settings', 'wpfzs') ?></h2>
				<form method="post" action="options.php">
					<?php settings_fields('wpfzs-se'); //Add necessary hidden fields to the form. ?>
					<table class="form-table">
						<?php do_settings_fields('wpfzs-se', 'default') ?>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		public function admin_page_contents_block_emails_callback() {
			// Callback function to display content goes here
			?>
			<div class="wrap wpfzs">
				<h2 class="primary"><?php echo __('WPForms - Zero Spam - Block Emails', 'wpfzs') ?></h2>
				<form method="post" action="options.php">
					<?php settings_fields('wpfzs-be'); //Add necessary hidden fields to the form. ?>
					<table class="form-table">
						<?php do_settings_fields('wpfzs-be', 'default') ?>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		public function admin_page_contents_block_domains_callback() {
			// Callback function to display content goes here
			?>
			<div class="wrap wpfzs option-card">
				<h2 class="primary option-card-title"><?php echo __('WPForms - Zero Spam - Block Domains', 'wpfzs') ?></h2>
				<p class="option-card-body">
				<form method="post" action="options.php">
					<?php settings_fields('wpfzs-bd'); //Add necessary hidden fields to the form. ?>
					<table class="form-table">
						<?php do_settings_fields('wpfzs-bd', 'default') ?>
					</table>
					<?php submit_button(); ?>
				</form>
				</p>
			</div>
			<?php
		}

		public function admin_page_contents_block_keywords_callback() {
			// Callback function to display content goes here
			?>
			<div class="wrap wpfzs">
				<h2 class=" primary"><?php echo __('WPForms - Zero Spam - Block Keywords', 'wpfzs') ?></h2>
				<form method="post" action="options.php">
					<?php settings_fields('wpfzs-bk'); //Add necessary hidden fields to the form. ?>
					<table class="form-table">
						<?php do_settings_fields('wpfzs-bk', 'default') ?>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		public function register_settings() {

			$field = 'wpfzs_debug';
			$args = [
				'type' => 'string',
				'sanitize_callback' => [ $this, 'dropdown_callback'],
				'default' => null
			];

			register_setting( 'wpfzs-se', $field, $args);

			add_settings_field(
				'wpfzs_degug',
				esc_html__('Debug', 'wpfzs'),
				[$this ,'debug_callback'],
				'wpfzs-se');


			$field = 'wpfzs_block_emails';
			$args = [
				'type' => 'string',
				'sanitize_callback' => [ $this, 'serialize_callback'],
				'default' => null
			];

			register_setting( 'wpfzs-be', $field, $args);

			add_settings_field(
				'wpfzs_block_emails',
				esc_html__('Block Emails', 'wpfzs'),
				[$this ,'block_emails_callback'],
				'wpfzs-be');

			$field = 'wpfzs_block_domains';
			$args = [
				'type' => 'string',
				'sanitize_callback' => [ $this, 'serialize_callback'],
				'default' => null
			];

			register_setting( 'wpfzs-bd', $field, $args);

			add_settings_field(
				'wpfzs_block_domains',
				esc_html__('Block Domains', 'wpfzs'),
				[$this ,'block_domains_callback'],
				'wpfzs-bd');	

			$field = 'wpfzs_block_keywords';
			$args = [
				'type' => 'string',
				'sanitize_callback' => [ $this, 'serialize_callback'],
				'default' => null
			];

			register_setting( 'wpfzs-bk', $field, $args);

			add_settings_field(
				'wpfzs_block_keywords',
				esc_html__('Block Keywords', 'wpfzs'),
				[$this ,'block_keywords_callback'],
				'wpfzs-bk');	

		}

		public function dropdown_callback() {
			$value = get_option('wpfzs_block_emails');
			$unserialize = unserialize($value);
			$outputString = str_replace(',', "\n", $unserialize);
			?>
			<textarea class="wpfzs-option-textarea" name="wpfzs_block_emails"><?php echo esc_attr($outputString) ?></textarea>
			<?php
			$numberOfEmails = count(explode("\n", $outputString));
			printf("<div class='info'>There are %s emails blocked.</div>", $numberOfEmails); 
		}

		public function block_emails_callback() 
		// Call back function to write the HTML for display the block_emails textarea
		{
			$value = get_option('wpfzs_block_emails');
			$unserialize = unserialize($value);
			$items = explode(',',$unserialize);
			echo '<ul id="cbox" class="cbox">';
			echo '<li>
				<input type="text" class="search emails" name="searchInput" id="searchInput" placeholder="Search Emails" />
				<input type=button" class="button-secondary emails" id="searchAdd" value="Add Email" />
			</li>';
			foreach($items as $item) {
			?>
			<li>
				<input type="checkbox" class="cbox-item" name="wpfzs_block_emails[]" value="<?php echo esc_attr($item) ?>" checked>
				<label for="ckbox"><?php echo esc_attr($item) ?></label>
			</li>
			<?php
			}
			echo '</ul>';
			$numberOfItems = count($items);
			printf("<div class='info'>There are %s emails blocked.</div>", $numberOfItems); 
		}

		public function block_domains_callback() {
			$value = get_option('wpfzs_block_domains');
			$unserialize = unserialize($value);
			$items = explode(',',$unserialize);		
			echo '<ul id="cbox" class="cbox">';
			echo '<li>
				<input type="text" class="search domains" name="searchInput" id="searchInput" placeholder="Search Domains" />
				<input type=button" class="button-secondary domains" id="searchAdd" value="Add Domain" />
			</li>';
			foreach($items as $item) {
			?>
			<li>
				<input type="checkbox" class="cbox-item" name="wpfzs_block_domains[]" value="<?php echo esc_attr($item) ?>" checked>
				<label for="ckbox"><?php echo esc_attr($item) ?></label>
			</li>
			<?php
			}
			echo '</ul>';
			$numberOfItems = count($items);
			printf("<div class='info'>There are %s domains blocked.</div>", $numberOfItems); 
		}

		public function block_keywords_callback() {
			$value = get_option('wpfzs_block_keywords');
			$unserialize = unserialize($value);
			$items = explode(',',$unserialize);
			echo '<ul id="cbox" class="cbox">';
			echo '<li>
				<input type="text" class="search keywords" name="searchInput" id="searchInput" placeholder="Search Keywords" />
				<input type=button" class="button-secondary keywords" id="searchAdd" value="Add Keyword" />
			</li>';
			foreach($items as $item) {
			?>
			<li>
				<input type="checkbox" class="cbox-item" name="wpfzs_block_keywords[]" value="<?php echo esc_attr($item) ?>" checked>
				<label for="ckbox"><?php echo esc_attr($item) ?></label>
			</li>
			<?php
			}
			echo '</ul>';
			$numberOfItems = count($items);
			printf("<div class='info'>There are %s keywords blocked.</div>", $numberOfItems); 
		}

		/**
		 * Function: serialize_callback
		 *  Input takes an array or a comma delimited list.
		 * Here's what the function does:
		 *	It checks if the input $data is an array using is_array($data). If it is an array, it removes duplicate values.
		 *  If $data is not an array, it replaces newline characters with commas, converts the string to an array, removes duplicate values, and sorts the array.
		 *  The unique and sorted array is then converted back to a comma-separated string.
		 *  Finally, the resulting string is serialized using serialize().
		 */
		public function serialize_callback($data) {
			error_log('Exec serialize_callback');
			if(is_array($data)) {
				$uniqueArray = array_unique($data);
			} else {
				$inputList = preg_replace('/[\r\n]+/', ',', $data);
				$inputArray = explode(',', $inputList);
				$uniqueArray = array_unique($inputArray);
			}
			sort($uniqueArray);
			$outlist = implode(',', $uniqueArray);
			$serializeList = serialize($outlist);
			return $serializeList;
		}
	

		public function register_scripts() {
			wp_register_style( 'wpfzs', WPFORMS_ZEROSPAM_PLUGIN_URL.'assets/css/wpfzs.css' );
			wp_register_script( 'wpfzs', WPFORMS_ZEROSPAM_PLUGIN_URL.'assets/js/admin/admin-wpfzs.js' );
		}
			
		public function load_scripts( $hook ) {
			// Load only on ?page= like wpfzs
			if (is_admin()  === false ) return;
			// Load style & scripts.
			wp_enqueue_style( 'wpfzs' );
			wp_enqueue_script( 'wpfzs' );	
		}
			
			

		/**
		 * Include files.
		 *
		 * @since 1.0.0
		 */
		private function includes() {
		}

		/**
		 * Handle plugin installation upon activation.
		 *
		 * @since 1.7.4
		 */
		public function install() {

		}
	
	/*
		* Check the email address field for blocked emails.
		*
		* @link https://wpforms.com/developers/wpforms_process_validate_email/
		*
		* @param int     $field_id        Field ID.
		* @param array   $field_submit    Unsanitized field value submitted for the field.
		* @param array   $form_data       Form data and settings.
	   */
		
	   public function block_email_address( $field_id, $field_submit, $form_data ) {
		
			error_log('Exec block_email_address');
			error_log('Value: '.$field_submit);
		
			if($this->is_domain_block($field_submit)) {
				add_filter( 'wpforms_entry_email_atts', [$this, 'sendToSpam'], 10, 4 );
			}
			if($this->is_email_block($field_submit)) {
				add_filter( 'wpforms_entry_email_atts', [$this, 'sendToSpam'], 10, 4 );
			}

	   }

	   public function block_email_text( $field_id, $field_submit, $form_data ) {		
			error_log('Exec block_email_text');
			error_log('Value: '.$field_submit);
		
			if($this->is_keyword_block($field_submit)) {
				add_filter( 'wpforms_entry_email_atts', [$this, 'sendToSpam'], 10, 4 );
			}
		
   		}

	   public function is_domain_block($email) {

			error_log('Exec is_domain_block');

			// Use explode to split the email address into an array based on the "@" symbol
			$emailParts = explode("@", $email);

			// Get the last element of the array, which is the domain
			$domain = strtolower('@'.end($emailParts));

			$value = get_option('wpfzs_block_domains');
			
			// Unserialize to same on comma delimited list.
			$list = unserialize($value);
			
			// Convert the list to an array
			$array = explode(",", $list);

			// Check if the value exists in the array
			if (in_array($domain, $array)) {
				error_log('Domain Blocked: '.$domain);
				return true;
			} else {
				return false;
			}

	   		
	   }

	   public function is_email_block($email) {

			error_log('Exec is_email_block');

	   		// Use explode to split the email address into an array based on the "@" symbol
			$email = strtolower($email);

			$value = get_option('wpfzs_block_emails');
			
			// Unserialize to same on comma delimited list.
			$list = unserialize($value);
			
			// Convert the list to an array
			$array = explode(",", $list);

			// Check if the value exists in the array
			if (in_array($email, $array)) {
				error_log('Email Blocked: '.$email);
				return true;
			} else {
				return false;
			}

	   }

	   public function is_keyword_block($text) {

			error_log('Exec is_keyword_block');

			// Use explode to split the email address into an array based on the "@" symbol
			$stringToSearch = strtolower($text);

			$value = get_option('wpfzs_block_keywords');

			// Unserialize to same on comma delimited list.
			$list = unserialize($value);
			
			// Convert the list to an array
			$valuesToFind = explode(",", $list);

			// Create a regular expression pattern
			$pattern = '/' . implode('|', array_map('preg_quote', $valuesToFind)) . '/i';

			// Perform the regular expression match
			if (preg_match_all($pattern, $stringToSearch, $matches)) {
				// $matches[0] contains the matched values
				error_log("Block keywords found: " . print_r($matches,true));
				return true;
			} else {
				error_log('No locked keywords found');
				return false;
			}

	  }

	  function sendToSpam( $email, $fields, $entry, $form_data ) {
		
		error_log('Exec sendToSpam');

		// Use explode to split the email address into an array based on the "@" symbol
		$emailParts = explode("@", $email['address'][0]);

		// Get the last element of the array, which is the domain
		$email['address'][0] = 'spam'.strtolower('@'.end($emailParts));

		error_log($email['address'][0]);

		return $email;

	}

}

	/**
	 * The function which returns the one WPForms instance.
	 *
	 * @since 1.0.0
	 *
	 * @return WPForms\WPForms
	 */
	function wpforms_zerospam() {

		new WPForms_ZeroSpam();
	}