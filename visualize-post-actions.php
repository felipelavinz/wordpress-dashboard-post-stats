<?php
/*
Plugin Name: Visualize post actions
Plugin URI: http://www.yukei.net
Description: Graph created/published/edited posts over time on a dashboard widget
Version: 0.1
Author: Felipe Lavín
Author URI: http://www.yukei.net
License: GPL3
*/

class VisualizePostActions{

	private static $instance;

	const ddbb_version = 1;
	const plugin_version = 1;

	private function __construct(){
		$this->setActions();
	}
	public static function getInstance(){
		if ( !isset(self::$instance) ){
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	public function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	/**
	 * Setup WordPress actions
	 */
	private function setActions(){
		add_action('wp_dashboard_setup', array($this, 'addDashboardWidget'));
		add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));
	}

	public function enqueueScripts(){
		$screen = get_current_screen();
		if ( $screen->base === 'dashboard' ) {
			wp_enqueue_script( 'google-ajaxapi', '//www.google.com/jsapi', array(), static::plugin_version, true );
			wp_enqueue_script( 'visualize-posts-script', plugins_url( '/js/visualize-posts.js', __FILE__ ), array('google-ajaxapi'), static::plugin_version, true );
			wp_localize_script( 'visualize-posts-script', 'VisualizePosts', $this->getData() );
		}
	}

	public function addDashboardWidget(){
		wp_add_dashboard_widget( 'visualize-post-actions', __('Post actions', 'visualize_post_actions'), array($this, 'dashboardWidgetContent') );
	}

	private function getData(){
		global $wpdb;
		$out = new stdClass;
		$published = $wpdb->get_results( $wpdb->prepare("SELECT CAST(post_date AS DATE) AS date, COUNT(ID) AS count FROM $wpdb->posts GROUP BY CAST(post_date AS DATE) ORDER BY post_date DESC LIMIT 30"), OBJECT_K );
		$start_date = new DateTime( key($published) );
		$pub_entries = array();
		if ( $published ) {
			$date_index  = clone $start_date;
			for ( $i=0; $i<60; $i++ ){
				$this_date = $date_index->format('Y-m-d');
				$pub_entries[ $this_date ] = isset( $published[ $this_date ] ) ? (int)$published[ $this_date ]->count : 0;
				$date_index = $date_index->sub( new DateInterval('P1D') );
			}
		}
		$out->published = $pub_entries;
		return $out;
	}

	public function dashboardWidgetContent(){
		echo '<pre>', print_r($published, true) ,'</pre>';
	}
}
// Instantiate the class object

$VisualizePostActions = VisualizePostActions::getInstance();