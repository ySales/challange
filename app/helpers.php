<?php

namespace App;

function quick_sort($array)
{
	$length = count($array);
	
	if($length <= 1){
		return $array;
	}
	else{
	
		$pivot = $array[0];
		
		$left = $right = array();
		
		for($i = 1; $i < count($array); $i++)
		{
			if($array[$i] < $pivot){
				$left[] = $array[$i];
			}
			else{
				$right[] = $array[$i];
			}
		}
		
		return array_merge(quick_sort($left), array($pivot), quick_sort($right));
	}
}