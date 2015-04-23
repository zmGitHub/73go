<?php
namespace Common\Datasource;

/**
 * Class Datasource
 * 数据源服务类。
 * 主要方法：
 * 		getSQL(数据源名称, 参数)
 * 		execDS(数据源名称, 参数)
 * 		getData(数据源名称, 参数)
 *
 * getSQL:
 * 	可使用一个名称，配合给入的参数，获取组装好的SQL语句。
 * 	例子：
 * 		$sql = DataSource::getSQL('User/user', $_POST);
 * 		$model = M();
 *		$model->query($sql);
 *
 * 		例子中，数据源名称为'User/user'，这个字符串的格式是带目录的，意思是从
 * 	{$DS_Template_Path}（数据源的模板根目录）的User目录中获得user.tpl模板，采
 *  用从前端POST过来的参数，组装成所需的SQL语句。
 *
 *	上面的例子，相当于执行了execDS方法。
 *  getData方法是execDS方法的封装。
 *
 * @package Common\Datasource
 * @author Lanny Lee
 * @history
 * 2014-10-??		creation, by Lanny Lee
 * 2015-1-12		考虑绝大部分的使用场景的雷同性，增加了execDS和getData方法,by Lanny Lee
 */
class Datasource {
	
	/**
	 * 数据源公共变量，这些变量可以使用在数据SQL语句的解析生成中，比如
	 * SELECT * FROM {$_table_prefix}user
	 * 
	 * TODO: 在模板解析中需要的更多公共变量
	 */
	static $global_ds_define = array (
		'_table_prefix' => '73go_',
		'_sysdate' => 'sysdate()'
			
	);
	
	public static function getSQL($DatasourceID, $params) {
		$dsID = $DatasourceID;
		/**
		 * TODO: 后续的处理，这里的模板路径应从配置文件读取
		 * */
		//数据源模板根目录
		$tmpPath = APP_PATH.'Common/SqlTemplate';

		//创建Smarty实例
        vendor('Smarty.Smarty#class');
		$smarty = new \Smarty();
		
		//检查数据源名称中是否存在目录格式
		$p = strripos($dsID, '/');
		if ($p > -1) {
			//若存在目录格式，则调整$tmpPath到对应的目录中
			$tmpPath = $tmpPath.'/'.substr($dsID, 0, $p);
			//$dsID必须是不带目录的文件名(无后缀名)
			$dsID = substr($dsID, $p + 1);
		}
		//开始准备模板环境
		$smarty->template_dir = $tmpPath;
		$smarty->assign($params);                       //装入传入的参数
		$smarty->assign(Datasource::$global_ds_define); //装入公共变量
		//指定模板名称
		$templatefile = $dsID.'.tpl';
		//进行解析，获得结果
		return $smarty->fetch($templatefile);
	}

	/**
	 * 以传入的参数执行数据源。
	 * 此方法可用于执行语句，或获得数据。
	 * @param $dataSourceID
	 * @param $params
	 * @return mixed
	 */
	public static function execDS($dataSourceID, $params) {
		$m = M();
		return $m->query(Datasource::getSQL($dataSourceID, $params));
	}

	/**
	 * 以传入的参数执行数据源，获得数据。
	 * @param $dataSourceID
	 * @param $params
	 * @return mixed
	 */
	public static function getData($dataSourceID, $params) {
		//此方法目前其实就是执行execDS方法，仅为了源代码的可阅读性而设置此名称之方法。
		//TODO 后续可能会扩展与execDS不同的特性？
		return Datasource::execDS($dataSourceID, $params);
	}
}