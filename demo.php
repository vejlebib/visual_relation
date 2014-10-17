<?php
	
	require('ting.class.php');
	
	$id = '870970-basis:26917921';
	
	if (isset($_REQUEST['callback'])) {
		
		$callback = htmlspecialchars($_REQUEST['callback']);
		
		$record = ting::get_record_as_array($id);
		
		$property_set = [
			'title' => 'dc:title',
			'creator' => 'dc:creator+dkdcplus:aut',
			'subject' => 'dc:subject+dkdcplus:DBCS',
		];
		
		$properties = ['{"property":"id","value":' . json_encode($id) . '}'];
		
		foreach ($property_set as $property_key => $record_key) {
			
			if (isset($record[$record_key])) {
				
				foreach ($record[$record_key] as $value) {
					$properties[] = '{"property":"' . $property_key . '","value":' . json_encode($value) . '}';
				}
			}
		}
		
		exit($callback . '([' . implode(',', $properties) . ']);');
	}
