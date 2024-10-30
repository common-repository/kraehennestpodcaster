<?php

/**
 * If a post is saved the plugin searches in the xml file for an element with the same title.
 * If a matching element is found, the data is added zu post meta fields.
 */
function kp_save_post_filter()
{
	$kp_AdminOptions = get_option( KP_PLUGIN_OPTIONS_NAME );

	if( isset( $_REQUEST[ 'post' ] ) && !empty( $_REQUEST[ 'post' ] ) && $_REQUEST[ 'action' ] == 'edit' )
	{
		// Load post
		$post = get_post( intval( $_REQUEST[ 'post' ] ) );

		// Get Post title
		$postTitle = $post -> post_title;
		// Get XML data
		$xmlReader = new KPXMLReader( $kp_AdminOptions[ 'KPPodcastXMLURL' ], KP_CACHE_DIR, $kp_AdminOptions[ 'KPCacheXML' ], $kp_AdminOptions[ 'KPReverseXML' ], $kp_AdminOptions[ 'KPRemoveString' ], $kp_AdminOptions[ 'KPRemoveNumersRegEx' ], $kp_AdminOptions[ 'KPRestrictStringMatch' ] );
		$items = $xmlReader -> getElements();
		if( $items )
		{
			foreach( $items AS $title => $link )
			{
				$title = trim( $title );
				$postTitle = trim( $postTitle );
				if( $title == $postTitle )
				{
					update_post_meta($post->ID, 'KP_MP3_TITLE', $title );
					update_post_meta($post->ID, 'KP_MP3_URL', $link );
				}
			}
		}
	}
}
if( strpos( $_SERVER[ 'REQUEST_URI' ], 'wp-admin' ) )
	add_action( 'admin_menu', 'kp_save_post_filter' );

/**
 * Shows Krähennest Content after a post (via HTML template or custom post meta fields)
 */
function kp_read_post( $content )
{
	$kp_AdminOptions = get_option( KP_PLUGIN_OPTIONS_NAME );
	$post = $GLOBALS[ 'post' ];

	// Get Meta Options
	$kpTitle = get_post_meta( $post->ID, 'KP_MP3_TITLE', true );
	$kpMP3Url = get_post_meta( $post->ID, 'KP_MP3_URL', true );

	// Check again if empty for this post
	if( empty( $kpTitle ) && empty( $kpMP3Url ) && is_single( $post ) )
	{
		// Get Post title
		$postTitle = $post -> post_title;
		// Get XML data
		$xmlReader = new KPXMLReader( $kp_AdminOptions[ 'KPPodcastXMLURL' ], KP_CACHE_DIR, $kp_AdminOptions[ 'KPCacheXML' ], $kp_AdminOptions[ 'KPReverseXML' ], $kp_AdminOptions[ 'KPRemoveString' ], $kp_AdminOptions[ 'KPRemoveNumersRegEx' ], $kp_AdminOptions[ 'KPRestrictStringMatch' ] );
		$items = $xmlReader -> getElements();
		if( $items )
		{
			foreach( $items AS $title => $link )
			{
				$title = trim( $title );
				$postTitle = trim( $postTitle );
				if( $title == $postTitle )
				{
					update_post_meta($post->ID, 'KP_MP3_TITLE', $title );
					update_post_meta($post->ID, 'KP_MP3_URL', $link );
				}
			}
		}
		$kpTitle = get_post_meta( $post->ID, 'KP_MP3_TITLE', true );
		$kpMP3Url = get_post_meta( $post->ID, 'KP_MP3_URL', true );
	}
	if( !empty( $kpTitle ) && !empty( $kpMP3Url ) )
	{
		if( $kp_AdminOptions[ 'KPUseCustomFields' ] == 'true' )
		{
			// Insert Player after the content
			if( $kp_AdminOptions[ 'KPShowContent' ] == 'after' )
			{
				$content .= stripslashes( str_replace( array( '%%title%%', '%%link%%' ), array( $kpTitle, $kpMP3Url ), $kp_AdminOptions[ 'KPHtmlTemplate' ] ) );
			}
			else
			// Insert Player before the content
			if( $kp_AdminOptions[ 'KPShowContent' ] == 'before' )
			{
				$content = stripslashes( str_replace( array( '%%title%%', '%%link%%' ), array( $kpTitle, $kpMP3Url ), $kp_AdminOptions[ 'KPHtmlTemplate' ] ) ) . $content;
			}
			// Default: Insert Player after the content
			else
			{
				$content .= stripslashes( str_replace( array( '%%title%%', '%%link%%' ), array( $kpTitle, $kpMP3Url ), $kp_AdminOptions[ 'KPHtmlTemplate' ] ) );
			}
		}
		else
		{
			$content .= "\n<style type=\"text/css\">\n" . $kp_AdminOptions[ 'KPCustomFieldCSS' ] . "\n</style>\n";
		}
	}
	return $content;
}
add_filter( 'the_content', 'kp_read_post' );

?>