<?php
namespace System;

class UserRight {
	//功能的ID，好像没啥用呢，考虑去掉。
	public $funcId = NULL;
	//模块
	public $sModule = NULL;
	//控制器
	public $sController = NULL;
	//动作 
	public $sAction = NULL;
	//是否具有读的权限，功能进入权限等同于读权限
	public $canRead = FALSE;
	//是否具有写的权限，一般用于安排“增/删/改”等功能按钮或之类
	public $canWrite = FALSE;
	//是否具有导入的权限，比如是否能从Excel导入数据
	public $canImport = FALSE;
	//是否具有导出的权限
	public $canExport = FALSE;
	//是否具有打印的权限
	public $canPrint = FALSE;
	//扩展权限
	public $otherRights = NULL;
		
}