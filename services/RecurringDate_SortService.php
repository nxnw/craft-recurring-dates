<?php
namespace Craft;

class RecurringDate_SortService extends BaseApplicationComponent
{
	public function order($entries, $field){
		$dates = array();
		foreach ($entries as $index => $entry) {
			foreach ($entry->eventDate['dates'] as $i => $date) {
				$dates[] = array( 
					'start' => $date['start'],
					'entry' => $entry
				);
			}
		}

		$cmp = function($a, $b){
			return strcmp($a['start'], $b['start']);
		};

		usort($dates, $cmp);

		return $dates;
	}

	public function group($entries, $field){
		$dates = array();

		//Group the entries by date
		foreach ($entries as $index => $entry) {
			foreach ($entry->eventDate['dates'] as $i => $date) {
				$date = $date['start'];
				$formDate = strtotime($date->format('Ymd'));
				if( isset($dates[$formDate]) ){
					$dates[$formDate][] = $entry;
				}
				else{
					$dates[$formDate] = array($entry);
				}
			}
		}

		//Sort by dates
		ksort($dates);

		// $cmp = function($a, $b){
		// 	return strcmp($a->eventDate['startdate'], $b->eventDate['startdate']);
		// };

		// foreach ($dates as $index => $date) {
		// 	usort($date, $cmp);
		// }

		return $dates;
	}

}