<?php
namespace Craft;

class RecurringDateVariable
{
	public function dates($handle, $limit = null, $order = 'ASC', $groupBy = null, $before = null, $after = null, $criteria = null, $excludes = true){
		return craft()->recurringDate->getDates($handle, $limit, $order, $groupBy, $before, $after, $criteria, $excludes);
	}

	public function date($id) {
		return craft()->recurringDate->getDate($id);
	}

	// Will implement a better query system similar to the ElementCriteriaModel
	// public function dates($criteria = null){
	// 	return craft()->recurringDate->getCriteria($criteria);
	// }
}