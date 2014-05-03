<?php
namespace Craft;

require('vendor/autoload.php');

class RecurringDatePlugin extends BasePlugin
{

	public function init()
	{
		parent::init();
    	
		craft()->on('content.onSaveContent', function(Event $event) {
			craft()->recurringDate->contentSaved($event->params['content'], $event->params['isNewContent']);
		});
	}

	function getName()
	{
		return Craft::t('Recurring Dates');
	}

	function getVersion()
	{
		return '0.3';
	}

	function getDeveloper()
	{
		return 'NXNW';
	}

	function getDeveloperUrl()
	{
		return 'http://nxnw.net';
	}
}