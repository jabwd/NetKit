<?php
if( NKSession::access('pages.manage') ) {
	?>
	<div class="toolbar">
		<a class="item settings" href="/page/edit/<?=$this->page->id?>"></a>
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