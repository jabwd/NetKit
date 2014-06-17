<?hh
class NKLocalizedString
{
	private string 			$language;
	private NKStringsFile 	$file;

	public function __construct(string $languageName): void
	{
		$path = 'Website/Localization/'.$languageName.'.strings';
		
		$this->file 	= new NKStringsFile($path);
		$this->language = $languageName;
	}
	
	/**
	 * Localizes a given string by directly replacing it with
	 * the value found in the localizable file for the previous
	 * set language mode
	 */
	public function localize(string $string, string $category = 'general', bool $translationMode = false): string
	{
		if( isset($this->file->strings[$category][$string]) )
		{
			return $this->file->strings[$category][$string];
		}
		if( $translationMode )
		{
			throw new Exception('No translation for '.$string.' in category '.$category);
		}
		return $string;
	}
}