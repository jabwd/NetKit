<div class="contentBox">
	<form method="post">
		<table class="form">
			<tr>
				<td>Title</td><td><input type="text" name="title" value="<?php echo $this->page->title; ?>" maxlength="50"/></td>
			</tr>
		</table>
		<textarea id="elm1" name="content" class="mceEditor">
		<?php
		echo $this->page->content;
		?>
		</textarea>
		<br />
		<table class="form">
			<tr>
				<td></td>
				<td>
					<input type="submit" name="cancel" value="Back"/>
					<input type="submit" name="save" value="Create page"/>
				</td>
			</tr>
		</table>
	</form>
	
	<?php
	if( $this->success )
	{
		echo '<div class="flash success">Saved</div>';
	}
	?>
</div>