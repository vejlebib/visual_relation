<?php
	
	// Demo, using own tingclient for the VR-API
	
	// Functions for parsing title, creators, subjects, type, relations
	
	function getTitle($object) {
		if (isset($object['dkdcplus:full'])) {
			return current($object['dkdcplus:full']);
		}
		return current($object['dc:title']);
	}
	
	function getCreators($object) {
		$creators = array();
		if (isset($object['dc:creator+dkdcplus:aut'])) {
			foreach ($object['dc:creator+dkdcplus:aut'] as $creator) {
				$creators[] = $creator;
			}
		}
		return $creators;
	}
	
	function getSubjects($object) {
		$subjects = array();
		if (isset($object['dc:subject+dkdcplus:DBCS'])) {
			foreach ($object['dc:subject+dkdcplus:DBCS'] as $subject) {
				$subjects[] = $subject;
			}
		}
		return $subjects;
	}
	
	function getRecordType($object) {
		return isset($object['dc:type+dkdcplus:BibDK-Type']) ? current($object['dc:type+dkdcplus:BibDK-Type']) : false;
	}
	
	function getRelations($object) {
		return $object['relations'];
	}
	
	// Include my ting client
	
	require('ting.class.php');
	
	// Request a ting object
	
	$object = ting::get_object('870970-basis:26917921');
	
	// Output title, creators and subjects, type, relations
	
	function object2json($id, $title, $creators, $subjects, $type, $relations) {
		$properties = [
			'{"property":"id","value":' . json_encode($id) . '}',
			'{"property":"title","value":' . json_encode($title) . '}',
			'{"property":"type","value":' . json_encode($type) . '}',
		];
		foreach ($creators as $creator) {
			$properties[] = '{"property":"creator","value":' . json_encode($creator) . '}';
		}
		foreach ($subjects as $subject) {
			$properties[] = '{"property":"subject","value":' . json_encode($subject) . '}';
		}
		foreach ($relations as $relation) {
			$properties[] = '{"property":"subject","value":' . json_encode($relation) . '}';
		}
		return implode($properties);
	}
	
	echo(object2json(
		'870970-basis:26917921',
		getTitle($object),
		getCreators($object),
		getSubjects($object),
		getRecordType($object),
		getRelations($object)
	));
