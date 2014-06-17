<?php
namespace Craft;

use Recurr;

class RecurringDateService extends BaseApplicationComponent
{

	public $content;
	public $isNewContent;

	// Retrieves record from 3rd party db table
    public function getRule(BaseFieldType $fieldType)
    {
        // Load record (if exists)
        $ruleRecord = RecurringDate_RuleRecord::model()->findByAttributes(array(
            'elementId' => $fieldType->element->id,
            'handle'    => $fieldType->model->handle,
        ));

        // Get attributes
        if ($ruleRecord) {
            $attr = $ruleRecord->getAttributes();
            if( !empty($attr['start_date']) ){
            	$attr['start_date'] = DateTime::createFromString($attr['start_date'], craft()->getTimeZone());
            }
            else
            {
            	$attr['start_date'] = '';
            }

            if( !empty($attr['end_date']) ){
            	$attr['end_date'] = DateTime::createFromString($attr['end_date'], craft()->getTimeZone());
            }
            else
            {
            	$attr['end_date'] = '';
            }

            if( !empty($attr['until']) ){
            	$attr['until'] = DateTime::createFromString($attr['until'], craft()->getTimeZone());
            }
            else
            {
            	$attr['until'] = '';
            }

            if( !empty($attr['start_time']) ){
            	$attr['start_time'] = DateTime::createFromFormat('H:i:s', $attr['start_time'], craft()->getTimeZone());
            }
            else
            {
            	$attr['start_time'] = '';
            }

            if( !empty($attr['end_time']) ){
            	$attr['end_time'] = DateTime::createFromFormat('H:i:s', $attr['end_time'], craft()->getTimeZone());
            }
            else
            {
            	$attr['end_time'] = '';
            }

        } else {
            $attr = array();
        }

        return $attr;
    }

    public function validateRule(BaseFieldType $fieldType){
    	$postContent = $fieldType->element->getContentFromPost();
		$value = $postContent[$fieldType->model->handle];

		$startDate = $value['start_date']['date'];
		$startTime = $value['start_time']['time'];
		$endDate = $value['end_date']['date'];
		$endTime = $value['end_time']['time'];

		$allday = $value['allday'];
		$repeats = $value['repeats'];
		$ends = $value['ends'];
		$until = $value['until']['date'];
		$count = $value['count'];

		$errors = array();

		if( empty($startDate) ){
			$errors[] = Craft::t('There must be a valid Start Date');
		}

		if( empty($startTime) && !$allday ){
			$errors[] = Craft::t('If not all day event Start Time must be set');
		}

		if( empty($endDate) && !empty($endTime) ){
			$errors[] = Craft::t('If End Time is set, End Date must also be set');
		}

		if( !empty($endDate) && empty($endTime) && !$allday ){
			$errors[] = Craft::t('If End Date is set, End Time must also be set');
		}

		//Checking Dates
		if( !empty($endDate) && !empty($startDate) && empty($endTime) && empty($startTime) ){
			if( strtotime($endDate) <= strtotime($startDate) ){
				$errors[] = Craft::t('End Date must be after the Start Date');
			}
		}

		//Checking Times
		if( !empty($endDate) && !empty($startDate) && !empty($endTime) && !empty($startTime) ){
			if( strtotime($endDate . ' ' . $endTime) <= strtotime($startDate . ' ' . $startTime) ){
				$errors[] = Craft::t('End Date/Time must be after the Start Date/Time');
			}
		}

		//Check until date
		if( empty($until) && $ends == 'until' && $repeats ){
			$errors[] = Craft::t('Until date must be set if repeating until a specific date');
		}
		elseif( !empty($until) && $ends == 'until' && $repeats ){
			if( strtotime($until) <= strtotime($startDate) ){
				$errors[] = Craft::t('Until date must be after the Start Date');
			}
		}

		//Check after count
		if( empty($count) && $ends == 'after' && $repeats ){
			$errors[] = Craft::t('After count must be set');
		}

		if($errors){
			return $errors;
		}
		else{
			return true;
		}
    }

    // // Modify fieldtype query
    // public function modifyQuery(DbCommand $query, $params = array())
    // {
    //     // Join with plugin table
    //     $query->join('recurringdate_rules', 'elements.id='.craft()->db->tablePrefix.'recurringdate_rules'.'.elementId');
    //     // Search by comparing coordinates
    //     // Return modified query
    //     return $query;
    // }

    // Once the content has been saved...
    public function contentSaved(ContentModel $content, $isNewContent)
    {
        $this->content = $content;
        $this->isNewContent = $isNewContent;
    }

    // Save field to plugin table
    public function saveRuleField(BaseFieldType $fieldType)
    {
        // Get elementId and handle
        $elementId = $fieldType->element->id;
        $handle    = $fieldType->model->handle;

        // Check if attribute exists
        if (!$this->content->getAttribute($handle)) {
            return false;
        }

        // Set specified attributes
        $attr = $this->content[$handle];

        // Attempt to load existing record
        $ruleRecord = RecurringDate_RuleRecord::model()->findByAttributes(array(
            'elementId' => $elementId,
            'handle'    => $handle,
        ));

        // If no record exists, create new record
        if (!$ruleRecord) {
            $ruleRecord = new RecurringDate_RuleRecord;
            $attr['elementId'] = $elementId;
            $attr['handle']    = $handle;
        }

		$attr['start_date'] = ( !empty($attr['start_date']['date']) ? strtotime($attr['start_date']['date']) : null );
		$attr['end_date'] 	= ( !empty($attr['end_date']['date']) ? strtotime($attr['end_date']['date']) : null );
		$attr['start_time'] = ( !empty($attr['start_time']['time']) ? date('H:i:s', strtotime($attr['start_time']['time'])) : null );
		$attr['end_time'] = ( !empty($attr['end_time']['time']) ? date('H:i:s', strtotime($attr['end_time']['time'])) : null );


		if( isset($attr['exdates']) ){
			$rawExDates = $attr['exdates'];
			$attr['exdates'] = array();

			foreach ($rawExDates as $index => $exdate) {
				$attr['exdates'][] = !empty($exdate['date']) ? strtotime($exdate['date']) : null;
			}
		}
		else{
			$attr['exdates'] = array();
		}

		if (!isset($attr['allday'])) 	{ $attr['allday'] =null; }
		if (!isset($attr['repeats'])) 	{ $attr['repeats'] =null; }
		if (!isset($attr['frequency'])) { $attr['frequency'] =null; }
		if (!isset($attr['interval']))  { $attr['interval'] =null; }
		if (!isset($attr['weekdays']))  { $attr['weekdays'] =null; }
		if (!isset($attr['repeat_by'])) { $attr['repeat_by'] =null; }
		if (!isset($attr['ends'])) 		{ $attr['ends'] = null;} 
		if (!isset($attr['count'])) 	{ $attr['count'] = null;} 
		
		$attr['until'] = ( isset($attr['until']['date']) ? strtotime($attr['until']['date']) : null );

		$attr['rrule'] = $this->buildRRule($attr);

        // Set record attributes
        $ruleRecord->setAttributes($attr, false);

        $ruleSaved = $ruleRecord->save();
        
        $id = $ruleRecord->id;
        
        RecurringDate_DateRecord::model()->deleteAll('ruleId = '. $id);

        $this->generateDates($attr['rrule'], $id, $attr['repeats'], $attr['start_date'], $attr['end_date']);

        return $ruleSaved;

    }

    private function generateDates($rrule, $id, $repeats, $start, $end){
    	$finalDates = array();
		$start = DateTime::createFromString($start, craft()->getTimeZone());

		if(!is_null($end)){
			$end = DateTime::createFromString($end, craft()->getTimeZone());
		}

    	if($repeats){
    		$rule = new Recurr\RecurrenceRule($rrule);
			$ruleTransformer = new Recurr\RecurrenceRuleTransformer($rule, 300);
			$dates = $ruleTransformer->getComputedArray();

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
				
	    		
				$datesValues['start'] = DateTime::createFromFormat('Ymd\THis', $startDateString, craft()->getTimeZone());

				if( !empty($end) ){
					$datesValues['end'] = DateTime::createFromFormat('Ymd\THis', $endDateString, craft()->getTimeZone());
				}

				$fullDates[] = $datesValues;
	 		}

	 		$finalDates = $fullDates;
		}
		else{
			$finalDates = array(array( 'start' => $start, 'end' => $end ));
		}

    	foreach ($finalDates as $index => $date) {
    		$dateRecord = new RecurringDate_DateRecord;
    		$dateRecord->setAttributes(array(
	    		'ruleId' => $id,
	    		'start' => $date['start'],
	    		'end' => $date['end'],
    		), false);
    		$dateRecord->save();
    	}
    }

 	// SELECT d.start, d.end, r.end_time, r.start_time, r.allday, r.repeats, r.rrule
	// FROM craft_recurringdate_dates d
	// LEFT JOIN craft_recurringdate_rules r
	// ON d.ruleId = r.id
	// WHERE handle='eventDate'
	// ORDER BY start, start_time DESC

    // public function getCriteria($attributes = null){

    // }

    public function getDate($id){
    	$query = craft()->db->createCommand()
    		->select('d.id, r.elementId, d.start start_date, d.start start, d.end end_date, d.end end, r.end_time, r.start_time, r.allday, r.repeats, r.rrule')
    		->from('recurringdate_dates d')
    		->leftJoin('recurringdate_rules r', 'd.ruleId = r.id')
    		->where('d.id=:id', array(':id'=>$id));

    	return $query->queryRow();
    }

    public function getDates($handle, $limit, $order, $groupBy, $before, $after, $criteria, $excludes){

    	$query = craft()->db->createCommand()
    		->select('d.id, r.elementId, d.start start_date, d.start start, d.end end_date, d.end end, r.end_time, r.start_time, r.allday, r.repeats, r.rrule')
    		->from('recurringdate_dates d')
    		->leftJoin('recurringdate_rules r', 'd.ruleId = r.id')
    		->where('handle=:handle', array(':handle'=>$handle));

	    
    	if( !is_null($before) ){
    		$beforeValue = date('Y-m-d H:i:s', strtotime($before));
    		$query->andWhere(':before >= d.start', array(':before'=>$beforeValue));
    	}

    	if( !is_null($after) ){
    		$afterValue = date('Y-m-d H:i:s', strtotime($after));
    		$query->andWhere(':after <= d.start', array(':after'=>$afterValue));
    	}

    	if( !is_null($criteria) ){
    		$critArr = array();
	    	foreach ($criteria as $index => $entry) {
	    		$critArr[] = $entry->id;
	    	}

	    	$critIds = implode(',', $critArr);

	    	$query->andWhere('r.elementId IN (:ids)', array(':ids'=>$critIds));
	    }

    	if($order == 'ASC'){
    		$query->order(array('start ASC', 'start_time ASC'));
    	}
    	else{
    		$query->order(array('start DESC', 'start_time DESC'));
    	}


    	if( !is_null($limit) ){
    		$query->limit($limit * 2);
    	}

    	
    	$events = $query->queryAll();

    	$eventsFinal = array();

    	foreach ($events as $index => $value) {
    		$id = $value['elementId'];
    		if( $excludes ){
    			$exdates = $this->getExdates($value['rrule']);
	    		if( !in_array(date('Ymd', strtotime($value['start'])), $exdates) ){
		    		$eventsFinal[] = array(
		    			'date' => $value,
		    			'entry' => craft()->entries->getEntryById($id),
		    		);
		    	}
		    }
		    else{
		    	$eventsFinal[] = array(
	    			'date' => $value,
	    			'entry' => craft()->entries->getEntryById($id),
	    		);
		    }
    	}

    	if( !is_null($limit) ){
    		$eventsFinal = array_slice($eventsFinal, 0, $limit);
    	}

    	if( !is_null($groupBy) ){
    		if( $groupBy == 'day' ){
    			$eventsFinal = $this->groupBy($eventsFinal, 'n/j/Y');
    		}
    		elseif( $groupBy == 'month' ){
    			$eventsFinal = $this->groupBy($eventsFinal, 'n/1/Y');
    		}
    		elseif( $groupBy == 'year' ){
    			$eventsFinal = $this->groupBy($eventsFinal, '1/1/Y');
    		}
    	}

    	return $eventsFinal;
    }

    private function getExdates($rrule){
    	if( strpos($rrule, 'EXDATE') !== false ){
            $exdates = array();

            $exDatesArray = explode('EXDATE=', $rrule);
            $exDatesString = $exDatesArray[1];
            $exDatesString = rtrim($exDatesString, ";");
            $exDatesArray = explode(',', $exDatesString);
            
            foreach ($exDatesArray as $index => $date) {
            	$exdates[] = date('Ymd', strtotime($date));
            }
        }
        else{
        	$exdates = array();
        }
        return $exdates;
    }

    private function groupBy($events, $groupString){
    	$dates = array();
    	foreach ($events as $i => $date) {
			$dateStart = $date['date']['start'];
			$formDate = date($groupString, strtotime($dateStart));
			if( isset($dates[$formDate]) ){
				$dates[$formDate][] = array( 
					'entry' => $date['entry'],
					'date' => $date['date']
				);
			}
			else{
				$dates[$formDate] = array(array(
					'entry' => $date['entry'],
					'date' => $date['date']
				));
			}
		}

		return $dates;
    }

    private function buildRRule($settings){
    	$allday 	= $settings['allday'];
		$startDate 	= $settings['start_date'];
		$endDate 	= $settings['end_date'];
		$startTime 	= $settings['start_time'];
		$endTime 	= $settings['end_time'];
		$repeats 	= $settings['repeats']; //Does it repeat?
		$frequency 	= $settings['frequency']; //Weekly, Daily, Monthly, Yearly
		$interval 	= $settings['interval']; // i.e. Every 1-30 Months?
		$weekDays 	= $settings['weekdays']; //Which weekdays
		$repeatBy 	= $settings['repeat_by']; //Monthly, by day of week, or day of month
		$ends 		= $settings['ends']; //how it ends (never, after, until)
		$count 		= $settings['count']; // if ending occurs amounts
		$untilDate 	= $settings['until']; // if ending until date
		$exDates 	= $settings['exdates'];

		$dbString = '';

		//Builds RRULE based on UI Elements Input
		if($repeats){
			$rule = new Recurr\RecurrenceRule();
			$rule->setStartDate(DateTime::createFromString($startDate, craft()->getTimeZone()));
			$rule->setInterval($interval);

			if($ends == 'until'){
				$rule->setEndDate(DateTime::createFromString($untilDate, craft()->getTimeZone()));
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
							$dayOfMonth = date('j', $startDate);
							$rule->setByMonthDay(array($dayOfMonth));
						}
						else if( $repeatBy == 'week' ){
							$uStartDate = $startDate;
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
				$time = date('Ymd\THis', strtotime( date('Y-m-d', $startDate) . date(' H:i:s', strtotime($startTime))));
				$dbString .= 'DTSTART=' . $time . ';' . $rule->getString();
			}
			else{
				$time = $startDate;
				var_dump($startDate);
				$dbString .= 'DTSTART=' . date('Ymd', $time) . ';' . $rule->getString();
			}

			if( count($exDates) > 0 ){
				$dbString .= ';EXDATE=';
				foreach ($exDates as $index => $date) {
					$dbString .= date('Ymd', $date);
					if( $date !== end($exDates) ){
						$dbString .= ',';
					}
				}
				$dbString .= ';';
			}
		}
		else{
			if($startTime){
				$time = date('Ymd\THis', strtotime( date('Ymd', $startDate) . date('\THis', strtotime($startTime))));
				$dbString .= 'DTSTART=' . $time . ';';
			}
			else{
				$dbString .= 'DTSTART=' . date('Ymd', $startDate) . ';';
			}		
		}

		return $dbString;
    }
}