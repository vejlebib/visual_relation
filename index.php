<?php
	
	require('ting.class.php');
	
	$id = htmlspecialchars(isset($_REQUEST['id']) ? $_REQUEST['id'] : '870970-basis:26917921');
	
	if (isset($_REQUEST['callback'])) {
		
		$callback = htmlspecialchars($_REQUEST['callback']);
		
		$object = ting::get_object($id);
		
		$property_set = [
			'title' => 'dc:title',
			'creator' => 'dc:creator+dkdcplus:aut',
			'subject' => 'dc:subject+dkdcplus:DBCS',
		];
		
		$properties = ['{"property":"id","value":' . json_encode($id) . '}'];
		
		foreach ($property_set as $property_key => $record_key) {
			
			if (isset($object[$record_key])) {
				
				foreach ($object[$record_key] as $value) {
					$properties[] = '{"property":"' . $property_key . '","value":' . json_encode($value) . '}';
				}
			}
		}
		
		exit($callback . '([' . implode(',', $properties) . ']);');
	}
	
	exit(trim('
		<html>
			<head>
				<meta charset="utf-8" />
			</head>
			<body>
				<button data-relvis-id="' . $id . '" class="relvis-request button" data-relvis-type="external">Test</button>
				<script src="http://ssl.solsort.com/visualisering-af-relationer/scripts/9619e7eb.main.js"></script>
				<script src="http://ssl.solsort.com/visualisering-af-relationer/scripts/ce898ec3.vendor.js"></script>
				<script>
					$(function(){
						relvis.init({
							apiUrl: "http' . ($_SERVER['SERVER_PORT'] == '443' ? 's' : '') . '://' . trim($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], '/') . '",
						});
					});
				</script>
			</body>
		</html>
	'));
