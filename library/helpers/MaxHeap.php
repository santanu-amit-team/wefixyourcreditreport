<?php 

namespace Application\Helper;

use SplMaxHeap;

class MaxHeap extends SplMaxHeap
{
	protected function compare($value1, $value2){
		return (
			(int)$value1[1] - (int)$value2[1]
		);
	}
}