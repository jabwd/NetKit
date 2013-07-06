<?php
if( NKSession::access('pages.manage') ) {
	?>
	<div class="toolbar">
		<a class="item red" href="/page/delete/<?=$this->page->id?>">Delete page</a>
		<a class="item blue" href="/page/edit/<?=$this->page->id?>">Edit page</a>
	</div>
	<?php
}
?>
<div class="contentBox">
<div class="newsTitle"><?=$this->page->title?></div>
<?php
$parser = new BBCodeParser($this->page->content);
echo nl2br($parser->result());
?>
</div>