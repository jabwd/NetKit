<div id="forum">
	<div class="headerMenu">
		Inbox
	</div>
	<?php
	echo '<div class="forum">';
	if( count($this->messages) > 0 )
	{
		foreach($this->messages as $message)
		{
			echo '<a href="/privatemessage/view/'.$message->id.'">'.$message->title.'</a>';
		}
	}
	else
	{
		echo '<br /><div class="flash info">You have no messages in your inbox</div>';
	}
	echo '</div>';
	?>
	<br />
	<a style="float:left;margin-top:10px;margin-left:10px;" href="/privatemessage/create" class="button">Compose a new message</a>
	<br />
	<br />
</div>