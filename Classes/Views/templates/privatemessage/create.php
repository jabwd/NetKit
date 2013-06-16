<div class="headerMenu">
Create a Private Message
</div>
<?php
if( $this->success )
{
	echo '<br /><br /><div class="flash success">Your message has been sent</div>';
}
else
{
	if( is_array($this->errors) ) 
	{
		echo '<br /><br />';
		foreach($this->errors as $error) 
		{
			echo '<div class="flash warning">' . $error . '</div>';
		}
	}
	$title = $_POST['title'];
	if( !$title && $this->message )
	{
		$title = "Re: ".$this->message->title;
	}
	$content = $_POST['content'];
	if( !$content && $this->message )
	{
		$content = "\"".$this->message->content."\"\n\n";
	}
	$username = $_POST['username'];
	if( !$username && $this->message )
	{
		$username = $this->message->author->username;
	}
	?>
	<div class="contentBox">
		<form method="post">
			<input type="text" value="<?=$username?>" placeholder="Username" name="username"/>
			<br />
			<br />
			<input type="text" value="<?=$title?>" placeholder="title" name="title"/>
			<br />
			<br />
			<textarea name="content" placeholder="Message"><?=$content?></textarea>
			<br />
			<input type="submit" name="create" value="Send"/>
		</form>
	</div>
	<?php
}
?>