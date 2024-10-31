<?php
	/*
	Plugin Name: OAS ToolBox
	Plugin URI: http://wordpress.org/extend/plugins/
	Description: This plugin contains functions that helpful to develop plugins for wordpress.
	Version: 1.0
	Author: Online Associates, UAE
	Author URI: http://www.onlineassociates.ae
	*/
	
	//Wordpress [@S]
	add_filter('the_content', 'oas_wpx_parse_content');
	add_action('template_redirect', 'oas_report_404');
	
	//Retrieve the page title using the PageID
	function oas_get_page_title($PageID='')
	{
		if( oas_get_post_exists($PageID) ) return get_post_field( 'post_title', $PageID );
	}


	//Create a anchor tag using the PageID
	function oas_get_page_anchor_tag( $PageID='', $Args = array() ) 
	{
		extract($Args);
		
		if( $GLOBALS['post']->ID == $PageID )
		{
			if( !empty($attr) )
			{
				if( preg_match("/class=['\"](.*?)['\"]/", $attr, $matches) ) $attr = ' class="'.$matches[1].' current-page" ';
				else $attr = $attr . ' class="current-page"';
			}
			else $attr = ' class="current-page"';
		}	
		if( oas_get_post_exists($PageID) ) return "<a href=\"" . get_permalink($PageID) . "\" $attr>$before" . oas_get_page_title($PageID) . "$after</a>";
	}

	
	//Check if a post exist or not on the database
	function oas_get_post_exists($PostID = '', $Error='Requested post not found')
	{
		$post = get_post($PostID, ARRAY_A);
		
		if( !empty($post) )
		{
			switch( $post['post_status'] )
			{
				case 'publish':
					return true;
				break;
				
				case 'draft':
				case 'inherit':
				case 'pending':
					if ( function_exists('wp_error_free_log') ) wp_error_free_log( array('post_id' => $PostID, 'url' => $post['guid'], 'desc' => $post['post_status']) );
				break;
				
				default:
					if ( function_exists('wp_error_free_log') ) wp_error_free_log( array('post_id' => $PostID, 'url' => $post['guid'], 'desc' => 'Unknown Post') );
			}
		}
		else
		{
			if ( function_exists('wp_error_free_log') ) wp_error_free_log( array('post_id' => $PostID, 'url' => $PostID, 'desc' => $Error) );
		}	
	}		


	//If is 404 (only useful if you use it with the wp_error_free_log)
	function oas_report_404( $testmode = false )
	{
		if ( is_404() && function_exists('wp_error_free_log') ) wp_error_free_log( array('desc' => '404 Not Found', 'referer' => true) );
		return true;
	}



	//Function can be accessed from anywhere within wordpress to print an array [@F]
	function oas_print_array($Array, $KeepAlive = false)
	{
		echo "<pre>";
		print_r($Array);
		echo "</pre>";
		if(!$KeepAlive) die;
	}
	
	//Get the parent list in an array
	function oas_get_parents( $PostID = '', $Construct = array() )
	{
		$ParentID = get_post_field( 'post_parent', $PostID );
		$ParentID = (int) $ParentID; //Safe
		
		if( !empty($ParentID) && $ParentID > 0 )
		{
			$Construct[] = $ParentID;
			return oas_get_parents( $ParentID, $Construct );
		}
		else if( !empty($Construct) ) return $Construct;
	}
	
	//Find the top most parent
	function oas_get_section_id( $PostID = '' )
	{
		$GrandParent = oas_get_parents($PostID);
		if( !empty($GrandParent) ) return end($GrandParent);
		else return $PostID;
	}


/*************************************************************************************
/							WPX SHORT CODE FUNCTIONS
/************************************************************************************/	
	
	//Regex and callback function to filter the wpx contents [@S]
	function oas_wpx_parse_content($content)
	{
		//Regex Pattern From : SilverLight Video Player Wordpress Plugin [OAS page-anchor=1 /]
		return preg_replace_callback("/\[OAS ([^]]*)\/\]/i", 'oas_wpx_process_engine', $content);
	}
	
	//Call the relevant functions [@S]
	function oas_wpx_process_engine($matches)
	{
		if( is_array($matches) && !empty($matches) )
		{
			$Options = explode( ',', $matches[1] );
			list($FunctionName, $Args) = explode('=', $Options[0]);
						
			$FunctionName = strtolower(  trim($FunctionName) );
			
			switch($FunctionName)
			{
				case 'get_page_title': //[OAS get_page_title=1 /]
					if( !empty($Args) ) return oas_get_page_title($Args);
				break;
				
				case 'get_page_anchor_tag': //[OAS get_page_anchor_tag=1 /]
					if( !empty($Args) ) return oas_get_page_anchor_tag($Args);
				break;
				
				case 'get_page_href': //[OAS get_page_href=1 /]
				 if( !empty($Args) ) return get_permalink($Args);
				break;
				
				case 'get_date': //[OAS get_date=Y-m-d /]
					if( !empty($Args) ) return oas_date_format( date("Y-m-d H:i:s T"), $Args );
				break;
				
				default:
			}
		}
	}


/*************************************************************************************
/							META CACHE FUNCTIONS
/************************************************************************************/


	function oas_cache_meta_data($refresh=false)
	{
		global $wpdb;
		//$meta = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta;", ARRAY_A );
		$meta = $wpdb->get_results( "SELECT $wpdb->posts.post_status, $wpdb->postmeta.* FROM $wpdb->posts 
									LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) 
									WHERE (post_type = 'page' OR post_type = 'post' AND post_status = 'publish')", ARRAY_A );
		$MetaCache = array();
		if( is_array($meta) && !empty($meta) )
		{
			foreach($meta as $cache) $MetaCache[ $cache['post_id'] ][ $cache['meta_key'] ][] = $cache['meta_value'];

			if( !empty($MetaCache) )
			{
				foreach ( array_keys($MetaCache) as $post ) wp_cache_set($post, $MetaCache[$post], 'oas_post_meta');
				unset( $meta  ); //unset and clear the original query
			}
		}
	}
	
	function oas_get_meta($PostID, $Key)
	{
		$PostID = (int) $PostID;
		$CachedMeta = wp_cache_get( $PostID, 'post_meta' );
		
		//If there's no wp cache meta, check to see if we have loaded our cache
		if(!$CachedMeta) $CachedMeta = wp_cache_get( $PostID, 'oas_post_meta' );
		
		//If there's still no cache (i.e. we haven't loaded our own cache) then load our own cache and get it from that
		if(!$CachedMeta)
		{
			oas_cache_meta_data();
			$CachedMeta = wp_cache_get($PostID, 'oas_post_meta');   
		} 
		
		if( !empty( $CachedMeta[$Key] ) )
		{
			$CachedMeta = array_map( 'maybe_unserialize', $CachedMeta[$Key] );
			return $CachedMeta[0];
		}
	}
	

/*************************************************************************************
/							MISC FUNCTIONS
/************************************************************************************/

	//datetime to unix time converter
	function oas_date_unix( $datetime )
	{
		$unixtime = strtotime( $datetime );
		if( $unixtime != -1 || $unixtime !== false ) return $unixtime;
	}
	
	//Convert the datetime in to desired user format
	function oas_date_format( $Date, $Pattern )
	{
		return date( $Pattern, oas_date_unix( $Date ) );
	}
	
	//Returns the blog role of the current user, visitor mean the regular blog users.
	function oas_get_user_type()
	{
		get_currentuserinfo();
		$UserLevel = $GLOBALS['current_user']->user_level;
		$UserRoles = array( 'subscriber', 'contributor', 'author', 'author', 'author', 'editor', 'editor', 'editor', 'admin', 'admin', 'admin' );
		
		if( array_key_exists($UserLevel, $UserRoles) ) return $UserRoles[$UserLevel];
		else return "visitor";
	}


/*************************************************************************************
/							Output Buffer
/************************************************************************************/

/*
Example :

oas_output_buffer("callback");
function callback( $buffer )
{
  return ( str_replace("<h2>Home</h2>", "<h2>My Modified Text</h2>", $buffer) );
}


*/

	$OAS_OUTPUT_HTML_BUFFER = false;

	function oas_output_buffer($callback, $autoclose=true,$action='wp_head', $close='wp_footer')
	{
		$GLOBALS['OAS_OUTPUT_HTML_BUFFER'] =& new OasOutputBuffer($callback, $autoclose, $action, $close);
	}
	
	function oas_end_buffer()
	{
		if($GLOBALS['OAS_OUTPUT_HTML_BUFFER']) $GLOBALS['OAS_OUTPUT_HTML_BUFFER']->complete();
	}

	class OasOutputBuffer
	{	
	
		var $Content = false;
		var $Started = true;
		var $AutoClose;
		var $callback;
		
		function OasOutputBuffer($callback, $autoclose=true, $action='wp_head', $close='wp_footer')
		{
			if( function_exists('add_action') )
			{
				$this->callback = $callback;
				$this->AutoClose = $autoclose;
				add_action($action, array(&$this, "oas_start"));
				add_action($close, array(&$this, "oas_flush"));
			}
		}
	
		function oas_start()
		{
			if($this->callback)
			{
				ob_start($this->callback);
				$this->Started = true;
			}
		}
		
		function oas_flush()
		{
			if($this->Started && $this->AutoClose)
			{
				ob_end_flush();
				$this->Started = false;
			}	
		}
		
		function complete()
		{
			$this->AutoClose = true;
			$this->oas_flush();
		}
		
	}
?>