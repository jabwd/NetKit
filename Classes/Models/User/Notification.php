<?php
class Notification extends NKTableRow
{
	public $tableName = "Notifications";
	
	public function goToPage() {
		redirect($this->URL);
	}
	
	/*
	 * Description: Sends a notification to all users on Tnggo that either have the 'admin' or 'highadmin' permission flag
	 *
	 * Returns:		void
	 */
	public static function postNotificationForAdmins($message = "New notification", $URL = "/") {
		if( ! $message || ! $URL ) {
			return;
		}
		
		// get all the admins of this website
		$result = Permissions::defaultTable()->findWhere("permission = 'admin' OR permission = 'highAdmin'");
		foreach($result as $row) {
			$userID = (int)$row->userID;
			if( $userID > 0 )
			{
				// found an admin, send the notification
				Notification::sendNotification($userID, $message, $URL);
			}
		}
	}
	
	/*
	 * Description:	this method is made so you do not have to bother writing the creation of a Notification instance
	 *				when you want to send notifications to users in your controllers
	 *
	 * Returns:		the notification ID
	 */
	public static function sendNotification($recipient = 0, $message = "New notification", $URL = "/") {
		$recipient = (int)$recipient;
		if( $recipient < 1 || ! $message || ! $URL )
		{
			return -1; 
		}
		$notification 			= new Notification();
		$notification->userID 	= $recipient;
		$notification->message	= $message;
		$notification->URL		= $URL;
		$notification->created	= time();
		
		return $notification->save();
	}
}
?>