<?php

function wp_all_import_get_image_from_gallery($image_name, $targetDir = false, $bundle_type = 'images') 
{
	global $wpdb;

	if ( ! $targetDir )
	{
		$wp_uploads = wp_upload_dir();	
		$targetDir = $wp_uploads['path'];
	}	

	// search attachment by file name with extension
	$attch = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->posts . " WHERE (post_title = %s OR post_title = %s OR post_name = %s) AND post_type = %s AND post_mime_type LIKE %s;", $image_name, preg_replace('/\\.[^.\\s]{3,4}$/', '', $image_name), sanitize_title($image_name), "attachment", "image%" ) );	

	if ( empty($attch) )
	{										
		
		// search attachment by file name without extension
		$attachment_title = explode(".", $image_name);
		if (is_array($attachment_title) and count($attachment_title) > 1) array_pop($attachment_title);
		$image_name = implode(".", $attachment_title);

		$attch = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->posts . " WHERE (post_title = %s OR post_title = %s OR post_name = %s) AND post_type = %s AND post_mime_type LIKE %s;", $image_name, preg_replace('/\\.[^.\\s]{3,4}$/', '', $image_name), sanitize_title($image_name), "attachment", "image%" ) );	
	}
	
	// search attachment by file headers
	if ( empty($attch) and @file_exists($targetDir . DIRECTORY_SEPARATOR . $image_name) )
	{
		if ($bundle_type == 'images' and ($img_meta = wp_read_image_metadata($targetDir . DIRECTORY_SEPARATOR . $image_name)))
		{
			if (trim($img_meta['title']) && ! is_numeric(sanitize_title($img_meta['title'])))
			{
				$img_title = $img_meta['title'];
				$attch = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->posts . " WHERE post_title = %s AND post_type = %s AND post_mime_type LIKE %s;", $img_title, "attachment", "image%" ) );					
			}
		}
	}

	return $attch;
} 