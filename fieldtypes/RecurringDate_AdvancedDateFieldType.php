<?php
namespace Craft;

use When;
use Recurr;
use RecurringDate;

class RecurringDate_AdvancedDateFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('Advanced Date');
	}
	
	/**
	 * Display fieldtype
	 *
	 * @param string $name  Our fieldtype handle
	 * @return string Return our fields input template
	 */
	public function getInputHtml($name, $value)
	{

		$value['name'] = $name;
		
		$id = craft()->templates->formatInputId($name);

		return craft()->templates->render('recurringdate/fields', $value);
	}

	public function prepValue($value){

		$dateObj = new RecurringDate\AdvancedDate($value);
		$rule = $dateObj->getRule();

		$fieldValues['startdate'] = $dateObj->getStartDate();
		$fieldValues['enddate'] = $dateObj->getEndDate();
		$fieldValues['dates'] = $dateObj->getDates();

		if( $dateObj->isAllday() ){
			$fieldValues['starttime'] = null;
			$fieldValues['endtime'] = null;
		}
		else{
			$fieldValues['starttime'] = $dateObj->getStartTime();
			$fieldValues['endtime'] = $dateObj->getEndTime();
		}
		
		//If it repeats and has an rrule object
		if( $dateObj->isRecurring() ){
			
			$fieldValues['by'] = $dateObj->getMonthBy();
			$fieldValues['repeats'] = true;
			$fieldValues['interval'] = strtolower($rule->getFreqAsText());
			$fieldValues['every'] = $rule->getInterval();
			$fieldValues['on'] = $rule->getByDay();
			
			$endMethod = $dateObj->getEndProperty();
			$fieldValues['ends'] = $endMethod;

			if( $endMethod == 'after' ){
				$fieldValues['occurrences'] = $rule->getCount();
			}
			elseif( $endMethod == 'until' ){
				$fieldValues['untildate'] = $dateObj->getUntilDate();
			}

		}
		elseif( strpos($value, "DTSTART") !== false ){
			$fieldValues['repeats'] = false;
		}
		else{
			$fieldValues['repeats'] = false;
		}

		//Set allday variable
		$fieldValues['allday'] = $dateObj->isAllday();

		return $fieldValues;
	}

	public function validate($value){
		$postContent = $this->element->getContentFromPost();
		$value = $postContent[$this->model->handle];

		$startDate = $value['startdate']['date'];
		$startTime = $value['starttime']['time'];
		$endDate = $value['enddate']['date'];
		$endTime = $value['endtime']['time'];

		$errors = array();

		if( empty($startDate) ){
			$errors[] = Craft::t('There must be a valid Start Date');
		}

		//Checking Dates
		if( !empty($endDate) && !empty($startDate) && empty($endTime) && empty($startTime) ){
			if( strtotime($endDate) < strtotime($startDate) ){
				$errors[] = Craft::t('End Date must be after the Start Date');
			}
		}

		//Checking Times
		if( !empty($endDate) && !empty($startDate) && !empty($endTime) && !empty($startTime) ){
			if( strtotime($endDate . ' ' . $endTime) < strtotime($startDate . ' ' . $startTime) ){
				$errors[] = Craft::t('End Date/Time must be after the Start Date/Time');
			}
		}

		if($errors){
			return $errors;
		}
		else{
			return true;
		}
	}

	public function prepValueFromPost($value){
		// echo '<pre>';
		// var_dump($value);
		// echo '</pre>';
		$allday = $value['allday'];
		$startDate = $value['startdate']['date'];
		$startTime = $value['starttime']['time'];
		$endDate = $value['enddate']['date'];
		$endTime = $value['endtime']['time'];
		$repeats = $value['repeats']; //Does it repeat?
		$frequency = $value['interval']; //Weekly, Daily, Monthly, Yearly
		$interval = $value['every']; // i.e. Every 1-30 Months?
		$weekDays = $value['on']; //Which weekdays
		$repeatBy = $value['by']; //Monthly, by day of week, or day of month
		$ends = $value['ends']; //how it ends (never, after, until)
		$count = $value['occurrences']; // if ending occurs amounts
		$untilDate = $value['untildate']['date']; // if ending until date

		$dbString = '';


		//Builds RRULE based on UI Elements Input
		if($repeats){
			$rule = new Recurr\RecurrenceRule();
			$rule->setStartDate(new \DateTime($startDate));
			$rule->setInterval($interval);

			if($ends == 'until'){
				$rule->setEndDate(new \DateTime($untilDate));
			}
			else if($ends == 'after'){
				$rule->setCount($count);
			}

			switch ($frequency) {
				case 'daily':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_DAILY);
					break;

				case 'weekly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_WEEKLY);
						if( empty($weekDays) ){
							//If weekdays empty set monday by default
							$rule->setByDay(array('MO'));
						}
						else{
							$rule->setByDay($weekDays);
						}
					break;

				case 'monthly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_MONTHLY);
						if( $repeatBy == 'month' ){
							$dayOfMonth = date('j', strtotime($startDate));
							$rule->setByMonthDay(array($dayOfMonth));
						}
						else if( $repeatBy == 'week' ){
							$uStartDate = strtotime($startDate);
							$dayOfWeek = strtoupper( substr( date( 'D', $uStartDate ), 0, -1) );
							$numberOfWeek = ceil( date( 'j', $uStartDate ) / 7 );
							$rule->setByDay(array('+'.$numberOfWeek . $dayOfWeek));
						}
					break;

				case 'yearly':
						$rule->setFreq(Recurr\RecurrenceRule::FREQ_YEARLY);
					break;
			}

			if($startTime){
				$time = strtotime($startDate . ' ' . $startTime);
				$dbString .= 'DTSTART=' . date('Ymd\THis', $time) . ';' . $rule->getString();
			}
			else{
				$time = strtotime($startDate);
				$dbString .= 'DTSTART=' . date('Ymd', $time) . ';' . $rule->getString();
			}
		}
		else{
			if($startTime){
				$time = strtotime($startDate . ' ' . $startTime);
				$dbString .= 'DTSTART=' . date('Ymd\THis', $time) . ';';
			}
			else{
				$time = strtotime($startDate);
				$dbString .= 'DTSTART=' . date('Ymd', $time) . ';';
			}		
		}

		if($endDate){
			if($endTime){
				$time = strtotime($endDate . ' ' . $endTime);
				$dbString = 'DTEND=' . date('Ymd\THis', $time) . ';' . $dbString;
			}
			else{
				$time = strtotime($endDate);
				$dbString = 'DTEND=' . date('Ymd', $time) . ';' . $dbString;
			}
		}

		if($allday){
			$dbString = 'ALLDAY=1;' . $dbString;
		}

		return $dbString;
	}
}