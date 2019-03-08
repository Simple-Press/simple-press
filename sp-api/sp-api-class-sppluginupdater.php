<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allows plugins to use their own update API.
 *
 * @author Easy Digital Downloads
 * 
 * @version 1.6.17
 */

class SPPluginUpdater {

	private $api_url     = '';
	private $api_data    = array();
	private $name        = '';
	private $slug        = '';
	private $version     = '';
	protected $API_action = null;

	private $health_check_timeout = 5;

	/**
	 * Class constructor.
	 *
	 * @uses plugin_basename()
	 * @uses hook()
	 *
	 * @param string  $_api_url     The URL pointing to the custom API endpoint.
	 * @param string  $_plugin_file Path to the plugin file.
	 * @param array   $_api_data    Optional data to send with API calls.
	 */
	 
	public function __construct( $_api_url, $_plugin_file, $_api_data = null ) {

		$this->api_url     = trailingslashit( $_api_url );
		$this->api_data    = $_api_data;
		$this->name        = plugin_basename( $_plugin_file );
		$this->slug        = basename( $_plugin_file, '.php' );
		$this->version     = $_api_data['version'];
		$this->beta        = ! empty( $this->api_data['beta'] ) ? true : false;
		$this->API_action   = ! empty( $_api_data['API_action'] ) ? $_api_data['API_action'] : false;

	}
	
	/**
	 * Makes a call to the API.
	 *
	 * @since 1.0.0
	 *
	 * @param array $api_params to be used for wp_remote_get.
	 * @return array $response decoded JSON response.
	 */
	 public function get_api_response( $api_params ) {

		 // Call the custom API.
		 
		$response = wp_remote_get(
			esc_url_raw( add_query_arg( $api_params, $this->api_url ) ),
			array( 'timeout' => 15, 'sslverify' => true )
		);

		// Make sure the response came back okay.
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$response = json_decode( wp_remote_retrieve_body( $response ) );

		return $response;
 	}
	
	public function check_for_addon_update(){
		
		$api_params = array(
				
			'edd_action' 	=> $this->api_data['API_action'],
			'license'    	=> isset( $this->api_data['license'] ) ? urlencode( $this->api_data['license'] ) : '123456789',
			'version'   	=> $this->api_data['version'],   // current version number
			'item_name'  	=> isset( $this->api_data['item_name'] ) ? trim( $this->api_data['item_name'] ) : false,
			'item_id'    	=> isset( $this->api_data['item_id'] ) ? trim( $this->api_data['item_id'] ) : false,
			'author'    	=> 'Simple:Press',  // author of this plugin
		);
		
		$license_data = $this->get_api_response( $api_params );
		
		return $license_data;
		
	}
	
	/*function for check current plugins status and update to database */
	
	public function check_addons_status($data = array())
	{
		$this->api_data['edd_action'] = $data['edd_action'];
		
		$response = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => true, 'body' => $this->api_data ) );
		
		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		
			return false;
		}
		
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		if(isset($data['status']) && $data['status'] != ''){
			
			return $license_data;
			
		}else{
			
			$addon_info = json_decode(wp_remote_retrieve_body( $response ));
			
			$addon_info->item_name = sanitize_title_with_dashes($addon_info->item_name);
			
			$update_info_option = json_encode($addon_info);
			
			$check_version = $this->check_for_addon_update();

			if(isset($license_data->license)) {

				if($license_data->license != 'expired'){

					$download_link = isset( $check_version->download_link ) ? $check_version->download_link : '';
				}else{

					$download_link = '';
				}
			
				$update_version_option = array(
					
					'new_version'=> isset( $check_version->new_version ) ? $check_version->new_version : '',
					'stable_version'=> isset( $check_version->stable_version ) ? $check_version->stable_version : '',
					'name'=>isset( $check_version->name ) ? sanitize_title_with_dashes($check_version->name) : '',
					'slug'=>isset( $check_version->slug ) ? $check_version->slug : '',
					'url'=>isset( $check_version->url ) ? $check_version->url : '',
					'homepage'=>isset( $check_version->homepage ) ? $check_version->homepage : '',
					'last_updated'=>isset( $check_version->last_updated ) ? $check_version->last_updated : '',
					'download_link'=> $download_link,
					'package' => isset( $check_version->package ) ? $check_version->package : '',
					'icons'=>isset( $check_version->icons ) ? $check_version->icons : '',
					'banners'=>isset( $check_version->banners ) ? $check_version->banners : '',
					'sections'=>isset( $check_version->sections ) ? $check_version->sections : ''
				);
			
				// save status to option table
				SP()->options->update( $data['update_status_option'], $license_data->license );
				
				// save info to option table
				SP()->options->update( $data['update_info_option'], isset( $update_info_option ) ? $update_info_option : '');
				
				// save update_version_option to option table
				SP()->options->update( $data['update_version_option'], json_encode($update_version_option) );

				return $license_data;
			}
		}
	}

	/**
	 * Disable SSL verification in order to prevent download update failures
	 *
	 * @param array   $args
	 * @param string  $url
	 * @return object $array
	 */
	public function http_request_args( $args, $url ) {

		$verify_ssl = $this->verify_ssl();
		if ( strpos( $url, 'https://' ) !== false && strpos( $url, 'edd_action=package_download' ) ) {
			$args['sslverify'] = $verify_ssl;
		}
		return $args;

	}
	
	/**
	 * Calls the API and, if successfull, returns the object delivered by the API.
	 *
	 * @uses get_bloginfo()
	 * @uses wp_remote_post()
	 * @uses is_wp_error()
	 *
	 * @param string  $_action The requested action.
	 * @param array   $_data   Parameters for the API action.
	 * @return false|object
	 */
	private function api_request( $_action, $_data ) {

		global $wp_version;

		$data = array_merge( $this->api_data, $_data );

		if ( $data['slug'] != $this->slug ) {
			return;
		}

		if( $this->api_url == home_url() ) {
			return false; // Don't allow a plugin to ping itself
		}

		$api_params = array(
			'edd_action' => 'get_version',
			'license'    => ! empty( $data['license'] ) ? $data['license'] : '',
			'item_name'  => isset( $data['item_name'] ) ? $data['item_name'] : false,
			'item_id'    => isset( $data['item_id'] ) ? $data['item_id'] : false,
			'slug'       => $data['slug'],
			'author'     => $data['author'],
			'url'        => home_url()
		);

		$request = wp_remote_post( $this->api_url, array( 'timeout' => 15, 'sslverify' => true, 'body' => $api_params ) );

		if ( ! is_wp_error( $request ) ) {
			$request = json_decode( wp_remote_retrieve_body( $request ) );
		}

		if ( $request && isset( $request->sections ) ) {
			$request->sections = maybe_unserialize( $request->sections );
		} else {
			$request = false;
		}

		return $request;
	}

}