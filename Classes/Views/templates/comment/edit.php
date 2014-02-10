<?php
if( is_array($this->errors) )
{
	echo '<div class="flash warning">'.$this->errors[0].'</div>';
}
?>
<div class="commentForm">
	<form method="post" id="commentFormElement">
		<textarea placeholder="Comment" id="commentFormTextarea" name="content"><?=$this->comment->content?></textarea>
		<br />
		<input style="float:right;" type="submit" name="save" value="Save changes"/>
		<input style="float:right;" type="submit" name="cancel" value="Cancel"/>
	</form>
</div>