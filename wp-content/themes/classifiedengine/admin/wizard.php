<?php
class ET_AdminWizard extends ET_AdminMenuItem {

	function __construct(){
		parent::__construct('et-wizard',  array(
			'menu_title'	=> __('Setup wizard', ET_DOMAIN),
			'page_title' 	=> __('SETUP WIZARD', ET_DOMAIN),
			'callback'   	=> array($this, 'menu_view'),
			'slug'			=> 'et-wizard',
			'page_subtitle'	=> __('ClassifiedEngine Wizard', ET_DOMAIN),
			'pos' 			=> 13,
			'icon_class'	=> 'icon-help'
		));
		$this->add_action('admin_init', 'remove_notices');
		$this->add_action('et_admin_localize_scripts', 'localize_script');
		$this->add_ajax('et-insert-sample-data', 'et_insert_sample_data' , true , false);
		$this->add_ajax('et-delete-sample-data', 'et_delete_sample_data',true,false);
		
	}
	function on_add_scripts() {
		$this->add_script( 'et_wizard', TEMPLATEURL.'/js/admin/wizard.js', array('jquery','jquery-ui-sortable',  'underscore', 'backbone', 'ce') );
	}

	function on_add_styles() {
		$this->add_existed_style('admin.css');
	}

	public function remove_notices(){
		if (isset($_GET['page']) && $_GET['page'] == 'et-wizard'){
			update_option( 'et_wizard_status',1);
		}
	}
	public function localize_script ($slug) {		
		if($slug == 'et-wizard') {
			wp_localize_script( 
				'et_wizard', 
				'et_wizard', 
				array(
					'insert_sample_data' => __("Insert sample data", ET_DOMAIN),
					'delete_sample_data' => __("Delete sample data", ET_DOMAIN),
					'insert_fail' 		 => __('Insert sample data false',ET_DOMAIN),
					'delete_fail' 		 => __('Delete sample data false',ET_DOMAIN),
					'wr_uploading' 		 => __("It may take a few minutes for the images to get uploaded to the server. Please don't close or reload this page.")
					)
				);
			
		}
	}

	function et_insert_sample_data(){	
		
		$response = array('success' => false, 'data' => "", 'updated_op' => get_option('option_sample_data'));

		if ( !$response['updated_op'] ) {
			update_option( 'option_sample_data', true);			
			require_once get_template_directory()  . '/includes/ce_import.php';
			$import_xml = new CE_Import_XML();
			$import_xml->dispatch();
			$response = array('success' => true, 'data' => "", 'updated_op' => true);
		}
		
		wp_send_json($response);		
	}

	public function et_delete_sample_data(){
			
		$response = array('success' => false, 'data' => '', 'updated_op' => get_option('option_sample_data'));
		if ( $response['updated_op'] ) {
			delete_option( 'option_sample_data');			
			require_once get_template_directory()  . '/includes/ce_import.php';
			$import_xml = new CE_Import_XML();
			$import_xml->depatch();
			$response = array('success' => true, 'data' => '', 'updated_op' => false);
		}		
		wp_send_json($response);
		
	}

	
	public function get_header(){
		?>
		<div class="et-main-header">
			<div class="title font-quicksand"><?php //echo $this->page_title ?></div>
			<div class="desc"><?php //echo $this->page_subtitle ?></div>
			
			<div class="wizard-step" ></div>
		</div>
		<?php
	}

	/**
	 * Render view for payment item 
	 * @since 1.0
	 */
	public function menu_view($args ){
		?>
		<div class="et-main-header">
			<div class="title font-quicksand"><?php echo $args->menu_title ?></div>
			<div class="desc"><?php _e("Guide to configure your website", ET_DOMAIN); ?></div>
		</div>
		<style>.et-main-main .desc .form .form-item span.notice {color : #E0040F;}</style>
		<div id= "wizard-sample-data" class="et-main-content">
			
			<div class="settings-content et-main-main">
				
				<div class="title font-quicksand">Install sample data</div>
				<div class="desc">
					Insert our sample data to see how your website works. We highly recommend using this function only when you have not posted your own data yet.		
					<div class="btn-language padding-top10 f-left-all">
					<?php  
						$sample_data_op = get_option('option_sample_data');
						if (!$sample_data_op) {
							echo '<button class="primary-button" id="install_sample_data">'.__("Install sample data", ET_DOMAIN).'</button>';
						}
						else{
							echo '<button class="primary-button" id="delete_sample_data">'.__("Delete sample data", ET_DOMAIN).'</button>';
						}
					?>
					</div>
				</div>
			</div>
			<?php if(CE_AD_POSTTYPE == 'product') { ?>
			<div class="settings-content et-main-main">
				
				<div class="title font-quicksand">Update database</div>
				<div class="desc">
					Version 1.8.5 has been updated to be compatible with WooEcommerce. 
					Update the database to get the latest conversion.
					<div class="btn-language padding-top10 f-left-all">
						<button class="primary-button" id="update_database">Update database</button>
					</div>
				</div>
			</div>
			<?php } ?>
			<!-- <div class="settings-content et-main-main">
				
				<div class="title font-quicksand">Reverse database</div>
				<div class="desc">
					Version 1.8.5 has been updated to be compatible with WooEcommerce. 
					Update the database to get the latest conversion.
					<div class="btn-language padding-top10 f-left-all">
						<button class="primary-button" id="reverse_database">Reverse database</button>
					</div>
				</div>
			</div> -->
		</div>
		<?php
		//echo $this->get_footer();
	}

}
