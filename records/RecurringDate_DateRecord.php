<?php
namespace Craft;

class RecurringDate_DateRecord extends BaseRecord
{
	public function getTableName(){
		return 'recurringdate_dates';
	}

	public function defineRelations(){
		return array(
			'rule' => array(static::BELONGS_TO, 'RecurringDate_RuleRecord', 'onDelete' => static::CASCADE),
		);
	}

	protected function defineAttributes(){
		return array(
			'start' => AttributeType::DateTime,
			'end' => AttributeType::DateTime
		);
	}
}