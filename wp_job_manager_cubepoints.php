<?php
/*
Plugin Name: WP Job Manager Cubepoints
Plugin Script: wp_job_manager_cubepoints.php
Plugin URI: http://www.beprosoftware.com/shop
Description: Get paid for job listings on WP Job Manager with cubepoints.
Version: 1.0.0
License: GPL V3
Author: BePro Software Team
Author URI: http://www.beprosoftware.com


Copyright 2012 [Beyond Programs LTD.](http://www.beyondprograms.com/)

Commercial users are requested to, but not required to contribute, promotion, 
know-how, or money to plug-in development or to www.beprosoftware.com. 

This file is part of BePro Listings.

WP Job Manager Cubepoints is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

WP Job Manager Cubepoints is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with BePro Listings.  If not, see <http://www.gnu.org/licenses/>.
*/


add_action("init", "register_paid_jobs");
	
function register_paid_jobs(){
	update_option('cp_module_pjobs_poster_points', 1);
	update_option('cp_module_pjobs_text_poster_insufficient', __('<p>You need %points% to post jobs on this website. You can get points via the form below.</p>', 'cp'));
}




	
	/*
	//
	//
	//        ************** FUNCTIONS
	//
	//
	//
	*/
	
	//subtract points from user who submitted job listing
	function cp_submit_job_form($job_id){
		$post_data = get_post($job_id);
		
		$uid = cp_currentUser();
		$type = "Job";
		$data = $post_data->post_title;
		$current_points = cp_getPoints($uid);
		$use_points = get_option("cp_module_pjobs_poster_points");
		
		$new_points = $current_points - $use_points;
		//update cubepoint records
		cp_updatePoints($uid, $new_points);
		cp_log($type, $uid, $new_points, $data);
	}
	$charge_for_post = get_option("cp_module_pjobs_poster_points");
	if($charge_for_post > 0){
		add_action( 'job_manager_job_submitted', 'cp_submit_job_form' );
	}


	//stop users without enough points or who are logged out from posting
	function cp_check_can_post_job(){
		if(is_user_logged_in()){
			$uid = cp_currentUser();
			$current_points = cp_getPoints($uid);
			if($current_points == 0)
				add_filter("job_manager_user_can_post_job","cp_prevent_job_form_filter_empty");
		}
	}
	//if we are charging to post
	$charge_for_post = get_option("cp_module_pjobs_poster_points");
	if($charge_for_post > 0){
		add_action( 'init', 'cp_check_can_post_job' );
	}

	//empty function for filters
	function cp_prevent_job_form_filter_empty(){}

	//this is a hook in the job submit template. If form is removed because of cp_paid_job_applications() then this fires
	function cp_submit_job_form_disabled(){
		if(is_user_logged_in()){
			echo '<hr /><br /><div style="float:left;font-size:17px;font-weight:bold;background:#E0E0E0;padding:18px;color:#565656;">' . __('My Points', 'cp') . ':</div>
					<div style="float:left;padding:18px;font-size:20px;">' . cp_getPoints(cp_currentUser()) . '</div>';
			echo "<div style='clear:both'><br /></div>";
			$insufficient_message = switch_cp_job_tags("cp_module_pjobs_text_poster_insufficient", false);
			echo $insufficient_message;
			echo do_shortcode("[cp_paypal]");
		}
	}
	add_action('submit_job_form_disabled', "cp_submit_job_form_disabled");
	
	function switch_cp_job_tags($var, $app = true){
		$find = array('%points%');
		if($app){
		
		}else{
			$replace = array(cp_formatPoints(get_option("cp_module_pjobs_poster_points")));
		}
		return str_replace($find,$replace, get_option($var));
	}
