<br />
<div class="flash warning">
	<?=$this->message?>
</div>
<br />
<?php
// print the stacktrace if needed
if( Config::debugMode )
{
	?>
	<br />
	<div class="flash info" style="text-align:left;">
	<h3>Error stack trace</h3>
	<table>
		<tr><th>File</th><th>Function</th></tr>
	<?php
	foreach($this->exception->getTrace() as $symbol)
	{
		$path = $symbol['file'];
		$components = explode("/",$path);
		$lastPath = $components[count($components)-1];
		echo '<tr>';
		echo '<td>'.$lastPath.':'.$symbol['line'].'&nbsp; &nbsp; </td><td>'.$symbol['function'].'</td>';
		echo '</tr>';
	}
	?>
	</table>
	</div>
	<?php
}
?>