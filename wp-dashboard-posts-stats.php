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
		add_action('wp_ajax_get_visualize_post_data', array($this, 'getDataAjax'));
	}

	public function enqueueScripts(){
		$screen = get_current_screen();
		if ( $screen->base === 'dashboard' ) {
			wp_enqueue_script( 'google-ajaxapi', '//www.google.com/jsapi', array(), static::plugin_version, true );
			wp_enqueue_script( 'visualize-posts-script', plugins_url( '/js/visualize-posts.js', __FILE__ ), array('google-ajaxapi'), static::plugin_version, true );
		}
	}

	public function addDashboardWidget(){
		wp_add_dashboard_widget( 'visualize-post-actions', __('Published posts', 'visualize_post_actions'), array($this, 'dashboardWidgetContent') );
	}

	public function getDataAjax(){
		$data = $this->getData();
		$out = array();
		foreach ( $data as $key => $val ){
			$out[] = array( $key, $val );
		}
		$out = array_reverse($out);
		array_unshift($out, array('Date', 'Published posts'));
		echo json_encode( $out );
		exit;
	}
	private function getData(){
		global $wpdb;
		$out = new stdClass;
		$published = $wpdb->get_results( $wpdb->prepare("SELECT CAST(post_date AS DATE) AS date, COUNT(ID) AS count FROM $wpdb->posts WHERE post_status = 'publish' GROUP BY CAST(post_date AS DATE) ORDER BY post_date DESC LIMIT %d", 30), OBJECT_K );
		// published entries by type
		// SELECT
		// 	CAST(post_date AS DATE) AS date,
		// 	post_type,
		// 	COUNT(post_type) AS post_type_count
		// FROM $wpdb->posts
		// WHERE
		// 	post_status = 'publish'
		// 	AND post_date > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
		// GROUP BY
		// 	CAST(post_date AS DATE), post_type
		// ORDER BY post_date DESC
		$start_date = new DateTime( key($published) );
		$pub_entries = $this->prepareData( $published, 30, true, $start_date );
		return $pub_entries;
	}

	/**
	 * Prepare data points
	 * @param array $data The data points that will be graphed, using keys as dates and count as values
	 * @param int $limit The total amount of data entries to be processed
	 * @param bool $chronological Whether to show days with no activity
	 * @param DateTime $start_date The start date. If empty, the first key will be used
	 * @return array
	 */
	private function prepareData( array $data, $limit = 30, $chronological = true, $start_date = null ){
		$out = array();
		if ( $chronological ) {
			if ( is_null($start_date) ) {
				// if no starting date it's given, use the key for the first data point
				$start_date = new DateTime( key($data) );
			}
			if ( ! $start_date instanceof DateTime ){
				throw new InvalidArgumentException( __('The $start_date parameter must be a DateTime object') );
			}
			$date_index = clone $start_date;
			for ( $i = 0; $i < $limit; $i++ ){
				$this_date = $date_index->format('Y-m-d');
				$out[ $this_date ] = isset( $data[ $this_date ] ) ? (int)$data[ $this_date ]->count : 0;
				$date_index = $date_index->sub( new DateInterval('P1D') );
			}
		} else {
			foreach ( $data as $key => $val ) {
				$this_date = new DateTime( $key );
				$this_date = $this_date->format('Y-m-d');
				$out[ $this_date ] = (int)$val->count;
			}
		}
		return $out;
	}

	public function dashboardWidgetContent(){
		echo '<div id="visualize-posts-canvas" style="height:120px">';
			echo '<span class="description">Loading data&hellip;</span>';
		echo '</div>';
	}
}
// Instantiate the class object

$VisualizePostActions = VisualizePostActions::getInstance();