<div id="headerMenu">
SystemInfo
</div>
<div class="contentBox">
<table class="form">
	<tr><td>Website version:</td><td><?php echo Config::siteVersion; ?></td></tr>
	<tr><td>NetKit version:</td><td><?php echo $this->netkitVersion; ?></td></tr>
	<tr><td>&nbsp; </td><td></td></tr>
	<tr>
		<td>Disk usage</td>
		<td><div class="progressBar"><div class="progress" style="width:<?php echo $this->diskUsage;?>%;"></td>
	<tr>
		<td>Memory usage</td>
		<td><div class="progressBar"><div class="progress" style="width:<?php echo $this->memoryPercentage;?>%;"></div></div></td>
	</tr>
	<tr><td>&nbsp; </td><td></td></tr>
	<tr><td>Webserver:</td><td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td></tr>
	<tr><td>Zend version:</td><td><?php echo zend_version(); ?></td></tr>
	<tr><td>PHP version:</td><td><?php echo str_ireplace("-1ubuntu1.1", "", phpversion()); ?></td></tr>
	<tr>
		<td>PHP Memory usage:</td>
		<td><?php echo round(memory_get_usage()/1024/1024,2).'mb'; ?> (<?php echo round(memory_get_peak_usage()/1024/1024, 2); ?>mb Max)</td>
	</tr>
	<tr>
	   <td>Database:</td>
	   <td><?php echo str_ireplace("-1~dotdeb.0", "", NKDatabase::sharedDatabase()->engineName()); ?></td>
	</tr>
	<tr><td>Server OS:</td><td><?php echo php_uname('s'); ?></td></tr>
	<tr><td>&nbsp; </td><td></td></tr>
	<!--<tr><td></td><td><a href="/systemInfo/update" class="button">Check for updates</a></td></tr>-->
</table>
</div>