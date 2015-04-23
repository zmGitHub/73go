<?php
namespace Home\Controller;
use Think\Controller;
class TmcController extends Controller {
	
	/**
	 * 图片全部自动加载
	 *
	 * @param $logo 入口地址
	 */
	public function _empty($param) {
		$FUrl = $_SERVER ['HTTP_REFERER'];
		//字符串处理 取url最后个 / 后面的值
		$code = currTMCNum();
		// 获取config配置的本地根目录
		$path = C ( "TMC-IMAGE-PATH" );
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = M('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = M('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		
		$map['store_id']=$storeId;
		if($param == "small_logo"){   //头部logo
			$tmc_store_head_photo = M('tmc_store_head_photo');
			$imgPath=$tmc_store_head_photo -> where($map)->getField('path');
		}
		elseif(($param == "lunbo1") or ($param == "lunbo2") or ($param == "lunbo3")){  //轮播图
			$tmc_store_lunbo_photo = M('tmc_store_lunbo_photo');
		    switch($param)
			{
				case 'lunbo1':
					$imgPath = $tmc_store_lunbo_photo-> where($map)->getField('path_first');
					break;
				case 'lunbo2':
					$imgPath = $tmc_store_lunbo_photo-> where($map)->getField('path_second');
					break;
				case 'lunbo3':
					$imgPath = $tmc_store_lunbo_photo-> where($map)->getField('path_third');
					break;
				default: break;
			}
		}
		//产品图
		elseif(($param == "img1") or ($param == "img2") or ($param == "img3") or ($param == "img4") or ($param == "img5") or ($param == "img6")){
			$tmc_store_product_photo = M('tmc_store_product_photo');
		    switch($param)
			{
				case 'img1':			
					$imgPath = $tmc_store_product_photo-> where($map)->getField('first_path');
					break;
				case 'img2':
					$imgPath = $tmc_store_product_photo-> where($map)->getField('second_path');
					break;
				case 'img3':
					$imgPath = $tmc_store_product_photo-> where($map)->getField('third_path');
					break;
				case 'img4':
					$imgPath = $tmc_store_product_photo-> where($map)->getField('fourth_path');
					break;
				case 'img5':
					$imgPath = $tmc_store_product_photo-> where($map)->getField('fifth_path');
					break;
				case 'img6':
					$imgPath = $tmc_store_product_photo-> where($map)->getField('sixth_path');
					break;
				default: break;
			}
		}
		$imgFile = "{$path}{$imgPath}";
		ob_clean();
		if (file_exists($imgFile)) {
			header('Content-Type: image/jpeg');
			header("Content-Length:".filesize($imgFile));
			//header('Content-Description: File Transfer');
			//header('Content-Disposition: attachment; filename='.basename($imgFile));
			header('Expires: 0');
			//header('Cache-Control: must-revalidate');
			//header('Pragma: public');
			readfile($imgFile);   // 拼接成本地地址
			flush();
			exit;

		}
		//echo "{$path}{$imgPath}";
		exit;
	}
	
	//页面全部文字自动更新__ing...
	public function updata() {
		header ( "Content-Type:text/html; charset=utf-8" );
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		//字符串处理 取url最后个 / 后面的值
		$code = currTMCNum();
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		// 获取config配置的本地根目录
		$path = C ( "TMC-IMAGE-PATH" );
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = M('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = M('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$data = array();
		
		//获取当前登录用户的ID信息
		$userId = LI('userId');
		$userType = LI('userType');
		
		$tmc_store_foot = M('tmc_store_foot');
		$tmc_store_introduction = M('tmc_store_introduction');
		$tmc_store_lunbo_word = M('tmc_store_lunbo_word');
		$tmc_store_product_word = M('tmc_store_product_word');
		$tmc_store_web_word = M('tmc_store_web_word');
		$tmc_store_window = M('tmc_store_window');
		
		//将当前登录用户的ID信息返回到主页
		if($userType == '3'){                       //判断是否为当前旗舰店的TMC用户
			$tmcCode = $tmc->where('u_id = '.$userId)->getField('tmc_code');
			if($tmcCode == $code){
				$data['showEdit'] = 1;
			}else{
				$data['showEdit'] = 0;
			}
		}else{
			$data['showEdit'] = 0;
		}
		
		$data['foot'] = $tmc_store_foot-> where($map)->select();
		$data['introduction'] = $tmc_store_introduction-> where($map)->select();
		$data['lunbo_word'] = $tmc_store_lunbo_word-> where($map)->select();
		$data['product_word'] = $tmc_store_product_word-> where($map)->select();
		$data['web_word'] = $tmc_store_web_word-> where($map)->select();
		$data['window'] = $tmc_store_window-> where($map)->select();
		$this->ajaxReturn ( $data, 'JSON' );
		
	}
	
	//3个轮播图的图片修改
	public function updateLunbo() {
		header ( "Content-Type:jpg/jpeg; charset=utf-8" );
		$k = $_POST['k']['k'];
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		//字符串处理 取url最后个 / 后面的值
		$code = currTMCNum();
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		// 获取config配置的本地根目录
		$path = C ( "TMC-IMAGE-PATH" );
		
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = M('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = M('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$tmc_store_lunbo_word = M('tmc_store_lunbo_word');
		
			$lunbo_word = $_POST['Lunbo']['lunbo_word'.$k];
			$lunbo_url = $_POST['Lunbo']['lunbo_url'.$k];
			//将*amp*、*equal*分别替换成&和=
			$lunbo_url = str_replace("*amp*","&",$lunbo_url);
			$lunbo_url = str_replace("*equal*","=",$lunbo_url);
			$data['lunbo_word'.$k] = $lunbo_word;
			$data['lunbo_url'.$k] = $lunbo_url;
			$result =$tmc_store_lunbo_word->where($map)->save($data);
		$this->ajaxReturn($result,"JSON");
	}
	
    //六个产品  文字更新
	public function updateProductWord() {
		header ( "Content-Type:text/html; charset=utf-8" );
		$k = $_POST['k']['k'];
		$code = currTMCNum();
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		$path = C ( "TMC-IMAGE-PATH" );
		
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = D('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig =D('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$tmc_store_product_word = D('tmc_store_product_word');
		
			$big_word =$_POST['Product']['big_word'.$k];
			$small_word = $_POST['Product']['small_word'.$k];
			$product_url = $_POST['Product']['product_url'.$k];
			//将*amp*、*equal*分别替换成&和=
			$product_url = str_replace("*amp*","&",$product_url);
			$product_url = str_replace("*equal*","=",$product_url);
			
			$product_price = $_POST['Product']['product_price'.$k];
			$product_sale = $_POST['Product']['product_sale'.$k];
			$data['big_word'.$k] = $big_word;
			$data['small_word'.$k] = $small_word;
			$data['product_url'.$k] = $product_url;
			$data['product_price'.$k] = $product_price;
			$data['product_sale'.$k] = $product_sale;
			$tmc_store_product_word->where($map)->save($data);
			
			$result =$tmc_store_product_word->where($map)->limit(1)->select();
			$this->ajaxReturn($result,"JSON");
	}

	//TMC公司宣传	 专业差旅 专业服务  一分钟预定
	public function updateInfo(){
		header ( "Content-Type:text/html; charset=utf-8" );
		$k = $_POST['k']['k'];
		$code = currTMCNum();
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		//字符串处理 取url最后个 / 后面的值
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		$path = C ( "TMC-IMAGE-PATH" );
		
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = D('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = D('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$tmc_store_introduction = D('tmc_store_introduction');
		
			$title =$_POST['Introduction']['title'.$k];
			$body = $_POST['Introduction']['body'.$k];
			$url = $_POST['Introduction']['url'.$k];
			//将*amp*、*equal*分别替换成&和=
			$url = str_replace("*amp*","&",$url);
			$url = str_replace("*equal*","=",$url);
			
			$data['title'.$k] = $title;
			$data['body'.$k] = $body;
			$data['url'.$k] = $url;
			$result =$tmc_store_introduction->where($map)->save($data);
			$this->ajaxReturn($result,"JSON");
	}
	
    // 关于我们 招聘信息 联系我们 合作伙伴  版权所有 
	public function updateFoot(){
		header ( "Content-Type:text/html; charset=utf-8" );
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		$code = currTMCNum();
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		// 获取config配置的本地根目录
		$path = C ( "TMC-IMAGE-PATH" );
		
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = D('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = D('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$tmc_store_foot =D('tmc_store_foot');
		$aboutUrl = $_POST['about_url'];
		$recUrl = $_POST['rec_url'];
		$contUrl = $_POST['cont_url'];
		$partUrl = $_POST['part_url'];
		$aboutUrl = str_replace("*amp*","&",$aboutUrl);
		$aboutUrl = str_replace("*equal*","=",$aboutUrl);
		$recUrl = str_replace("*amp*","&",$recUrl);
		$recUrl = str_replace("*equal*","=",$recUrl);
		$contUrl = str_replace("*amp*","&",$contUrl);
		$contUrl = str_replace("*equal*","=",$contUrl);
		$partUrl = str_replace("*amp*","&",$partUrl);
		$partUrl = str_replace("*equal*","=",$partUrl);
		$data['about_url'] = $aboutUrl;
		$data['rec_url'] = $recUrl;
		$data['cont_url'] = $contUrl;
		$data['part_url'] = $partUrl;
		$data['abmoutme'] = $_POST['abmoutme'];
		$data['recuitmeninfo'] = $_POST['recuitmeninfo'];
		$data['contactus'] = $_POST['contactus'];
		$data['partners'] = $_POST['partners'];
		$result =$tmc_store_foot->where($map)->save($data);
		$this->ajaxReturn($result,"JSON");
	}
	
    //Web Word 特价日
	public function updateBigTitle(){
		header ( "Content-Type:text/html; charset=utf-8" );
		$code = currTMCNum();
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		//字符串处理 取url最后个 / 后面的值
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		// 获取config配置的本地根目录
		$path = C ( "TMC-IMAGE-PATH" );
		
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = D('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = D('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$tmc_store_web_word = D('tmc_store_web_word');
		
		
			$word_key =$_POST['word_key'];
			$word_value = $_POST['word_value'];
			$data['word_key'] = $word_key;
			$data['word_value'] = $word_value;
			$result =$tmc_store_web_word->where($map)->save($data);
		$this->ajaxReturn($result,"JSON");
	}
	
    //窗口颜色
	public function updateWindow(){
		header ( "Content-Type:text/html; charset=utf-8" );
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		//字符串处理 取url最后个 / 后面的值
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		$code = currTMCNum();
		// 获取config配置的本地根目录
		$path = C ( "TMC-IMAGE-PATH" );
		
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = D('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = D('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$tmc_store_window = D('tmc_store_window');
		
			$color =$_POST['color'];
			$show_tag = $_POST['show_tag'];
			$data['color'] = $color;
			$data['show_tag'] = $show_tag;
			$result =$tmc_store_window->where($map)->save($data);
		$this->ajaxReturn($result,"JSON");
	}
	
	//TMC公司联系方式  右弹窗
	public function updateConnection(){
		header ( "Content-Type:text/html; charset=utf-8" );
		$code = currTMCNum();
		//$FUrl = $_SERVER ['HTTP_REFERER'];
		//字符串处理 取url最后个 / 后面的值
		//$code = substr ( $FUrl, strrpos ( $FUrl, '?' ) + 1 );
		// 获取config配置的本地根目录
		$path = C ( "TMC-IMAGE-PATH" );
		
		//由TMC编号获得TMC的id，再获得store_id
		$tmc = D('tmc');
		$tmcId = $tmc->where('tmc_code = '.$code)->getField('id');
		$tmc_siteconfig = D('tmc_siteconfig');
		$storeId = $tmc_siteconfig->where('tmc_id = '.$tmcId)->getField('id');
		$map['store_id']=$storeId;
		$tmc_store_connection = D('tmc_store_connection');
		
		
			$phone =$_POST['phone'];
			$qq = $_POST['qq'];
			$sina_url = $_POST['sina_url'];
			$weixin_num = $_POST['weixin_num'];
			$weixin_code = $_POST['weixin_code'];
			$service_time = $_POST['service_time'];
			$data['phone'] = $phone;
			$data['qq'] = $qq;
			$data['sina_url'] = $sina_url;
			$data['weixin_num'] = $weixin_num;
			$data['weixin_code'] = $weixin_code;
			$data['service_time'] = $service_time;
			$result =$tmc_store_connection->where($map)->save($data);
		$this->ajaxReturn($result,"JSON");
	}
	
	
}