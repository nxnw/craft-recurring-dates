<?php
namespace Craft;

class RecurringDateVariable
{
	public function getEvents($entries, $field){
		return craft()->recurringDate_sort->order($entries, $field);
	}

	public function getGroupedEvents($entries, $field){
		return craft()->recurringDate_sort->group($entries, $field);
	}
}