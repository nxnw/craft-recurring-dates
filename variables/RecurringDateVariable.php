<?php
namespace Craft;

class RecurringDateVariable
{
	public function getDates($handle, $limit = null, $order = 'ASC', $groupBy = false){
		return craft()->recurringDate->getDates($handle, $limit, $order, $groupBy);
	}
}