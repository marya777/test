<?php 
class CE_Schedule extends ET_Base {

	static protected $cron_time	;
	static protected $cron_name	=	'ce_arhive_ad';
	static protected $cron_hook	=	'ce_ads_archived_expireds';

	static $post_type			=	CE_AD_POSTTYPE;

	function __construct() {
		$this->add_filter( 'cron_schedules',  'add_cron_time');
		$this->add_action('init', 'schedule_events', 100);

		$this->add_action( self::$cron_hook,'archive_ad' );

		self::$cron_time	=	3600*4;
	}
	/**
	 * register a cron for run schedule archive expired ads
	*/
	function add_cron_time () {
		$schedules[self::$cron_name] = array(
	 		'interval' =>  self::$cron_time ,
	 		'display' => 'ClassifiedEngine Archive Expired Ad cron time'
	 	);
	 	return $schedules;
	}

	function schedule_events () {
		// echo date('Y-m-d 00:00:00', wp_next_scheduled(self::$cron_hook));
		// wp_clear_scheduled_hook( self::$cron_hook );
		
		if ( !wp_next_scheduled( self::$cron_hook ) ){
			$tomorrow = strtotime( date( 'Y-m-d 00:00:00', strtotime('now')) );
			wp_schedule_event( time() , self::$cron_name, self::$cron_hook );
		}
		// $a	=	get_option( 'je_schedule_log' );
		// echo "<pre>";
		// print_r($a) ;
		// echo "</pre>";
	}	

	/**
	 * archive expired ad
	*/
	public  function archive_ad () {
		global $wpdb, $et_global, $post;
		$current = date('Y-m-d H:i:s', current_time('timestamp') );
		$sql = "SELECT DISTINCT ID FROM {$wpdb->posts} as p 
				INNER JOIN {$wpdb->postmeta} as mt ON mt.post_id = p.ID AND mt.meta_key = 'et_expired_date' 
				WHERE 	(p.post_type = '{CE_AD_POSTTYPE}') 	AND
						(p.post_status = 'publish') 			AND
						(mt.meta_value < '{$current}')			AND 
						(mt.meta_value != '' ) " ;

		$archived_ads = $wpdb->get_results($sql);
		
		$count = 0;

		$ar	=	array();
		foreach ($archived_ads as $key =>  $ad) {
			// perform approval for found job
			// $return	=	CE_Ads::update( array( 'ID' => $ad->ID , 'post_status' => 'archive', 'change_status' => 'change_status' ));
			wp_update_post( array( 'ID' => $ad->ID , 'post_status' => 'archive') );
			$count++;
			$ar[]	=	  $ad->ID;
			update_option ('je_schedule_log', $ar);
		}
		//update_option ('je_schedule_log', $current );
		return $count;
	}

}



