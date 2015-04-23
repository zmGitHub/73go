<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 机票政策
 * php导入excle文件
 * 创建者：董发勇
 * 创建时间：2014-12-22上午10:02:31
 *
 *
 */
class DepartmentStaffExcelController extends Controller {
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
          $res = $this->read ( $savePath . $file_name );
           /*
        
                重要代码 解决Thinkphp M、D方法不能调用的问题   
        
                如果在thinkphp中遇到M 、D方法失效时就加入下面一句代码
        
            */
          // spl_autoload_register ( array ('Think', 'autoload' ) );
        
        /**
         *当数据成功转换后，首先将信息保存到 上传文件管理中
         *文件名，原始文件名，上传者，上传时间
         */
          if($res){
           /*对生成的数组进行数据库的写入*/
			
			$dictionarydata=M('');
				
          foreach ( $res as $k => $v ) 
           {
               if ($k != 0) 
              {
					 $data['emp_code'] = $v[0];//工号
					 $data['name']=$v[1];//姓名
					 $tmcbr_id=$v[2];//部门
					 $co_id=LI('comId');
					 $data['co_id']=$co_id;
					 $branch=M('');
					 $branchsql='select id from 73go_branch where `name`="'."$tmcbr_id".'" and co_id='.$co_id;
					 $branchrequest=$branch->query($branchsql);
					 $data['br_id']=$branchrequest[0]['id'];
					 
					 $data['sex'] = $v[3];//性别
					 if($data['sex'] == "男"){
					 	$data['sex']="F";
					 }
					 else{
					 	$data['sex']="N";
					 }
					 $data['phone']=$v[4];//电话
					 $data['email']=$v[5];//邮箱
					 $id_type = $v[6];//证件类型
					 
					 
					 $dictionary=M('');
					 $typesql='select id from 73go_dictionary where d_group="id_type" and d_value="'."$id_type".'"';
					 $typerequest=$dictionary->query($typesql);
					 $data['id_type']=$typerequest[0]['id'];
					 
					 $data['id_num']=$v[7];//证件号码
					 $data['province']=$v[8];//省
					 $city = $v[9];//市
					 $citysql='select id from 73go_dictionary where display_ex ="'."$city".'"';
					 $cityrequest=$dictionary->query($citysql);
					 $data['city']=$cityrequest[0]['id'];
					 
					 //$data['status']=0;//状态
					 $data['status']=1;//导入员工时是属于企业,状态为1
					 $result = M ( 'employee' )->add ( $data );
	
                 if (! $result) 
                 {
                      $this->error ( '导入数据库失败' );
                 }
                 else{
                 	  $this->success('导入数据库成功');
                 }
              }
           }
           
           }
        }
    }
}


