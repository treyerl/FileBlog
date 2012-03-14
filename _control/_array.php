<?php /* Copyright (c) 2012 Lukas Treyer; MIT-License http://www.opensource.org/licenses/mit-license.php */

/* array_umerge($array1, callback $callback, $array2 [, $array3, ...] )*/
function array_umerge($array1, $callback, $array2){
	// return the rest of an array from a certain index
	$arraysToBeMapped = array_splice(func_get_args(),2,func_num_args());
	
	if (!is_array($array1)) throw new Exception('array_umerge() : parameter 1 is no array but '.var_export($array1, true));
	if (!is_array($array2)) throw new Exception('array_umerge() : parameter 2 is no array but '.var_export($array2, true));
	
	foreach ($array1 as $key => &$value){
		foreach($arraysToBeMapped as &$maparray){
			if (array_key_exists($key, $maparray)){
				$value = $callback($value, $maparray[$key]);
				unset($maparray[$key]);
			}
		}
	}
	foreach($arraysToBeMapped as $maparray)	$array1 = array_merge($array1,$maparray);
	return $array1;
}

//test arrays
//$italiano = array('eins' => 'uno','zwei'=>'due','drei'=>'tre');
//$english  = array('eins' => 'one','drei'=>'three');
//$francais = array('drei' => 'troi');

function updateWith($ToBeReplaced, $replacement){
	if ($replacement != '') return $replacement;	
	else return $ToBeReplaced;
}

function recursiveUpdateWith($ToBeReplaced, $replacement){
	if ($replacement != '') {
		if (is_array($ToBeReplaced) && is_array($replacement)) {
			return array_umerge($ToBeReplaced, 'recursiveUpdateWith', $replacement);
		} else {
			return $replacement;	
		}
	} else {
		return $ToBeReplaced;
	}
}

function is_array_accessible($array){
	if (is_array($array)) { return true;}
	elseif (is_object($array)) {
		$classname = get_class($array);
		$class = new ReflectionClass($classname);
		if ($class->implementsInterface('arrayaccess')) return true;
	} else {return false;}
}

?>