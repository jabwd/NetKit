<div class="contentBox">
	<div class="newsTitle">
		<?=$this->message->title?>
		<br />
		<span class="author">Sent by 
		<a href="/user/view/<?=$this->message->author->id?>">
			<b><?=$this->message->author->displayString(false)?></b></a> 
		on <?=$this->message->sent?></span>
	</div>
	<div style="float:left;height:auto;width:640px;margin-bottom:10px;">
		<?php
		$parser = new BBCodeParser($this->message->content);
		echo nl2br($parser->result());
		?>
	</div>
	<br />
	<a href="/privatemessage/delete/<?=$this->message->id?>" class="button">Delete</a>
	<a href="/privatemessage/create/<?=$this->message->id?>" class="button">Reply</a>
</div>