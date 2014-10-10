<?php
	
class ting {
	
	private static $OPENSEARCH_URL = 'http://opensearch.addi.dk/3.0/';
	private static $AGENCY = '';
	private static $PROFILE = '';
	private static $ECHO_XML = false;
	
	/**
	* Returns the result of an opensearch-request as a simplexml element
	*/
	private static function request($body) {
		
		$conn = curl_init();
		curl_setopt($conn, CURLOPT_URL, self::$OPENSEARCH_URL);
		curl_setopt($conn, CURLOPT_TIMEOUT, 10);
		curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($conn, CURLOPT_LOW_SPEED_LIMIT, 1024);
		curl_setopt($conn, CURLOPT_LOW_SPEED_TIME, 10);
		curl_setopt($conn, CURLOPT_HTTPHEADER, ['Content-Type: text/xml;charset=UTF-8']);
		curl_setopt($conn, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($conn, CURLOPT_POSTFIELDS, trim('
			<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://oss.dbc.dk/ns/opensearch">
			  <SOAP-ENV:Body>' . trim($body) . '</SOAP-ENV:Body>
			</SOAP-ENV:Envelope>
		'));
		if (false === $response = curl_exec($conn)) {
			return false;
		}
		curl_close($conn);
		// Remove the default xml-namespace, so simplexml doesn't require its prefix:
		$response = str_replace('xmlns=', 'ns=', $response);
		if (self::$ECHO_XML) {
			echo($response);
		}
		$xml = simplexml_load_string($response);
		return $xml->children('SOAP-ENV', true)->Body;
	}
	
	/**
	* Returns a opensearch-record as a simplexml element
	*/
	private static function get_record($id) {
		
		if (!$xml = self::request('
			<getObjectRequest>
				<agency>' . self::$AGENCY . '</agency>
				<profile>' . self::$PROFILE . '</profile>
				<identifier>' . $id . '</identifier>
			</getObjectRequest>
		')) return false;
		
		
		$xml->registerXPathNamespace('dkabm', 'http://biblstandard.dk/abm/namespace/dkabm/');
		if (false === $record = current($xml->xpath('searchResponse/result/searchResult/collection/object/dkabm:record'))) {
			return false;
		}
		
		return $record;
	}
	
	/**
	* Returns a opensearch-record as an array
	*/
	public static function get_record_as_array($id) {
		
		if (false === $record = self::get_record($id)) {
			return false;
		}
		
		$recordArray = array();
		$namespaces = $record->getNamespaces(true);
		foreach ($namespaces as $prefix => $namespace) {
			$children = $record->children($namespace);
			foreach ($children as $child) {
				$type = $child->attributes('xsi', true)->type;
				$recordArray[$prefix . ':' . $child->getName() . ($type ? '+' . $type : '')][] = strval($child);
			}
		}
		
		return $recordArray;
	}
	
}
