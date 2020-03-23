<?php
	/*
	 Returns an array of events according to the 
	 format specified here: https://fullcalendar.io/docs/event-object
	 */

	define('PREPEND_PATH', '../');
	@header('Content-type: application/json');

	$hooks_dir = dirname(__FILE__);
	include("{$hooks_dir}/../defaultLang.php");
	include("{$hooks_dir}/../language.php");
	include("{$hooks_dir}/../lib.php");

	// event config
	$type = 'egypt-all-stats';
	$color = 'primary';
	$defaultClasses = "text-{$color} bg-{$color}";
	$table = 'confirmed';
	$customWhere = 'country=\'Egypt\'';
	$title = 'EGYPT: Infected: {7}, recovered: {8}, deaths: {9}';
	$allDay = true;
	$startDateField = 'date';
	$startTimeField = '';
	$endDateField = '';
	$endTimeField = '';
	$pk = getPKFieldName($table);
	// end of event config
	
	/* return this on error */
	$nothing = json_encode(array());

	/* check access */
	$from = get_sql_from($table);
	if(!$from) { // no permission to access that table
		@header('HTTP/1.0 403 Forbidden');
		exit($nothing);
	}

	$date_handler = function($dt) {
		$dto = DateTime::createFromFormat(DateTime::ISO8601, $dt);
		if($dto === false) return false;

		return date('Y-m-d H:i:s', $dto->format('U'));
	};

	$start = $date_handler($_REQUEST['start']);
	$end = $date_handler($_REQUEST['end']);
	if(!$start || !$end) exit($nothing);

	$events = array();
	$fields = get_sql_fields($table);

	/* 
	 * Build event start/end conditions:
	 * if event is configured with both a startDateField and endDateField,
	 *    get events where startDateField < end and endDateField > start and startDateField <= endDateField
	 * if event is configured with only a startDateField (default),
	 *    get events where startDateField < end and startDateField >= start

	 * Here, we apply date conditions only and ignore time.
	 * The reason is that the minimum interval for fullcalendar is 1 day.
	 * So, there is no need to build time filters using time fields.
	 */
	$eventDatesWhere = "`{$table}`.`{$startDateField}` >= '{$start}' AND 
	                    `{$table}`.`{$startDateField}` < '{$end}'";
	
	if($endDateField) $eventDatesWhere = "`{$table}`.`{$endDateField}` >= '{$start}' AND
	                                      `{$table}`.`{$startDateField}` < '{$end}' AND
	                                      `{$table}`.`{$startDateField}` <= `{$table}`.`{$endDateField}`";

	$eo = array('silentErrors' => true);
	$res = sql(
		"SELECT {$fields} FROM {$from} AND 
			`{$table}`.`{$startDateField}` >= '{$start}' AND 
			({$eventDatesWhere}) AND
			({$customWhere})", $eo
		);

	while($row = db_fetch_array($res)) {
		// preparing event title variables
		$replace = array();
		foreach($row as $key => $value)
			if(is_numeric($key)) $replace['{' . ($key + 1) . '}'] = $value;
		$currentTitle = str_replace(array_keys($replace), array_values($replace), $title);

		$events[] = array(
			'id' => $row[$pk],
			'url' => PREPEND_PATH . $table . '_view.php?Embedded=1&SelectedID=' . urlencode($row[$pk]),

			/*
				if a function named 'calendar_event_title' is defined
				(in hooks/__global.php for example), it will be called instead of using the title
				defined through the plugin. This is useful if you want to modify/append the
				default title defined for this event type based on some criteria in the data. For
				example to add some icon or extra info if specific criteria are met

				The calendar_event_title() function should:
					1. Accept the following parameters:
						(string) event_type (set to current event type)
						(string) title (set to the default event title)
						(associative array) event_data (contains the event data as retrieved from this event's table)

					2. Return a string containing the new/modified title to apply to the event, HTML is allowed
			*/
			'title' => (
				function_exists('calendar_event_title') ? 
					call_user_func_array('calendar_event_title', array($type, $currentTitle, $row)) :
					$currentTitle
			),

			/*
				if a function named 'calendar_event_classes' is defined
				(in hooks/__global.php for example), it will be called instead of using the color classes
				defined through the plugin. This is useful if you want to apply CSS classes other than the
				default ones defined for this event type based on some criteria in the data.

				The calendar_event_classes() function should:
					1. Accept the following parameters:
						(string) event_type (set to current event type)
						(string) classes (set to the default classes)
						(associative array) event_data (contains the event data as retrieved from this event's table)

					2. Return a string containing CSS class names (space-separated) to apply to the event.
			*/
			'classNames' => (
				function_exists('calendar_event_classes') ? 
					call_user_func_array('calendar_event_classes', array($type, $defaultClasses, $row)) :
					$defaultClasses
			),
		);

		$lastEvent = &$events[count($events) - 1];

		// convert formatted start and end dates to ISO
		$lastEvent['start'] = iso_datetime($row[$startDateField]);
		$lastEvent['end'  ] = $endDateField ? iso_datetime($row[$endDateField]) : $lastEvent['start'];

		if($allDay) {
			// no start/end time
			$lastEvent['start'] = date_only($lastEvent['start']); 
			$lastEvent['end'  ] = date_only($lastEvent['end'  ]);
			continue;
		}

		if($startTimeField)
			$lastEvent['start'] = iso_datetime(
				// take only the app-formatted date part of startDateField (in case it's a datetime)
				date_only($row[$startDateField]) . 
				// append a space then fomratted startTimeField
				' ' . $row[$startTimeField]
			);

		if($endTimeField)
			$lastEvent['end'] = iso_datetime(
				// take only the app-formatted date part of endDateField (in case it's a datetime)
				date_only($endDateField ? $row[$endDateField] : $row[$startDateField]) . 
				// and append a space then fomratted endTimeField
				' ' . $row[$endTimeField]
			);
	}

	echo json_encode($events);

	function date_only($dt) { return substr($dt, 0, 10); }

	function iso_datetime($dt) {
		// if date already in the format yyyy-mm-dd? do nothing
		if(preg_match('/^[0-9]{4}-/', $dt)) return $dt;

		// convert app-formatted date to iso (mysql)
		return mysql_datetime($dt);
	}
