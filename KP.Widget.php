<?php

/**
 * Widget to display the latest Kraehennest Podcast in a sidebar
 */
class KPWidget extends WP_Widget
{
	public $kp_AdminOptionsName;
	public $kp_AdminOptions;

	public function __construct()
	{
		parent::__construct(
	 		'KPWidget', // Base ID
			'KPWidget', // Name
			array( 'description' => __( 'Krähennest Postcaster Widget. Zeigt die letzte Folge im Widget an.', 'KraehennestPodcaster' ) )
		);
		$this -> kp_AdminOptionsName	= KP_PLUGIN_OPTIONS_NAME;
		$this -> kp_AdminOptions	= get_option( $this -> kp_AdminOptionsName );
	}

 	public function form( $instance )
 	{
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Neuer Titel', 'KraehennestPodcaster' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance )
	{
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	public function widget( $args, $instance )
	{
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Before widget
		echo $before_widget;

		// Widget title
		if( !empty( $title ) )
		{
			echo $before_title;
			echo $title;
			echo $after_title;
		}

		// Nesting div container
		echo '<div class="kraehennestplayer">';

		// Get entrys
		$xmlReader = new KPXMLReader( $this -> kp_AdminOptions[ 'KPPodcastXMLURL' ], KP_CACHE_DIR, $this -> kp_AdminOptions[ 'KPCacheXML' ], $this -> kp_AdminOptions[ 'KPReverseXML' ], $this -> kp_AdminOptions[ 'KPRemoveString' ], $this -> kp_AdminOptions[ 'KPRemoveNumersRegEx' ], $this -> kp_AdminOptions[ 'KPRestrictStringMatch' ] );
		$items = $xmlReader -> getElements();
		if( $items )
		{
			foreach( $items AS $title => $link )
			{
				// Embed Player
				$player  = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="165" height="38" id="niftyPlayer1">';
				$player .= '<param name=movie value="' . plugin_dir_url(__FILE__) . '/niftyplayer.swf?file=' . $link . '">';
				$player .= '<param name=quality value=high>';
				$player .= '<param name=bgcolor value=#FFFFFF>';
				$player .= '<embed src="' . plugin_dir_url(__FILE__) . '/niftyplayer.swf?file=' . $link . '" quality=high bgcolor=#FFFFFF width="165" height="38" name="niftyPlayer1" align="" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">';
				$player .= '</embed>';
				$player .= '</object>';

				echo '<div class="kraehennestplayerTitle">' . trim( $title ) . '</div>';
				echo '<div class="kraehennestplayerSWF">' . $player . '</div>';
				break;
			}
		}

		// Close nesting div
		echo '</div>';

		// After widget
		echo $after_widget;
	}
}
// Register KPWidget
add_action( 'widgets_init', create_function( '', 'register_widget( "KPWidget" );' ) );

?>