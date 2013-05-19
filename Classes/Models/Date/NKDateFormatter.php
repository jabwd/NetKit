<?php
/**
 * This class offers lots of static methods in order to improve
 * performance in big lists of stuff and to reduce object creation
 * for such lists, can't have that on a high load web page
 */
class NKDateFormatter {
	protected $timestamp;

	public function __construct($time) 
	{
		if( $time ) 
		{
			// if we cannot get an integer representation of this
			// timestamp we will try to parse it as a date string
			$this->timestamp = (int)$time;
			if( $this->timestamp === 0 ) 
			{
				$this->timestamp = strtotime($time);
			}
		}
	}
	
	/**
	 * The non static vesion of simpleDeltaDateString()
	 * so that you can execute that on the timestamp you might've created
	 * using the parser of NKDateFormatter's contstructor
	 */
	public function deltaDateString() 
	{
		return self::simpleDeltaDateString($this->timestamp);
	}
	
	// TODO: Make this function suck less,
	// If you are using 86400 with date & time you're doing it WRONG
	public static function simpleDeltaDateString($timestamp) 
	{
		$delta = (int)(time()-$timestamp);
		
		// WARNING: NOT ACCURATE!!!!
		// Although this *logically* seems right
		// it isn't how date & time ACTUALLY works
		// PHP WHERE ARE YOUR GOD DAMN APIS FFS
		// 60 seconds in 1 minute
		// 3600 seconds in 1 hour
		// 86400 seconds in 1 day
		// 2592000 seconds in 1 month
		// 31104000 seconds in 1 year
		
		$years = floor($delta/31104000);
		$delta -= $years*31104000;
		$months = floor($delta/2592000);
		$delta -= $months*2592000;
		$days = floor($delta/86400);
		$delta -= $days*86400;
		$hours = floor($delta/3600);
		$delta -= $hours*3600;
		$minutes = floor($delta/60);
		$delta -= $minutes*60;
		
		// determine which format is relevant
		if( $years > 0 ) {
			return $years.' '.(($years==1) ? "year":"years").' ago';
		} else if( $months > 0 ) {
			return $months.' '.(($months==1) ? "month":"months"). ' ago';
		} else if( $days > 0 ) {
			return $days.' '.(($days==1) ? "day":"days").' ago';
		} else if( $hours > 0 ) {
			return $hours.' '.(($hours==1) ? "hour":"hours").' ago';
		} else if( $minutes > 0 ) {
			return $minutes.' '.(($minutes==1) ? "minute":"minutes").' ago';
		} else {
			return 'Just now';
		}
	}
	
	/**
	 * For this class it is quite important that we disallow
	 * direct access to the timestamp
	 * we want to make sure that date & time are done properly
	 */
	public function timestamp() {
		return $this->timestamp;
	}
}