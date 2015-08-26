<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WordPress_Plugin_Template_Settings {

	/**
	 * The single instance of WordPress_Plugin_Template_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wpt_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// add_action('admin_menu', 'baw_create_menu');



		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

		// $this->handleForm();
	}

	public function displaySubmit(){
		if (isset($_GET["tab"])){
			if (isset($this->settings[$_GET["tab"]]['custom_handler'])){
				return false;
				// print "Diff6erent table here 6";
				// exit();
			}
		}
		return true;
	}

	public function displayCustomForm(){
		 $getstring = $this->settings[$_GET["tab"]]['custom_handler'];
		 $found = false;
		 foreach ($this->settings as $setting => $data) {
		 	if ($setting == $_GET["tab"]){
		 		$found = true;
		 	}
		 	# code...
		 }
		 // print $getstring;
		 $evstring = '$this->'.$getstring.'();';
		 // print $evstring;
		 if ($found)
		 	eval ( $evstring );
		 else{
		 	print "ERROR: Tab not found";
		 }
	}


	public function useCustomFields(){
		if (isset($_GET["tab"])){
			if ($_GET["tab"]=="whitelist"){
				return true;
				// print "Diff6erent table here 6";
				// exit();
			}
		}
		return false;
	}

	public function displayMessage($msg){
		$html = '<br><div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
		<p><strong>'.$msg.'</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	
		print $html;
	}

	public function whitelist_remove(){
		global $wpdb;
		$table_name = $wpdb->prefix . "authusers"; 
		$user_id = $_GET["user_id"];

		$results = $wpdb->get_results( 'DELETE FROM '.$table_name.' WHERE id = '.$user_id);
		$msg = "User Removed from Whitelist";
		$this->displayMessage($msg);
	}

	public function whitelist_add(){
		global $wpdb;
		$email = $_GET["email"];
		$email = mysql_real_escape_string($email);
		$table_name = $wpdb->prefix . "authusers"; 
		$sql = 'INSERT INTO '.$table_name.' (`email`) VALUES ("'.$email.'");';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		$msg =  "User Added to Whitelist";
		$this->displayMessage($msg);
	}

	public function whitelist_display(){
		global $wpdb;
		if ($_GET["action"]=="remove"){
			$this->whitelist_remove();
		}
		if ($_GET["action"]=="add"){
			$this->whitelist_add();
		}
		$table_name = $wpdb->prefix . "authusers"; 
		$results = $wpdb->get_results( 'SELECT count(*) as cnt FROM '.$table_name );
		$results = $results[0];
		$total =$results->cnt;
		$html = '';
		$html.="<br>Total Emails : ".$total."<br><br>";
		
		// $addlink = remove_query_arg( 'action', $addlink );
		$addlink = add_query_arg( array( 'action' => 'add' ) );
		$addlink = remove_query_arg( 'user_id', $addlink );

		$html .= "<form action='".$addlink."' method='GET'>";
		$html .= "Email: <input type='text' name='email'>";
											$html .= '<input type="hidden" name="page" value="' . esc_attr( $_GET["page"] ) . '" />' . "\n";

									$html .= '<input type="hidden" name="tab" value="' . esc_attr( $_GET["tab"] ) . '" />' . "\n";
							$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Add User' , 'wordpress-plugin-template' ) ) . '" />' . "\n";
		$html .= "<input type='hidden' name='action' value='add'>";
		// $html .= "<input type='submit' value='add'>";
		$html .= "</form><br>";
		$html.="<table  class='widefat' style='width:70%;'>
		 <thead>
		 		<tr>
		 				<th>ID.</th>
		 				<th>Email</th>
						<th>Action</th>
		 		</tr>
		 </thead>";
		 $posts_per_page = 25;
		if (!isset($_GET["page_no"])){
			$offset = 0;
		}else{
			$offset = $_GET["page_no"]*$posts_per_page;
		}
		
		

		$totalpages = $total/$posts_per_page;
		
		$pageposts = $wpdb->get_results( 'SELECT * FROM '.$table_name. ' LIMIT '.$offset.','.$posts_per_page  );
		
		 if ($pageposts){

		 	foreach ($pageposts as $post) {
		 		# code...
		 		$deletelink = add_query_arg( array( 'user_id' => $post->id, 'action' => 'remove' ) );
		 		$html.="<tr>";
		 		$html.="<td>".$post->id."</td>";
		 		$html.="<td>".$post->email."</td>";
		 		$html.="<td><a href='".$deletelink."'>Remove</a></td>";
		 		$html.="</tr>";
		 	}
		 }
				$html.="</table>";
		 $html.="<br>Page(s) : &nbsp;";
		 for($y = 0; $y<$totalpages; $y++){
		 	$plink1 = add_query_arg( array( 'page_no' => $y ) );
		 	$plink1 = remove_query_arg( 'action' , $plink1);
		 	$plink1 = remove_query_arg( 'Submit' , $plink1);
		 	$plink1 = remove_query_arg( 'email' , $plink1);
		 	// $plink2 = add_query_arg( array( 'page_no' => $y ) );
		 	if ($y == $_GET["page_no"])
		 		$html.="<b><a href='".$plink1."'>".($y+1)."</a></b>&nbsp";
		 	else
		 		$html.="<a href='".$plink1."'>".($y+1)."</a>&nbsp";
		 }
		 print $html;
		// }

	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'RoadBlock', 
			'wordpress-plugin-template' ) ,
			 __( 'RoadBlock', 'wordpress-plugin-template' ) , 
			 'manage_options' , 
			 $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) 
			 );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );




	}




	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wordpress-plugin-template' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}


	public function getCustomOptions() {
	    return array(
	        //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
	        'PrevCallResult' => array(__('A text option', 'previous-call-result')),
	        // 'Donated' => array(__('I have donated to this plugin', 'my-awesome-plugin'), 'false', 'true'),
	        // 'CanSeeSubmitData' => array(__('Can See Submission data', 'my-awesome-plugin'),
	        //                             'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber', 'Anyone')
	    );
}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {
// text_field_titlesite
		$lplink = wp_lostpassword_url();
		$settings['standard'] = array(
			'title'					=> __( 'Settings', 'wordpress-plugin-template' ),
			'description'			=> __( 'RoadBlock configuration panel.', 'wordpress-plugin-template' ),
			'fields'				=> array(
				array(
					'id' 			=> 'text_field',
					'label'			=> __( 'Image' , 'wordpress-plugin-template' ),
					'description'	=> __( 'Image URL', 'wordpress-plugin-template' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> __( 'Image URL', 'wordpress-plugin-template' )
				),
				array(
					'id' 			=> 'text_field_minilogo',
					'label'			=> __( 'Mini Logo Image' , 'wordpress-plugin-template' ),
					'description'	=> __( 'Mini Image URL', 'wordpress-plugin-template' ),
					'type'			=> 'image',
					'default'		=> '',
					'placeholder'	=> __( 'Mini Image URL', 'wordpress-plugin-template' )
				),
					
				array(
					'id' 			=> 'text_field_titlesite',
					'label'			=> __( 'Title of Roadblock' , 'wordpress-plugin-template' ),
					'description'	=> __( 'Page title: <title></title>', 'wordpress-plugin-template' ),
					'type'			=> 'text',
					'default'		=> 'RoadBlock',
					'placeholder'	=> __( 'Title of Roadblock', 'wordpress-plugin-template' )
				),
				array(
					'id' 			=> 'text_field_secret',
					'label'			=> __( 'Recaptcha Secret Key' , 'wordpress-plugin-template' ),
					'description'	=> __( 'Recaptcha Secret Key', 'wordpress-plugin-template' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Recaptcha Secret Key', 'wordpress-plugin-template' )
				),
				array(
					'id' 			=> 'text_field_captcha_site',
					'label'			=> __( 'Recaptcha Site Key' , 'wordpress-plugin-template' ),
					'description'	=> __( 'Recaptcha Site Key', 'wordpress-plugin-template' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'Recaptcha Site Key', 'wordpress-plugin-template' )
				),
				array(
					'id' 			=> 'text_field_redirect',
					'label'			=> __( 'Lost Password Link' , 'wordpress-plugin-template' ),
					'description'	=> __( 'Lost Password Link', 'wordpress-plugin-template' ),
					'type'			=> 'text',
					'default'		=> $lplink,
					'placeholder'	=> __( 'Lost Password Link', 'wordpress-plugin-template' )
				),
							
			)
		);

		$settings['whitelist'] = array(
			'title'					=> __( 'Whitelist', 'wordpress-plugin-template' ),
			'description'			=> __( 'Emails on the Whitelist.', 'wordpress-plugin-template' ),
			'custom_handler'		=> 'whitelist_display',
			'fields'				=> array(
				array(
					'id' 			=> 'text_field_emails',
					'label'			=> __( 'Emails' , 'wordpress-plugin-template' ),
					'description'	=> __( 'Image URL', 'wordpress-plugin-template' ),
					'type'			=> 'textarea',
					'default'		=> 'http://jobardev.com/wp-content/themes/jobar-inc/images/logo.png',
					'placeholder'	=> __( 'Image URL', 'wordpress-plugin-template' )
				)
			)
		);

		// ();
		$options = $this->getCustomOptions();
		if (!empty($options)) {
			foreach ($options as $key => $arr) {
				if (is_array($arr) && count($arr > 1)) {
					add_option($key, $arr[1]);
				}
			}
		}

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'RoadBlock' , 'wordpress-plugin-template' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					$tab_link = remove_query_arg( 'action' , $tab_link);
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}


				// Get settings fields
				ob_start();
				if ($this->useCustomFields()){
					$this->displayCustomForm();
				}else{
								$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

					settings_fields( $this->parent->_token . '_settings' );
					do_settings_sections( $this->parent->_token . '_settings' );
				}
				
				$html .= ob_get_clean();
				// $html .= '<br>Endpoint URL : '.get_option( $this->base.'text_field' );
					if ($this->displaySubmit()){
						$html .= '<p class="submit">' . "\n";
							$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
							$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'wordpress-plugin-template' ) ) . '" />' . "\n";
						$html .= '</p>' . "\n";
					}
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main WordPress_Plugin_Template_Settings Instance
	 *
	 * Ensures only one instance of WordPress_Plugin_Template_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WordPress_Plugin_Template()
	 * @return Main WordPress_Plugin_Template_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}