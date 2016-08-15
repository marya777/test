<?php
require_once dirname(__FILE__).'/overview.php';
require_once dirname(__FILE__).'/settings.php';
require_once dirname(__FILE__).'/sellers.php';
require_once dirname(__FILE__).'/payments.php';
require_once dirname(__FILE__).'/wizard.php';
require_once dirname(__FILE__).'/api-settings.php';
require_once dirname(__FILE__).'/extensions.php';
/**
 * class ET_ClassifiedAdmin
 * control backend settings
*/

class ET_ClassifiedAdmin extends ET_Base {
	
	function __construct() {
		$this->add_action( 'init', 'menu_view');	
		
		new ET_AdminOverview ();
		new ET_AdminSettings ();
		new ET_AdminSellers ();
		new ET_Adminpayments();
		new ET_AdminWizard();
		new ET_AdminAPI();
		new ET_MenuExtensions();
		$this->add_action('admin_footer','admin_footer_style' );
		$this->add_action('tgmpa_register', 'ce_required_plugins');
	}
	
	/**
	 * Register the required plugins for this theme.
	 *
	 * In this example, we register two plugins - one included with the TGMPA library
	 * and one from the .org repo.
	 *
	 * The variable passed to tgmpa_register_plugins() should be an array of plugin
	 * arrays.
	 *
	 * This function is hooked into tgmpa_init, which is fired within the
	 * TGM_Plugin_Activation class constructor.
	 */
	function ce_required_plugins() {
	    $license_key = get_option('et_license_key', '');
	    $plugins = array( 
	      
	        array(
                'name' => 'Revolution Slider Plugin',
                'slug' => 'revslider',
                'source' => 'http://www.enginethemes.com/files/revslider.zip',
                'required' => false,
                'version' => '4.5.95',
                'force_activation' => false,
                'force_deactivation' => true,
                'external_url' => 'http://www.enginethemes.com/files/revslider.zip',
            )
	        
	    );
	 
	    /**
	     * Array of configuration settings. Amend each line as needed.
	     * If you want the default strings to be available under your own theme domain,
	     * leave the strings uncommented.
	     * Some of the strings are added into a sprintf, so see the comments at the
	     * end of each line for what each argument will be.
	     */
	    $config = array(
	        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
	        'menu'         => 'tgmpa-install-plugins', // Menu slug.
	        'has_notices'  => true,                    // Show admin notices or not.
	        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
	        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
	        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
	        'message'      => '',                      // Message to output right before the plugins table.
	        'strings'      => array(
	            'page_title'                      => __( 'Install Required Plugins', 'ET_DOMAIN' ),
	            'menu_title'                      => __( 'Install Plugins', 'ET_DOMAIN' ),
	            'installing'                      => __( 'Installing Plugin: %s', 'ET_DOMAIN' ), // %s = plugin name.
	            'oops'                            => __( 'Something went wrong with the plugin API.', 'ET_DOMAIN' ),
	            'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s).
	            'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s).
	            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
	            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
	            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
	            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
	            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s).
	            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
	            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
	            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
	            'return'                          => __( 'Return to Required Plugins Installer', 'ET_DOMAIN' ),
	            'plugin_activated'                => __( 'Plugin activated successfully.', 'ET_DOMAIN' ),
	            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'ET_DOMAIN' ), // %s = dashboard link.
	            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
	        )
	    );
	 
	    tgmpa( $plugins, $config );
	 
	}

	function menu_view () {
		global $et_admin_page;

		wp_register_style( 'admin.css', TEMPLATEURL.'/css/admin.css', array(), '1.0' );

		$et_admin_page = new ET_EngineAdminMenu();

		do_action('et_admin_menu');

	}
	function admin_footer_style(){?>
		<style type="text/css">
		.rs-update-notice-wrap{display: none;}
		#rs-validation-wrapper{display: none;}
		#viewWrapper .title_line{display: none !important;}

		</style>
		<script type="text/javascript">
		(function($) {
			$(document).ready(function() {
				$("#benefitscontent").next().hide();
				
			});
		})(jQuery)
		</script>
	<?php }

}

if(is_admin()) {
	new ET_ClassifiedAdmin ();
}


/*
 * display enable/disable payment gateway button
 */

function et_display_enable_disable_button ( $option, $label, $option_type = "payment" ) {
	$enable	=	false;
	switch ($option_type) {
		case 'payment':
			$payment_gateways	=	et_get_enable_gateways();
			if( !is_array($payment_gateways)) {
				$payment_gateways	=	array ();
			}
			
			if( isset ($payment_gateways[$option]) && $payment_gateways[$option]['active'] != -1 ) {
				$enable	=	true;
			}
			break;
		case 'payment_test_mode':			
			if( et_get_payment_test_mode () ) $enable	=	true ;			
			break;

		case 'payment_disable': 
			if( et_get_payment_disable () ) $enable	=	true ;
			break;


		case 'use_captcha': 
			$ce_option = new CE_Options();
			if( $ce_option->use_captcha() ) 
				$enable = true;
			break;

		case 'facebook_login':
			$ce_option 			= new CE_Options();
			$facebook_login	 	= $ce_option->get_option('et_facebook_login');
			if($facebook_login)
				$enable = true;
			break;
		case 'twitter_login':
			$ce_option 			= new CE_Options();
			$twitter_login	 	= $ce_option->get_option('et_twitter_login');
			if($twitter_login)
				$enable = true;
			break;
		default :
			$enable = false;
		
	}
	
	if( $enable ) {
	?>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>" data= "<?php echo $option;?>" class="toggle-button deactive">
			<span><?php _e("Disable", ET_DOMAIN);?></span>
		</a>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>"  data= "<?php echo $option;?>" class="toggle-button active selected">
			<span><?php _e("Enable", ET_DOMAIN);?></span>
		</a>
	<?php 
	} else { // disable
	?>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>"  data= "<?php echo $option;?>" class="toggle-button deactive selected">
			<span><?php _e("Disable", ET_DOMAIN);?></span>
		</a>
		<a href="#" rel="<?php echo $option?>" title="<?php echo $label ?>" data= "<?php echo $option;?>" class="toggle-button active">
			<span><?php _e("Enable", ET_DOMAIN);?></span>
		</a>
	<?php 
	}
}