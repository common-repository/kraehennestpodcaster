<?php

// WP SimplePie
require_once( ABSPATH . WPINC . '/class-simplepie.php' );

/**
 * Read Kraehennes XML File
 */
class KPXMLReader
{
	public $xmlUrl;
	public $cacheDir;
	public $cacheTime;
	public $cacheFile;
	public $reverseArr;
	public $removeString;
	public $removeNumersRegEx;
	public $restrictStringMatch;

	function __construct( $xmlUrl, $cacheDir, $cacheTime, $reverseArr = 'false', $removeString = '', $removeNumersRegEx = '', $restrictStringMatch = '', $cacheFile = 'KraehennestPodcasterCache.xml' )
	{
		global $blog_id;

		$this -> xmlUrl = $xmlUrl;
		$this -> cacheDir = $cacheDir;
		$this -> cacheTime = $cacheTime;
		$this -> cacheFile = $blog_id . $cacheFile;
		$this -> reverseArr = $reverseArr;
		$this -> removeString = $removeString;
		$this -> removeNumersRegEx = $removeNumersRegEx;
		$this -> restrictStringMatch = $restrictStringMatch;
	}

	public function getElements()
	{
		// Check Cache usage
		if( file_exists( $this -> cacheDir . DIRECTORY_SEPARATOR . $this -> cacheFile ) && !empty( $this -> cacheTime ) )
		{
			// Read from cache if file is not older than now-$cacheTime
			if( (filemtime( $this -> cacheDir . DIRECTORY_SEPARATOR . $this -> cacheFile ) > (time() - $this -> cacheTime)) && filesize( $this -> cacheDir . DIRECTORY_SEPARATOR . $this -> cacheFile ) > 0 )
				$this -> xmlUrl = $this -> cacheDir . DIRECTORY_SEPARATOR . $this -> cacheFile;
		}

		// Read XML
		$SimplePie_File = new SimplePie_File( $this -> xmlUrl, 8 );	// url, timeout
		if( $SimplePie_File -> success == 1 )
			$xmlData = $SimplePie_File -> body;
		else
			$xmlData = false;

		// Write to cachefile if not cache used
		if( !empty( $this -> cacheTime ) && strpos( $this -> xmlUrl, 'http:' ) !== false )
			file_put_contents( $this -> cacheDir . DIRECTORY_SEPARATOR . $this -> cacheFile, $xmlData );

		// Parse XML
		if( $xmlData )
		{
			try
			{
				$xml = new SimpleXMLElement( $xmlData );
			}
			catch( Exception $xmlErr )
			{
				print_r( $xmlErr -> getMessage() );
				return false;
			}

			if( isset( $xml -> channel -> item ) )
			{
				$returnArr = array();
				foreach( $xml -> channel -> children() AS $item )
				{
					if( strpos( (string)$item -> link, '.mp3' ) !== false )
					{
						$title = (string)$item -> title;

						// Only if restriction not set, or restriction to title matches
						if( empty( $this -> restrictStringMatch ) || preg_match( '#' . $this -> restrictStringMatch . '#iu', $title ) )
						{
							// Remove string if configured
							if( !empty( $this -> removeString ) )
								$title = preg_replace( '#(.*)(' . $this -> removeString . ')(.*)#iu', '$1$3', $title );

							// Remove numbers on beginning
							if( !empty( $this -> removeNumersRegEx ) )
								$title = preg_replace( '#' . $this -> removeNumersRegEx . '#iu', '', $title );

							$returnArr[ $title ] = (string)$item -> link;
						}
					}
				}
				if( $this -> reverseArr == 'true' )
					$returnArr = array_reverse( $returnArr, true );
				return $returnArr;
			}
			else	return false;
		}
		else return false;
	}
}

?>