<?php
namespace Craft;

class RecurringDateVariable
{
	public function dates($handle, $opts){
		// $limit = null, $order = 'ASC', $groupBy = null, $before = null, $after = null, $criteria = null, $excludes = true

		$limit 		= ( isset($opts['limit']) ? $opts['limit'] : null );
		$order 		= ( isset($opts['order']) ? $opts['order'] : 'ASC' );
		$groupBy 	= ( isset($opts['group']) ? $opts['group'] : null );
		$before 	= ( isset($opts['before']) ? $opts['before'] : null );
		$after 		= ( isset($opts['after']) ? $opts['after'] : null );
		$criteria 	= ( isset($opts['criteria']) ? $opts['criteria'] : null );
		$excludes 	= ( isset($opts['excludes']) ? $opts['excludes'] : true );

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