<?php
if( count($this->pagesList) > 0 )
{
	echo '<table class="list"><tr><th>Page title</th><th></th></tr>';
	foreach($this->pagesList as $page)
	{
		echo '<tr class="clickable" onclick="goToPage(\'page/edit/'.$page->id.'\');"><td><a href="/page/edit/'.$page->id.'">'.$page->title.'</a></td>';
		echo '<td><a class="button critical" href="/page/delete/'.$page->id.'">Delete page</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}
else
{
	?>
	<div class="contentBox">
		<div class="flash info">
			There are currently no static pages
		</div>
	</div>
	<?php
}
?>
<div class="contentBox">
	<a href="/page/create/" class="button">Create page</a>
</div>