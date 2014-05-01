<?php
namespace Craft;

class RecurringDate_RuleRecord extends BaseRecord
{
	public function getTableName(){
		return 'recurringdate_rules';
	}

	public function defineRelations(){
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}

	protected function defineAttributes(){
		return array(
			'handle' 	=> AttributeType::String,
			'start_date'=> AttributeType::String,
			'start_time'=> AttributeType::String,
			'end_date' 	=> AttributeType::String,
			'end_time'	=> AttributeType::String,
			'allday' 	=> AttributeType::Bool,
			'repeats' 	=> AttributeType::Bool,
			'frequency' => array(AttributeType::Enum, 'values' => "daily, monthly, weekly, yearly"),
			'interval' 	=> AttributeType::Number,
			'weekdays' 	=> AttributeType::String,
			'repeat_by' => array(AttributeType::Enum, 'values' => "week, month"),
			'ends' 		=> array(AttributeType::Enum, 'values' => "after, until"),
			'count' 	=> AttributeType::Number,
			'until' 	=> AttributeType::String,
			'rrule' 	=> AttributeType::String,
		);
	}
}