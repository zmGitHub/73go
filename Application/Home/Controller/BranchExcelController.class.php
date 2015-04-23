<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 部门
 * php导入excle文件
 * 创建者：董发勇
 * 创建时间：2014-12-22上午10:02:31
 *
 *
 */
class BranchExcelController extends Controller {
	//选择导入文件
	public function read($filename,$encode='utf-8'){
		vendor('Classes.PHPExcel');
        $objReader =\PHPExcel_IOFactory::createReader(Excel5); 
        $objReader->setReadDataOnly(true); 
        $objPHPExcel = $objReader->load($filename); 
        $objWorksheet = $objPHPExcel->getActiveSheet(); 
        $highestRow = $objWorksheet->getHighestRow(); 
        $highestColumn = $objWorksheet->getHighestColumn(); 
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn); 
        $excelData = array(); 
        for ($row = 2; $row <= $highestRow; $row++) { 
            for ($col = 0; $col < $highestColumnIndex; $col++) { 
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            } 
        } 
        return $excelData;    
    } 
    public function excel(){
        $this->display();    
    }
    public function runexcel(){
    	header("Content-Type:text/html;charset=utf-8");
        if (! empty ( $_FILES ['file_stu'] ['name'] )) 
         {
         	$filewenjianname=$_FILES ['file_stu'] ['name'];
            $tmp_file = $_FILES ['file_stu'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['file_stu'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];
             /*判别是不是.xls文件，判别是不是excel文件*/
             if (strtolower ( $file_type ) != "xls")              
            {
                  $this->error ( '不是Excel文件，重新上传' );
             }
            /*设置上传路径*/
             $savePath = '../';
            /*以时间来命名上传的文件*/
             $str = date ( 'Ymdhis' ); 
             $file_name = $str . "." . $file_type;
             /*是否上传成功*/
             if (! copy ( $tmp_file, $savePath . $file_name )) 
              {
                  $this->error ( '上传失败' );
              }
            /*
        
               *对上传的Excel数据进行处理生成编程数据,这个函数会在下面第三步的ExcelToArray类中
        
              注意：这里调用执行了第三步类里面的read函数，把Excel转化为数组并返回给$res,再进行数据库写入
        
            */
          $res = $this->read ($savePath . $file_name );
          //对导入数据预处理操作
          foreach($res  as $key=>$val){
          		if($val[0]!="" && $val[1]!="" && $val[2]!="" && $val[3]!="" && $val[4]!="" ){
          			$res[$key]['name']=1;
          		}else if($val[4]=="" && $val[3]!="" && $val[2]!="" && $val[1]!="" && $val[0]!=""){
          			$res[$key]['name']=1;
          		}else if($val[4]=="" && $val[3]=="" && $val[2]!="" && $val[1]!="" && $val[0]!=""){
          			$res[$key]['name']=1;
          		}else if($val[4]=="" && $val[3]=="" && $val[2]=="" && $val[1]!="" && $val[0]!=""){
          			$res[$key]['name']=1;
          		}else if($val[4]=="" && $val[3]=="" && $val[2]=="" && $val[1]=="" && $val[0]!=""){
          			$res[$key]['name']=1;
          		}else{
          			$res[$key]['name']=0;
          		}
          }
           /*
                重要代码 解决Thinkphp M、D方法不能调用的问题   
        
                如果在thinkphp中遇到M 、D方法失效时就加入下面一句代码
           */	
         }     
        $Department= new \Home\Controller\DepartmentStaffController();
 		$Department->ExeclDepartments($res);
 		
    }
}


