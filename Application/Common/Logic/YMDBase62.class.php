<?php
namespace Common\Logic;

/**
 * 用于生成单个日期码的62进制支持类。
 * @author Lanny Lee
 *
 */
use Think\Exception;

class YMDBase62 {

	/**
	 * 62进制表
	 * @var unknown_type
	 */
	private static $base62Table = array (
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',
		'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
		'U', 'V', 'W', 'X', 'Y', 'Z',
		'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
		'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
		'u', 'v', 'w', 'x', 'y', 'z',
	);

	public static function getStrOfNumber($num) {
		if ($num < 0 || $num > 61) {
			throw new Exception("只能在0到61之间取值");
		}
		return YMDBase62::$base62Table[$num];
	}

	public static function getNumberOfStr($c) {
		for ($i = 0; $i < 62; $i++) {
			if (YMDBase62::$base62Table[$i] === $c)
				return $i;
		}
		//不是正确的编码
		return -1;
	}
	
}