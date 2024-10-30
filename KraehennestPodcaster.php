<?php
/*
Plugin Name: KraehennestPodcaster
Plugin URI: http://www.thegeek.de/KraehennestPodcaster/
Version: 1.2.1
Author: Marc Schieferdecker (@motorradblogger)
Author URI: http://thegeek.de
Description: Der KraehennestPodcaster ermöglicht dir die super einfache Einbindung von Podcasts des Krähennests in dein WordPress Blog.
License: GPL
*/

/**
 * Set paths
 */
global $blog_id;
define( 'KP_PLUGIN_DIR', dirname(__FILE__) );
define( 'KP_CACHE_DIR', realpath( dirname(__FILE__).'/../../uploads/' ) );
define( 'KP_PLUGIN_OPTIONS_NAME', 'kpPlugin_AdminOptionsName' . $blog_id );

/**
 * Replacement for PHP >= PHP 5.2.1 function sys_get_temp_dir
 */
if( !function_exists( 'sys_get_temp_dir' ) )
{
	function sys_get_temp_dir()
	{
		if( !empty( $_ENV[ 'TMP' ] ) )
			return realpath($_ENV['TMP']);
		if( !empty( $_ENV[ 'TMPDIR' ] ) )
			return realpath( $_ENV[ 'TMPDIR' ] );
		if( !empty( $_ENV[ 'TEMP' ] ) )
			return realpath( $_ENV[ 'TEMP' ] );
		$tempfile = @tempnam( uniqid( rand(), TRUE ), '' );
		if( file_exists( $tempfile ) )
		{
			@unlink( $tempfile );
			return realpath( dirname( $tempfile ) );
		}
	}
}

/**
 * The main plugin class
 * requires PHP 5 !!!
 */
class KraehennestPodcaster
{
	public $kp_AdminOptionsName;
	public $kp_AdminOptions;

	// Construct
	function __construct()
	{
		$this -> kp_AdminOptionsName	= KP_PLUGIN_OPTIONS_NAME;
		$this -> kp_AdminOptions	= $this -> AdminOptions();
	}

	/**
	 * Get options for this plugin
	 */
	function AdminOptions()
	{
		// Build default HTML player
		$defaultHTMLPlayer  = '<div class="kraehennestplayer">' . "\n";
		$defaultHTMLPlayer .= "\t" . '<p class="kraehennestplayerTitle">%%title%%</p>' . "\n";
		$defaultHTMLPlayer .= "\t" . '<p class="kraehennestplayerSWF">' . "\n";
		$defaultHTMLPlayer .= "\t" . "\t" . '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="165" height="38" id="niftyPlayer1" align="">' . "\n";
		$defaultHTMLPlayer .= "\t" . "\t" . '<param name=movie value="' . plugin_dir_url(__FILE__) . '/niftyplayer.swf?file=%%link%%">' . "\n";
		$defaultHTMLPlayer .= "\t" . "\t" . '<param name=quality value=high>' . "\n";
		$defaultHTMLPlayer .= "\t" . "\t" . '<param name=bgcolor value=#FFFFFF>' . "\n";
		$defaultHTMLPlayer .= "\t" . "\t" . '<embed src="' . plugin_dir_url(__FILE__) . '/niftyplayer.swf?file=%%link%%" quality=high bgcolor=#FFFFFF width="165" height="38" name="niftyPlayer1" align="" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">' . "\n";
		$defaultHTMLPlayer .= "\t" . "\t" . '</embed>' . "\n";
		$defaultHTMLPlayer .= "\t" . "\t" . '</object>' . "\n";
		$defaultHTMLPlayer .= "\t" . '</p>' . "\n";
		$defaultHTMLPlayer .= '</div>';

		// Set default options
		$kp_AdminOptions = array(
					'KPPodcastXMLURL' => 'http://kraehennest.piraten-wagen-mehr-demokratie.de/PiratenNRW.xml',
					'KPUseCustomFields' => 'true',
					'KPShowContent' => 'after',
					'KPCustomFieldCSS' => '',
					'KPHtmlTemplate' => $defaultHTMLPlayer,
					'KPCacheXML' => '3600',
					'KPCacheDir' => KP_CACHE_DIR,
					'KPReverseXML' => 'false',
					'KPRemoveString' => 'Pressemitteilung der Piratenpartei Deutschland:|Pressemitteilung der Piratenfraktion NRW:|Pressemiteilung der Piratenpartei Deutschland:',
					'KPRemoveNumersRegEx' => '^[0-9]+ - ',
					'KPRestrictStringMatch' => ''
				);
		// Load existing options
		$_kp_AdminOptions = get_option( $this -> kp_AdminOptionsName );
		// Overwrite defaults
		$update = false;
		if( count( $_kp_AdminOptions ) )
		{
			foreach( $_kp_AdminOptions AS $oKey => $oVal )
			{
				if( $oKey == 'KPPodcastXMLURL' && empty( $oVal ) )
				{
					$oVal = 'http://kraehennest.piraten-wagen-mehr-demokratie.de/PiratenNRW.xml';
					$update = true;
				}
				if( $oKey == 'KPHtmlTemplate' && empty( $oVal ) )
				{
					$oVal = $defaultHTMLPlayer;
					$update = true;
				}
				$kp_AdminOptions[ $oKey ] = $oVal;
			}
		}
		// Set default options to wp db if no existing options or new options are found
		if( !count( $_kp_AdminOptions ) || count( $_kp_AdminOptions ) != count( $kp_AdminOptions ) || $update )
		{
			update_option( $this -> kp_AdminOptionsName, $kp_AdminOptions );
		}
		// Return options
		return $kp_AdminOptions;
	}

	// Adminpage
	function AdminPage()
	{
		// Make translations possible
		if( function_exists( 'load_plugin_textdomain' ) ) {
			load_plugin_textdomain( 'KraehennestPodcaster', 'wp-content/plugins/kraehennestpodcaster' );
		}
		$xmlread_test_result = '';

		/**
		 * Parse admin actions
		 */
		// Save options
		if( $_POST[ 'kp_admin_action' ] == 'save' )
		{
			$kpOptionsNew = array();
			foreach( array_keys( $this -> kp_AdminOptions ) AS $oKey )
				$kpOptionsNew[ $oKey ] = $_POST[ $oKey ];
			update_option( $this -> kp_AdminOptionsName, $kpOptionsNew );
			$this -> kp_AdminOptions = $kpOptionsNew;
			echo "<div id=\"message\" class=\"updated fade\"><p><strong>" . __( "Optionen gespeichert!", "KraehennestPodcaster" ) . "</strong></p></div>";
		}
		// Test XML network read and display results
		if( $_GET[ 'kp_admin_action' ] == 'test' )
		{
			$xmlUrl = $this -> kp_AdminOptions[ 'KPPodcastXMLURL' ];
			$xmlread_test_result .= "<h4>" . __( "Ergebnisse des Tests", "KraehennestPodcaster" ) . "</h4>";
			$xmlread_test_result .= "<p>" . __( "Teste mit URL", "KraehennestPodcaster" ) . ": <a href=\"$xmlUrl\">$xmlUrl</a></p>";

			// SimpleXML Parser
			$xmlReader = new KPXMLReader( $this -> kp_AdminOptions[ 'KPPodcastXMLURL' ], KP_CACHE_DIR, $this -> kp_AdminOptions[ 'KPCacheXML' ], $this -> kp_AdminOptions[ 'KPReverseXML' ], $this -> kp_AdminOptions[ 'KPRemoveString' ], $this -> kp_AdminOptions[ 'KPRemoveNumersRegEx' ], $this -> kp_AdminOptions[ 'KPRestrictStringMatch' ] );
			$items = $xmlReader -> getElements();
			if( $items )
			{
				$xmlread_test_result .= "<p>" . __( "Gültiges XML gefunden! Hier die letzten 10 Einträge", "KraehennestPodcaster" ) . ":</p>";
				$xmlread_test_result .= "<table class=\"form-table\">";
				$i = 0;
				foreach( $items AS $title => $link )
				{
					// Embed Player
					$player  = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="165" height="38" id="niftyPlayer1" align="">';
					$player .= '<param name=movie value="' . plugin_dir_url(__FILE__) . '/niftyplayer.swf?file=' . $link . '">';
					$player .= '<param name=quality value=high>';
					$player .= '<param name=bgcolor value=#FFFFFF>';
					$player .= '<embed src="' . plugin_dir_url(__FILE__) . '/niftyplayer.swf?file=' . $link . '" quality=high bgcolor=#FFFFFF width="165" height="38" name="niftyPlayer1" align="" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">';
					$player .= '</embed>';
					$player .= '</object>';

					$xmlread_test_result .= "<tr><th>" . $title . "</th><td>" . $link . "<br/>" . $player . "</td></tr>";
					$i++;
					if( $i >= 10 )
						break;
				}
				$xmlread_test_result .= "</table>";
			}
			else	$xmlread_test_result .= "<p>" . __( "Kein gültiges XML gefunden", "KraehennestPodcaster" ) . ".</p>";
		}

		// Page head & container
		$page .= "<div class=\"wrap\">\n";
		$page .= "<h2>" . __( "Krähennest Podcaster Settings", "KraehennestPodcaster" ) . "</h2>\n";
		$page .= "<div id=\"poststuff\">\n";

		/**
		 * Option form
		 */
		$page .= "<div class=\"postbox\">\n";
		$page .= "<h3>" . __( "Allgemeine Optionen", "KraehennestPodcaster" ) . "</h3>";
		// Form
		$page .= "<form name=\"kpAdminPage\" method=\"POST\" action=\"" . $_SERVER[ "REQUEST_URI" ] . "\" enctype=\"multipart/form-data\">\n";
		$page .= "<input type=\"hidden\" name=\"kp_admin_action\" value=\"save\"/>\n";
		$page .= "<table class=\"form-table\">\n";

			// XML URL
			$page .= "<tr>";
			$page .= "<th style=\"width:250px\">" . __( "HTTP Pfad zur XML Datei des Krähennests", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><input type=\"text\" name=\"KPPodcastXMLURL\" style=\"width:70%\" maxlength=\"600\" value=\"" . $this -> kp_AdminOptions[ 'KPPodcastXMLURL' ] . "\"/></td>";
			$page .= "</tr>";
			$page .= "<tr><td>&nbsp;</td><td><strong>Derzeit verfügbare URL's</strong><ul style=\"margin-left:10px\"><li><i>Pressemitteilungen Piratenpartei NRW</i><br/>http://kraehennest.piraten-wagen-mehr-demokratie.de/PiratenNRW.xml</li><li><i>Pressemitteilungen Piratenpartei Deutschland</i><br/>http://kraehennest.piraten-wagen-mehr-demokratie.de/PiratenparteiPM.xml</li></ul></td></tr>";

			// Use custom fields
			$page .= "<tr>";
			$page .= "<th>" . __( "Ausgabe über the_meta() oder über internes HTML Template?", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><input type=\"checkbox\" name=\"KPUseCustomFields\" value=\"true\"" . ($this -> kp_AdminOptions[ 'KPUseCustomFields' ] == 'true' ? ' checked="checked" ' : '') . "/> " . __( "Ja = intern, Nein = the_meta()", "KraehennestPodcaster" ) . "</td>";
			$page .= "</tr>";

			// Show Player before or after Content
			$page .= "<tr>";
			$page .= "<th>" . __( "Wo soll der Podcast ausgegeben werden?", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><select name=\"KPShowContent\">";
			$page .= "<option value=\"after\"" . ($this -> kp_AdminOptions[ 'KPShowContent' ] == 'after' ? 'selected="selected"' : '') . ">" . __( "Nach dem Artikel", "KraehennestPodcaster" ) . "</option>";
			$page .= "<option value=\"before\"" . ($this -> kp_AdminOptions[ 'KPShowContent' ] == 'before' ? 'selected="selected"' : '') . ">" . __( "Vor dem Artikel", "KraehennestPodcaster" ) . "</option>";
			$page .= "</select></td>";
			$page .= "</tr>";

			// Custom field css
			$page .= "<tr>";
			$page .= "<th width=\"50%\">" . __( "Custom Field CSS Anweisungen", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><textarea name=\"KPCustomFieldCSS\" style=\"width:70%;height:100px\">" . $this -> kp_AdminOptions[ 'KPCustomFieldCSS' ] . "</textarea></td>";
			$page .= "</tr>";

			// HTML Template for non custom field display
			$page .= "<tr>";
			$page .= "<th width=\"50%\">" . __( "HTML Template, wenn keine custom fields verwendet werden", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><textarea name=\"KPHtmlTemplate\" style=\"width:70%;height:200px\">" . stripslashes( $this -> kp_AdminOptions[ 'KPHtmlTemplate' ] ) . "</textarea><br/>" . __( "Platzhalter", "KraehennestPodcaster" ) .": %%title%%, %%link%% (.mp3)</td>";
			$page .= "</tr>";

			// Cache time in seconds
			$page .= "<tr>";
			$page .= "<th style=\"width:250px\">" . __( "XML Datei im Cache behalten (in Sekunden)", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><input type=\"text\" name=\"KPCacheXML\" style=\"width:10%\" maxlength=\"20\" value=\"" . $this -> kp_AdminOptions[ 'KPCacheXML' ] . "\"/></td>";
			$page .= "</tr>";

			// Cache Dir
			$page .= "<tr>";
			$page .= "<th style=\"width:250px\">" . __( "Dateipfad, wo die Cache-Datei abgelegt werden soll (muss beschreibbar sein!)", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><input type=\"text\" name=\"KPCacheDir\" style=\"width:70%\" maxlength=\"600\" value=\"" . $this -> kp_AdminOptions[ 'KPCacheDir' ] . "\"/><br/>" . __( "Bei Problemen versuchs mal mit diesem Pfad", "KraehennestPodcaster" ) . ": " . sys_get_temp_dir() . "</td>";
			$page .= "</tr>";

			// Reverse XML Array
			$page .= "<tr>";
			$page .= "<th>" . __( "Reihenfolge der Kraehennest Beiträge umdrehen?", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><input type=\"checkbox\" name=\"KPReverseXML\" value=\"true\"" . ($this -> kp_AdminOptions[ 'KPReverseXML' ] == 'true' ? ' checked="checked" ' : '') . "/> " . __( "Ja = Reihenfolge umdrehen, Nein = Reihenfolge wie angeliefert", "KraehennestPodcaster" ) . "</td>";
			$page .= "</tr>";

			// Remove special String from XML Title
			$page .= "<tr>";
			$page .= "<th width=\"50%\">" . __( "* Zeichenkette, die aus dem Titel des Kraehennest Beitrags entfernt wird (Fester String oder RegEx)", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><textarea name=\"KPRemoveString\" style=\"width:70%;height:100px\">" . $this -> kp_AdminOptions[ 'KPRemoveString' ] . "</textarea></td>";
			$page .= "</tr>";

			// Cache Dir
			$page .= "<tr>";
			$page .= "<th style=\"width:250px\">" . __( "RegEx, um die Zahlen am Anfang im Titel des Kraehenest Beitrags zu entfernen", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><input type=\"text\" name=\"KPRemoveNumersRegEx\" style=\"width:70%\" maxlength=\"600\" value=\"" . $this -> kp_AdminOptions[ 'KPRemoveNumersRegEx' ] . "\"/></td>";
			$page .= "</tr>";

			// Only accept entrys that matches a special regular expression
			$page .= "<tr>";
			$page .= "<th width=\"50%\">" . __( "Nur Podcast-Beiträge beachten, die folgender RegEx entsprechen", "KraehennestPodcaster" ) . "</th>";
			$page .= "<td><textarea name=\"KPRestrictStringMatch\" style=\"width:70%;height:100px\">" . $this -> kp_AdminOptions[ 'KPRestrictStringMatch' ] . "</textarea></td>";
			$page .= "</tr>";

			// Note
			$page .= "<tr><td colspan=\"2\">* -&gt; " . __( "Die Zeichen oder Zahlen, die aus dem Titel entfernt werden, werden VOR dem Vergleich mit deinen eigenen Beiträgen entfernt. Dies dient dazu, dass du den Titel des Kraehennests nicht exakt übernehmen musst, der Automatismus aber trotzdem noch funktioniert.", "KraehennestPodcaster" ) . "</td></tr>";

			// Save options
			$page .= "<tr class=\"submit\"><td>&nbsp;</td><td><input type=\"submit\" name=\"update_AdminOptions\" value=\"" . __( "Optionen speichern", "KraehennestPodcaster" ) . "\"/></td></tr>";

		$page .= "</table>";
		$page .= "</form>";
		// Close option form
		$page .= "</div>";

		/**
		 * XML test read
		 */
		$page .= "<div class=\"postbox\">\n";
		$page .= "<h3>" . __( "Funktionstest", "KraehennestPodcaster" ) . "</h3>";
		$page .= "<table class=\"form-table\"><tr><td>";
		$page .= "<p>" . __( "Test der grundlegenden Voraussetzungen für dieses Plugin", "KraehennestPodcaster" ) . ":</p>";
		$page .= "<p><ul>";
		$page .= "<li>" . __( "allow_url_fopen in der php.ini aktiviert", "KraehennestPodcaster" ) . ": " . (ini_get( 'allow_url_fopen' ) ? '<span style="color:#009900">' . __( "Aktiviert", "KraehennestPodcaster" ) . '!</span>' : '<span style="color:#990000">' . __( "Deaktiviert", "KraehennestPodcaster" ) . '!</span>') . "</li>";
		$page .= "<li>" . __( "Verzeichnis " . $this -> kp_AdminOptions[ 'KPCacheDir' ] . " durch den Webserver beschreibbar", "KraehennestPodcaster" ) . ": " . (is_writable( KP_CACHE_DIR ) ? '<span style="color:#009900">' . __( "Beschreibbar", "KraehennestPodcaster" ) . '!</span>' : '<span style="color:#990000">' . __( "Nur lesbar", "KraehennestPodcaster" ) . '!</span>') . "</li>";
		$page .= "</ul></p>";
		$page .= "<p>" . __( "Hier kannst du testen, ob die in den Optionen angegebene XML Datei des Krähennests mit deiner Server Konfiguration auch korrekt gelesen werden kann.", "KraehennestPodcaster" ) . "</p>";
		$page .= "<p><a href=\"" . $_SERVER[ "REQUEST_URI" ] . "&amp;kp_admin_action=test\">" . __( "Den Test starten", "KraehennestPodcaster" ) . "</a></p>";
		$page .= $xmlread_test_result;
		$page .= "</td></tr></table>";
		// Close XML test read
		$page .= "</div>";

		// Close container
		$page .= "</div>";

		echo $page;
	}

}

// Allways create class instance (setting default options on first activation, ready to play out of the box)
$KraehennestPodcasterPlugin = new KraehennestPodcaster();

/**
 * Admin hooks, only use, if user is using backend and user is admin
 */
if( strpos( $_SERVER[ 'REQUEST_URI' ], 'wp-admin' ) )
{
	// Admin wrapper
	function wrapper_kp_AdminPage()
	{
		global $KraehennestPodcasterPlugin;
		add_options_page( 'KraehennestPodcaster Options', 'Krähennest Podcaster', 9, KP_PLUGIN_DIR, array( &$KraehennestPodcasterPlugin, 'AdminPage' ) );
	}
	// Add action: Admin page
	add_action( 'admin_menu', 'wrapper_kp_AdminPage' );
}

/**
 * Includes
 */
include_once( KP_PLUGIN_DIR . '/KP.XMLReader.php' );
include_once( KP_PLUGIN_DIR . '/KP.Widget.php' );
include_once( KP_PLUGIN_DIR . '/KP.PostActions.php' );

?>