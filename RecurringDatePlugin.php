<?php
namespace Craft;

require('vendor/autoload.php');

class RecurringDatePlugin extends BasePlugin
{
	function getName()
	{
		return Craft::t('Recurring Dates');
	}

	function getVersion()
	{
		return '0.2';
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