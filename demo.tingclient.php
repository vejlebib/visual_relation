<?php
	
	// Demo, using github.com/ding2tal/ting-client for the VR-API
	
	// The following are adjusted functions from https://github.com/ding2tal/ting/blob/development/ting.entities.inc
	// I don't use the entire class, as it depends on the DingEntity drupal module
	
	function getTitle($object) {
		$title = FALSE;
		if (!empty($object->record['dc:title'])) {
			if (isset($object->record['dc:title']['dkdcplus:full'])) {
				$title = $object->record['dc:title']['dkdcplus:full'][0];
			}
			else {
				$title = $object->record['dc:title'][''][0];
			}
		}
		return $title;
	}
	
	function getCreators($object) {
		$creators = array();
		if (!empty($object->record['dc:creator'])) {
			foreach ($object->record['dc:creator'] as $type => $dc_creator) {
				if ($type != 'oss:sort') {
					$creators = array_merge($creators, $dc_creator);
				}
			}
		}
		return $creators;
	}
	
	function getSubjects($object) {
		$subjects = array();
		if (!empty($object->record['dc:subject'])) {
			foreach ($object->record['dc:subject'] as $type => $dc_subject) {
				if (!in_array($type, array('dkdcplus:genre', 'dkdcplus:DK5', 'dkdcplus:DK5-Text', 'dkdcplus:DBCO', 'dkdcplus:DBCN'))) {
					$subjects = array_merge($subjects, $dc_subject);
				}
			}
		}
		return $subjects;
	}
	
	function getRecordType($object) {
		return !empty($object->record['dc:type']['dkdcplus:BibDK-Type'][0]) ? $object->record['dc:type']['dkdcplus:BibDK-Type'][0] : FALSE;
	}
	
	function getRelations($object) {
		$relations = array();
		if (isset($object->relationsData)) {
			foreach ($object->relationsData as $record) {
				if (isset($record->relationUri) && isset($record->relationObject)) {
					$relations[] = [$record->relationUri, $record->relationType];
				}
			}
		}
		return $relations;
	}
	
	// Include nanosoap client (drupal.org/project/nanosoap), used by ting.client
	
	require_once('ting-client/nanosoap.inc');
	
	// Autoload ting.client's classes
	
	function __autoload($class_name) {
	
		$tingclient_dirs = [
			'',
			'adapter/',
			'excpetion/',
			'log/',
			'request/',
			'result/',
			'result/infomedia/',
			'result/object/',
			'result/object/data/',
			'result/recommendation/',
			'result/scan/',
			'result/search/',
			'result/spell/',
		];
		
		foreach ($tingclient_dirs as $dir) {
			if (file_exists('ting-client/lib/' . $dir . $class_name . '.php')) {
				require_once('ting-client/lib/' . $dir . $class_name . '.php');
			}
		}
	}
	
	// Request a ting object
	
	$request = new TingClientRequestFactory([
		'search' => '',
		'scan' => '',
		'object' => 'http://opensearch.addi.dk/3.0/',
		'collection' => '',
		'spell' => '',
		'recommendation' => '',
	]);
	$request = $request->getObjectRequest();
	$request->setObjectId('870970-basis:26917921');
	$request->setAgency('');
	$request->setProfile('');
	$request->setAllRelations(FALSE);
	$request->setRelationData('full');
	
	$client = new TingClient(new TingClientRequestAdapter(), new TingClientVoidLogger());
	
	$result = $client->execute($request);
	
	$object = $request->parseResponse($result);
	
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
