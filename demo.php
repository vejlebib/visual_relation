<?php

	require('ting.class.php');
	
	exit(
		json_encode(
			ting::get_record_as_array('870970-basis%3A27190731')
		)
	);
