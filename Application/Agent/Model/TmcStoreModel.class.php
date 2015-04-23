<?php

namespace Agent\Model;

use Think\Exception;
use Think\Model;

/**
 * 描述：旗舰店主页项目与73go网站对接功能类
 * @author 王月
 * @date 2015-02-02
 * 版本信息：V1.0	王月	2015-02-02	
 *
 */
class TmcStoreModel extends Model
{

	/**
	 * 描述：判断tmc是否已经创建旗舰店，如果创建返回true，为创建返回false
	 * @author 王月
	 * @date 2015-02-02
	 * 版本信息：V1.0    王月    2015-02-02
	 *
	 */
	public function whetherToCreate()
	{
		$Tmc = M("tmc");
		$tmcId = LI("tmcId");
		$code = $Tmc->where("id=" . $tmcId)->getField('tmc_code');

		// 获取TMC配置id
		$tmc_config = M('tmc_siteconfig');
		$data = $tmc_config->where("tmc_id=" . $tmcId)->find();
		$tmcName = $Tmc->where("id=" . $tmcId)->getField('name');

		if (!$data) {
			$array = array(
				'code' => $code,
				'tmcName' => $tmcName,
				'result' => false);
			return $array;
		} else {
			$reg_agreement = $data['reg_agreement'];
			$sub_url = $data['sub_url'];
			$array = array(
				'code' => $code,
				'tmcName' => $tmcName,
				'reg_agreement' => $reg_agreement,
				'sub_url' => $sub_url,
				'result' => true);
			return $array;
		}
	}

	/**
	 * 描述：tmc创建旗舰店，在旗舰店配置表里面添加数据
	 * @author 王月
	 * @date 2015-02-02
	 * 版本信息：V1.0    王月    2015-02-02
	 *
	 */
	public function create()
	{
		$tmc_config = M('tmc_siteconfig');
		$Tmc = M("tmc");
		$tmcId = LI("tmcId");

		$tmc_code = $Tmc->where("id=" . $tmcId)->getField('tmc_code');
		// 数据库添加
		$store ['sub_url'] = "http://" . C('TMC-HOSTING-SERVER') . "/" . $tmc_code;
		$store ['tmc_id'] = $tmcId;
		$store ['reg_agreement'] = $_POST['reg_agreement'];
		$tmc_config->data($store)->add();
	}

}

