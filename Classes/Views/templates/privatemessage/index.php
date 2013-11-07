<div class="titleBar">
	Inbox
</div>
<div id="newsContainer">
	<?php
	if( count($this->messages) > 0 )
	{
		foreach($this->messages as $message)
		{
			?>
			<a href="/privatemessage/view/<?=$message->id?>" class="newsItem">
				<?php
				$backgroundImage = $message->author->avatarURL();
				$user = $message->author;
				if( strlen($backgroundImage) < 5 )
				{
					$backgroundImage = '/resources/images/HGLogo.60.3.png';
				}
				?>
				<div class="image" style="background-image:url('<?=$backgroundImage?>');"></div>
				<div class="content">
					<div class="title">
						<?=$message->title?>
						<span class="author" style="font-size:11px;">
							From <?=$user->displayString()?>
						</span>
					</div>
					<div class="description">
						<?php
						$description = $message->content;
						if( strlen($description) > 140 )
						{
							$description = substr($description, 0, 140);
							$description .= '...';
						}
						echo strip_tags($description);
						?>
					</div>
				</div>
			</a>
			<?php
		}
	}
	else
	{
		echo '<br /><div class="flash info">You have no messages in your inbox</div>';
	}
	?>
</div>
<a style="float:left;margin-top:10px;margin-left:10px;" href="/privatemessage/create" class="button">Compose a new message</a>