<div class="contentBox">
	<form method="post">
		<input type="text" placeholder="Title" name="title" value="<?=$this->page->title?>" maxlength="50"/>
		<br />
		<br />
		<textarea id="elm1" name="content" class="mceEditor"><?=$this->page->content?></textarea>
		<br />
		<br />
		<input type="submit" name="cancel" value="Cancel"/>
		<input type="submit" name="save" value="Save changes"/>
	</form>
	
	<?php
	if( $this->success )
	{
		echo '<div class="flash success">Saved</div>';
	}
	?>
</div>