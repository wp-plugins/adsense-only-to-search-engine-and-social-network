<?php
/*
Plugin Name: Display Ads/AdSense Only to Search Engine Visitors
Plugin URI: http://cbnewsplus.com
Description: it seems that Search Engine visitors are more likely to click on targeted advertisements then your regular readers. With this plugin you can choose if you want or not show AdSense only to Search Engine Visitors. and social networking, such as Facebook, Twitter and etc.
Author: Cilene Bonfim
Version: 0.21
Author URI: http://cbnewsplus.com
*/


if ( !defined( 'ABSPATH' ) ) exit; 

if(!isset($_SESSION)) {
     session_start();
}



if ( !class_exists( 'SearchEngineVisitors' ) ) {

	class SearchEngineVisitors {

		function __construct() {
			register_activation_hook(__FILE__, array( $this, 'sev_install'));
			add_action('admin_menu', array( $this,'searchenginevisitors_plugin_menu'));
			add_action('init', array( $this, 'searchenginevisitors'));
			add_filter('the_content',  array( $this, 'visitor_sev_filter'), 25);
			add_filter('the_excerpt',  array( $this, 'visitor_sev_filter'), 25);
			add_action( 'add_meta_boxes',  array( $this, 'searchenginevisitors_meta_box') );
			add_action( 'save_post',   array( $this, 'sev_meta_post_save'));
		}
	
		
	
		public function sev_install(){
			add_option('sev_code', '');
			add_option('sev_position', 'bottom');
		}
		function plugin_path() {
			if ( $this->plugin_path ) return $this->plugin_path;
			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		function template_url() {
			return $this->template_url	= plugins_url(). '/search-engine-visitors';
		}
	
		public function searchenginevisitors_plugin_menu() {
			add_menu_page('AdSense SEV', 'AdSense SEV', 'sev_others_posts', 'sev-menu',  array( $this,'sev_main'));
			add_submenu_page('sev-menu', __('Setting','sevlang'), __('Setting','sevlang'), 'administrator',  'sev-setting', array( $this, 'sev_setting'));
		}

		function sev_main(){
		}
		
		public function sev_setting(){
			
              $sev_position_array = Array (
				 'top'=>'Top',
				 'top-middle'=>'Top Middle',
				 'top-left'=>'Top Left',
				 'top-right'=>'Top Right',
				 'bottom' => 'Bottom',
				 'bottom-middle'=>'Bottom Middle',
				 'bottom-left'=>'Bottom Left',
				 'bottom-right'=>'Bottom Right',
			 );
	
	
			$content = "
			<h2>AdSense SEV Setting</h2>
			<form method='post' action='options.php'>";
			echo $content;
				
				wp_nonce_field('update-options'); 
				
			$content = "<hr>";				
			$content .= "<label for=''>".__('Code #1:','sevlang')."</label><br />";
			$content .= "<textarea name='sev_code' rows='7' id='sev_code' class='ui-widget-content ui-corner-all' cols='100'>".get_option('sev_code')."</textarea>";		
			$content .= "<br /><div class='clear'></div><label for=''>".__('Position:','sevlang')."</label><br />";

			$content .= "<select name='sev_position' id='sev_position' style='width:440px;' >"; 
			foreach ( $sev_position_array  as  $k => $v ) {
				$content .= "<option value='".$k."'";
				if (get_option('sev_position') == $k){$content .= " selected";}
				$content .= " >".$v.'</option>';
			}
			$content .= "</select>";
			$content .= "<hr>";
			
//=================2

			$content .= "<label for=''>".__('Code #2:','sevlang')."</label><br />";
			$content .= "<textarea name='sev_code2' rows='7' id='sev_code2' class='ui-widget-content ui-corner-all' cols='100'>".get_option('sev_code2')."</textarea>";		
			$content .= "<br /><div class='clear'></div><label for=''>".__('Position:','sevlang')."</label><br />";

			$content .= "<select name='sev_position2' id='sev_position2' style='width:440px;' >"; 
			foreach ( $sev_position_array  as  $k => $v ) {
				$content .= "<option value='".$k."'";
				if (get_option('sev_position2') == $k){$content .= " selected";}
				$content .= " >".$v.'</option>';
			}
			$content .= "</select>";
			
//================3

			$content .= "<hr>";
			$content .= "<label for=''>".__('Code #3:','sevlang')."</label><br />";
			$content .= "<textarea name='sev_code3' rows='7' id='sev_code3' class='ui-widget-content ui-corner-all' cols='100'>".get_option('sev_code3')."</textarea>";		
			$content .= "<br /><div class='clear'></div><label for=''>".__('Position:','sevlang')."</label><br />";

			$content .= "<select name='sev_position3' id='sev_position3' style='width:440px;' >"; 
			foreach ( $sev_position_array  as  $k => $v ) {
				$content .= "<option value='".$k."'";
				if (get_option('sev_position3') == $k){$content .= " selected";}
				$content .= " >".$v.'</option>';
			}
			$content .= "</select>";

			$content .= "<hr>";			
			

			$content .= "<br /><div class='clear'></div>
						<input type='hidden' name='action' value='update' />
						<input type='hidden' name='page_options' value='sev_code, sev_position, sev_code2, sev_position2, sev_code3, sev_position3 ' />
						<p class='submit'><input type='submit' class='button-primary' value='".__('Save Changes','sevlang')."' /></p>
						</form>";
			echo $content;
		}
	
	
		public function searchenginevisitors(){
			$referer = $_SERVER['HTTP_REFERER'];
			$searchengine = array('/search?','images.google.','web.info.com','search.','del.icio.us/search','soso.com','/search/','.yahoo.','.google.','.facebook.com','twitter.com','bing.com','baidu.com');
			foreach ($searchengine as $v) {
					if (strpos($referer,$v)!==false){
						$_SESSION['visitor_sev'] = 1;
						return true;
					}
			}
			return false;
		}
	
		public function visitor_sev_filter($content){
			global $post;
			$output = $content;
			$post_meta = get_post_meta( $post->ID );
			$post_yes='0';

			if(isset($post_meta['sev-post-meta']) ){ $post_yes = $post_meta['sev-post-meta'][0];}else{$post_yes='1';}
				
			if (isset($_SESSION['visitor_sev'])  && $_SESSION['visitor_sev']==1 && $post_yes=='1'){ 
			
				$code = stripslashes(get_option('sev_code'));
				$sev_position = stripslashes(get_option('sev_position'));
				
				$code2 = stripslashes(get_option('sev_code2'));
				$sev_position2 = stripslashes(get_option('sev_position2'));
				
				$code3 = stripslashes(get_option('sev_code3'));
				$sev_position3 = stripslashes(get_option('sev_position3'));
				
				
				switch ($sev_position) {
					case 'top':
						$output = '<p>'.$code.'</p>'.$output;
					break;
					case 'top-middle':
						$output = '<p style="text-align: center;">'.$code.'</p>'.$output;
					break;
					case 'top-left':
						$output = '<p style="float:left; margin: 5px 5px 5px 0;">'.$code.'</p>'.$output;
					break;
					case 'top-right':
						$output = '<p style="float:right; margin: 5px 0 5px 5px;">'.$code.'</p>'.$output;
					break;
					case 'bottom':
						$output = $output.'<p style="text-align: center;">'.$code.'</p>';
					break;
					case 'bottom-middle':
						$output =  $output.'<p style="text-align: center;">'.$code.'</p>';
					break;
					case 'bottom-left':
						$output =  $output.'<p style="float:left; margin: 5px 5px 5px 0;">'.$code.'</p>';
					break;
					case 'bottom-right':
						$output =  $output.'<p style="float:right; margin: 5px 0 5px 5px;">'.$code.'</p>';
					break;
					default:
						$output = '<p>'.$code.'</p>'.$output;
					break;	
				}

				switch ($sev_position2) {
					case 'top':
						$output = '<p>'.$code2.'</p>'.$output;
					break;
					case 'top-middle':
						$output = '<p style="text-align: center;">'.$code2.'</p>'.$output;
					break;
					case 'top-left':
						$output = '<p style="float:left; margin: 5px 5px 5px 0;">'.$code2.'</p>'.$output;
					break;
					case 'top-right':
						$output = '<p style="float:right; margin: 5px 0 5px 5px;">'.$code2.'</p>'.$output;
					break;
					case 'bottom':
						$output = $output.'<p style="text-align: center;">'.$code2.'</p>';
					break;
					case 'bottom-middle':
						$output =  $output.'<p style="text-align: center;">'.$code2.'</p>';
					break;
					case 'bottom-left':
						$output =  $output.'<p style="float:left; margin: 5px 5px 5px 0;">'.$code2.'</p>';
					break;
					case 'bottom-right':
						$output =  $output.'<p style="float:right; margin: 5px 0 5px 5px;">'.$code2.'</p>';
					break;
					default:
						$output = '<p>'.$code2.'</p>'.$output;
					break;	
				}
				
				switch ($sev_position3) {
					case 'top':
						$output = '<p>'.$code3.'</p>'.$output;
					break;
					case 'top-middle':
						$output = '<p style="text-align: center;">'.$code3.'</p>'.$output;
					break;
					case 'top-left':
						$output = '<p style="float:left; margin: 5px 5px 5px 0;">'.$code3.'</p>'.$output;
					break;
					case 'top-right':
						$output = '<p style="float:right; margin: 5px 0 5px 5px;">'.$code3.'</p>'.$output;
					break;
					case 'bottom':
						$output = $output.'<p style="text-align: center;">'.$code3.'</p>';
					break;
					case 'bottom-middle':
						$output =  $output.'<p style="text-align: center;">'.$code3.'</p>';
					break;
					case 'bottom-left':
						$output =  $output.'<p style="float:left; margin: 5px 5px 5px 0;">'.$code3.'</p>';
					break;
					case 'bottom-right':
						$output =  $output.'<p style="float:right; margin: 5px 0 5px 5px;">'.$code3.'</p>';
					break;
					default:
						$output = '<p>'.$code3.'</p>'.$output;
					break;	
				}				
				
				
				
				
				
				
				
			}
			
			
			
			
			
		  return $output;
		}
		
		function searchenginevisitors_meta_box() {
				add_meta_box( 'searchenginevisitors_meta_b', __( 'Adsense SEV', 'amepro_prfx-textdomain' ), array($this,'searchenginevisitors_meta_b_callback'), '', 'side' );
		}

		function searchenginevisitors_meta_b_callback( $post ) {
			wp_nonce_field( basename( __FILE__ ), 'sev_nonce' );
			$post_meta = get_post_meta( $post->ID );
			
			if(isset($post_meta['sev-post-meta']) ){
				if($post_meta['sev-post-meta'][0]=='1'){$checked_p1 = "checked";  $checked_p2 = "";}
				if($post_meta['sev-post-meta'][0]=='0'){$checked_p1 = "";  $checked_p2 = "checked";}
			
			}
			else{ $checked_p1 = "checked";  $checked_p2 = ""; }
			
			$content = "<div class='row-content'>
							<p><label for='sev-post-meta'>Show on this Post</label></p>
							<input type='radio' name='sev-post-meta' id='sev-post-meta' value='1' ".$checked_p1."> Yes &nbsp;&nbsp;<input type='radio'  name='sev-post-meta' id='sev-post-meta' value='0' ".$checked_p2."> No
						</div>";
			echo $content;
		}

		function sev_meta_post_save( $post_id ) {
			$is_autosave = wp_is_post_autosave( $post_id );
			$is_revision = wp_is_post_revision( $post_id );
			$is_valid_nonce = ( isset( $_POST[ 'sev_nonce' ] ) && wp_verify_nonce( $_POST[ 'sev_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
			if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
				return;
			}
			if( isset( $_POST[ 'sev-post-meta' ] )   &&  $_POST[ 'sev-post-meta' ] == '1') {
				update_post_meta( $post_id, 'sev-post-meta', '1' );
			} 
			if( isset( $_POST[ 'sev-post-meta' ] )   &&  $_POST[ 'sev-post-meta' ] == '0') {
				update_post_meta( $post_id, 'sev-post-meta', '0' );
			}
		}			









		
	}
	
	new SearchEngineVisitors();	
}