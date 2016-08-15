<?php 
/**
 * Contains some quick method for post type
 */
class ET_PostType extends ET_Base{

	static $instance;
	public $name;
	public $args;
	public $taxonomy_args;
	public $meta_data;

	function __construct($name, $args, $taxonomy_args, $meta_data){
		$this->name 			= $name;
		$this->args 			= $args;
		$this->taxonomy_args 	= $taxonomy_args;
		$this->meta_data 		= $meta_data;
	}

	/**
	 * Init post type by registering post type and taxonomy
	 */
	static public function _init($name, $args, $taxonomy_args){
		// register post type
		register_post_type( 
			$name, 
			$args
		);
		// register taxonomies	
		if (!empty($taxonomy_args)){
			foreach ($taxonomy_args as $tax_name => $args) {
				register_taxonomy( $tax_name, array($name), $args );
			}
		}
	}

	protected function trip_meta($data){
        // trip meta datas
        $args = $data;
		$meta = array();
		foreach ($args as $key => $value) {
			if ( in_array($key, $this->meta_data) ){
				$meta[$key] = $value;
				unset($args[$key]);
			}
		}

		return array(
			'args' 	=> $args,
			'meta' 	=> $meta
		);
	}

	/**
	 * Insert post type data into database
	 */
	protected function _insert($args){
		global $current_user;

		$args = wp_parse_args( $args, array(
            'post_type'     => $this->name, 
            'post_status'   => 'pending'
        ) );
        // filter args
        $args = apply_filters( 'et_pre_insert_' . $this->name, $args );
        $data = $this->trip_meta($args);
        
        $result = wp_insert_post( $data['args'], true );

        if ($result != false || !is_wp_error( $result )){
        	foreach ($data['meta'] as $key => $value) {
        		update_post_meta( $result, $key, $value );
        	}

        	// do action here
        	do_action('et_insert_' . $this->name, $result);
        }

        return $result;
	}

	/**
	 * Update post type data in database
	 */
	protected function _update($args){
		global $current_user;

		$args = wp_parse_args( $args );

		// filter args
        $args = apply_filters( 'et_pre_update_' . $this->name, $args );

		// if missing ID, return errors
        if (empty($args['ID'])) return new WP_Error('et_missing_ID', __('Ad not found!', ET_DOMAIN));

        // separate default data and meta data
        $data = $this->trip_meta($args); 
    	// insert into database
        $result = wp_update_post( $data['args'], true );
        // insert meta data
        if ($result != false || !is_wp_error( $result )){
        	foreach ($data['meta'] as $key => $value) {
        		update_post_meta( $result, $key, $value );
        	}
        	if( !current_user_can('manage_categories') && isset( $data['args']['tax_input'][CE_AD_CAT] ) ){
        		$tax_ids 	= $data['args']['tax_input'][CE_AD_CAT];
        		$tax_ids 	= array_map( 'intval', $tax_ids );
				$tax_ids 	= array_unique( $tax_ids );
        		$tax 		= wp_set_post_terms($result, $tax_ids, CE_AD_CAT);
        	}
        	// make an action so develop can modify it
        	do_action('et_update_' . $this->name, $result);
        }

        return $result;
	}

	protected function _delete($ID, $force_delete = false){
		if ( $force_delete ){
			$result = wp_delete_post( $ID, true );
		} else {
			$result = wp_trash_post( $ID );
		}
		if ( $result )
			do_action('et_delete_' . $this->name, $ID);
	}

	protected function _update_field($id, $field_name, $value){
		update_post_meta( $id, $field_name, $value );
	}

	protected function _get_field($id, $field_name){
		return get_post_meta( $id, $field_name, true );
	}

	/**
	 * Get post type data by ID
	 */
	public function _get($id, $raw = false){
		$post = get_post($id);
		if ( $raw )
			return $raw;
		else 
			return $this->_convert($post);
	}

	public function _convert($post, $taxonomy = true, $meta = true){
		$result = (array)$post;
		
		// generate taxonomy
		if ( $taxonomy ){
			if(!empty($this->taxonomy_args))
			foreach ($this->taxonomy_args as $name => $args) {
				$result[$name]	 = wp_get_object_terms( $result['ID'], $name );
			}
		}

		// generate meta data
		if ( $meta ){
			foreach ($this->meta_data as $key) {
				$result[$key] 	= get_post_meta( $result['ID'], $key, true );
			}
		}
		$result['post_date_ce'] ='';
		$post_date  			= get_post_meta($result['ID'],'post_date',true);
		if(!empty($post_date))		
		$result['post_date_ce'] = get_the_date($post_date);

		$result['the_excerpt']  = apply_filters( 'the_excerpt' , $post->post_excerpt );

		return (object)$result;
	}
}
