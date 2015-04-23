<?php
namespace Agent\Controller;
use Think\Controller;
/**
 * 退改签政策配置
 * php导入excle文件
 * 创建者：董发勇
 * 创建时间：2014-12-22上午10:02:31
 *
 *
 */
class FlightRefundExcleController extends Controller {
	//选择导入文件
	public function index(){
		C('LAYOUT_ON',FALSE);
		$this->theme('agent')->display('excle');
	}
	
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
        for ($row = 3; $row <= $highestRow; $row++) { 
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
             $savePath = 'Uploads/xls/';
            /*以时间来命名上传的文件*/
             $str = date ( 'Ymdhis' ).'m'.LI('userId'); 
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
          $res = $this->read ( $savePath . $file_name );
		  
			$datafilename['filename']=$str;
			$datafilename['o_filename']=$filewenjianname;
			$datafilename['u_id']=LI('userId');
			$datafilename['time']=date("Y-m-d H:i:s" ,time());
			$request=M( 'upload_filename' )->add($datafilename);
           /*
        
                重要代码 解决Thinkphp M、D方法不能调用的问题   
        
                如果在thinkphp中遇到M 、D方法失效时就加入下面一句代码
           */
          foreach ($res as $key=>$val){
        	if($val[7]!=""){
        		$res[$key]['name']=1;
        	}else{
        		$res[$key]['name']=0;
        	}
        }
        }
        
        $Flight= new \Agent\Controller\ProductController();
 		$Flight->config_flight_refunds($res,$request);
 		
    }
}
    


