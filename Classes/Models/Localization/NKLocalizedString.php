<?php
class NKLocalizedString
{
	private $language;
	private $data;

	public function __construct($languageName)
	{
		$path = 'Website/Localization/'.$languageName.'.strings';
		if( !file_exists($path) )
		{
			throw new Exception('Cannot find translation file for '.$languageName.' at '.$path);
		}
		
		$this->language 	= $languageName;
		$this->data 		= parse_ini_file($path, true);
	}
	
	/**
	 * Localizes a given string by directly replacing it with
	 * the value found in the localizable file for the previous
	 * set language mode
	 */
	public function localize($string, $category = 'general', $translationMode = false)
	{
		if( isset($this->data[$category][$string]) )
		{
			return $this->data[$category][$string];
		}
		if( $translationMode )
		{
			throw new Exception('No translation for '.$string.' in category '.$category);
		}
		return $string;
	}
}