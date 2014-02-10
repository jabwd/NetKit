<div class="contentBox">
<?php
if( $this->errors )
{
	echo '<div class="flash warning">';
	foreach($this->errors as $error)
	{
		echo $error.'<br />';
	}
	echo '</div>';
}
?>
</div>