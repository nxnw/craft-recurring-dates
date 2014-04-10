<?php
namespace RecurringDate; 

use Recurr;
use Craft;

class AdvancedDate {

	protected $value;
	protected $rule;

	public function __construct($value = null) {
		if( !is_null($value) ){
			$this->value = $value;
		}
		else{
			$this->value = '';
		}
		
		if( $this->isRecurring() ){	
			$this->rule = new Recurr\RecurrenceRule($value);
		}
		else{
			$this->rule = false;
		}
	}

	public function isRecurring() {
		return strpos($this->value, "FREQ") !== false;
	}

	public function getStartDate() {

		if( strpos($this->value, "DTSTART") !== false ){

			if( $this->isRecurring() ){
				$startFormat = $this->rule->getStartDate()->format('Ymd');
				$startDate = \Craft\DateTime::createFromFormat('Ymd', $startFormat);
			}
			else{
				$startDateTime = rtrim(explode('DTSTART=', $this->value)[1], ';');
				$startDate = \Craft\DateTime::createFromFormat('Ymd\THis', $startDateTime);
			}
		}
		else{
			$startDate = null;
		}

		return $startDate;
	}

	public function getStartTime() {
		
		if( strpos( explode( ';', explode( 'DTSTART=', $this->value )[1] )[0], 'T' ) !== false ) {

			if( $this->isRecurring() ){
				$startTimeStr = $this->rule->getStartDate()->format('His');
				$startTime = \Craft\DateTime::createFromFormat('His', $startTimeStr);
			}
			else{
				$startDateTime = rtrim(explode('DTSTART=', $this->value)[1], ';');
				$startDate = \Craft\DateTime::createFromFormat('His', $startDateTime);
			}
		}
		else{
			$startTime = null;
		}

		return $startTime;
	}

	public function getDates() {

		if($this->isRecurring()){

			$ruleTransformer = new Recurr\RecurrenceRuleTransformer($this->rule, 300);
			$dates = $ruleTransformer->getComputedArray();

			$start = $this->rule->getStartDate();

			$end = $this->getEndDate();


			if( !is_null($end) ){
				$durationInterval = $start->diff($end);
			}
			else{
				$durationInterval = $start->diff($start);
			}

			$fullDates = array();
			foreach ($dates as $date) {
				$end = clone $date;
				$end = $end->add($durationInterval);

				$startDateString = $date->format('Ymd\THis');
				$endDateString = $end->format('Ymd\THis');
				
				$datesValues['start'] = \Craft\DateTime::createFromFormat('Ymd\THis', $startDateString);

				if( strpos($this->value, "DTEND") !== false ){
					$datesValues['end'] = \Craft\DateTime::createFromFormat('Ymd\THis', $endDateString);
				}

				$fullDates[] = $datesValues;
	 		}
	 		return $fullDates;
		}
		else{
			return array( array( 'start' => $this->getStartDate() ) );
		}

	}

	public function getEndDate() {

		if( strpos($this->value, "DTEND") !== false ){
			
			$endDateTime = explode(';', explode( 'DTEND=', $this->value)[1])[0];
			
			//If time is set we have to change the format
			if( strpos( explode(';', explode( 'DTEND=', $this->value)[1])[0], 'T' ) !== false ) {
				$endDate = \Craft\DateTime::createFromFormat('Ymd\THis', $endDateTime);
			}
			else{
				$endDate = \Craft\DateTime::createFromFormat('Ymd', $endDateTime);
			}
		}
		else{
			$endDate = null;
		}

		return $endDate;
	}

	public function getEndTime() {
		
		if( strpos($this->value, "DTEND") !== false ){
			if( strpos( explode(';', explode( 'DTEND=', $this->value)[1])[0], 'T' ) !== false ) {
				$endDateTime = rtrim(explode('DTEND=', $this->value)[1], ';');
				$endTime = \Craft\DateTime::createFromFormat('His', $endDateTime);
			}
			else{
				$endTime = null;
			}
		}
		else{
			$endTime = null;
		}

		return $endTime;
	}

	public function getMonthBy() {

		if($this->isRecurring()){
			if( strpos($this->value, "BYMONTHDAY") !== false ){
				return 'month';
			}
			elseif( strpos($this->value, "BYDAY") !== false ) {
				return 'week';
			}
		}
		else{
			return null;
		}
	}

	public function getEndProperty(){
		if( strpos($this->value, "COUNT") !== false ){
			return 'after';
		}
		elseif( strpos($this->value, "UNTIL") !== false ){
			return 'until';
		}
		else{
			return 'never';
		}
	}

	public function getUntilDate(){
		$untilDateStr = $this->rule->getUntil()->format('Ymd');
		$untilDate = \Craft\DateTime::createFromFormat('Ymd', $untilDateStr);
		return $untilDate;
	}

	public function isAllday() {
		if( strpos($this->value, "ALLDAY") !== false ){
			return true;
		}
		else{
			return false;
		}
	}

	public function getRule(){
		return $this->rule;
	}
}