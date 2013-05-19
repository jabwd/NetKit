<?php
class SystemInfoController extends NKActionController
{
	public function __construct()
	{
		$this->name = "System information";
	}
	
	public function handleRequest($request = null) {
		$this->view = new NKView('systeminfo/index', $this, 'NetKit/Classes/Views/templates/');
		return $this->view->pageExists();
	}
	
	public function indexAction()
	{
		// getting the netkit version like this should be easy
		// since the base paht should be the same
		$gitversion 	= exec("git rev-parse --short HEAD");
		$memInfo 		= file("/proc/meminfo");
		$totalMemory 	= (int)str_replace(" kB","",str_replace("MemTotal: ","",$memInfo[0]));
		$usedMemory 	= (int)str_replace(" kB","",str_ireplace("Active: ","",$memInfo[5]));


		$this->view->diskUsage 			= ceil((disk_free_space("/")/disk_total_space("/"))*100);
		$this->view->usedMemory 		= $usedMemory;
		$this->view->totalMemory 		= $totalMemory;
		$this->view->memoryPercentage 	= ceil(($usedMemory/$totalMemory)*100);
		$this->view->netkitVersion 		= NKWebsite::NetKitVersion.' ('.$gitversion.')';
	}
	
	public function updateAction()
	{
		// check with GIT whether there is a new version available for either the website or 
		// for NetKit
		
		// check for updates of NetKit
		exec("git fetch origin");
		echo 'hi';
		print_r(exec("git log HEAD..origin/master --oneline"));
		echo 'henk';
		exit;
		
		redirect("/systemInfo/");
	}
}