<?hh // decl
class NKRequest
{
	public string 	$controllerName;
	public string 	$actionName;
	public int		$ID;
	
	public function valueForKey(string $key): mixed;
	public function setValueForKey(mixed $value, string $key): void;
	
	public static function stringForURLTitle(string $value): string;
}