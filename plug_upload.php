<?php
	class plug_upload{
		private $config=array(
					"server_url" => "" 							//服务器地址(ip地址或域名)
					,"root_dirName"=>"blog/"					//网站根目录名称
					,"root_dir"  => "blog/"						//网站根目录（首页地址 相对路径）
					,"file_dir"  => "upload/"					//保存文件的目录（文件夹）					  
					,"file_size" => 512000						//文件上传大小限制（默认500kb 500*1024）
					,"file_type" => array(						//文件上传类型
						"image/jpeg"							//*.jpeg
						,"image/jpg"							//*.jpg
						,"image/pjpeg"
						,"image/x-png"
						,"image/png"							//*.png
						,"image/gif"							//*.gif
						,"image/vnd.dwg"						//*.dwg
						,"image/vnd.dxf"						//*.dxf
						,"image/jp2"							//*.jp2
						/*
						,"application/pdf"						//*.pdf
						,"text/plain"							//*.txt
						,"application/msword"					//*.doc
						,"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"//*.xlsx
						,"application/vnd.ms-powerpoint"		//*.ppt
						*/
						/*
						,"aplication/zip"						//*.zip
						,"audio/3gpp","video/3gpp"				//*.3gpp
						,"audio/ac3"							//*.ac3
						,"allpication/vnd.ms-asf"				//*.asf
						,"audio/basic"							//*.au
						,"text/css"								//*.css
						,"text/csv"								//*.csv
						,"application/msword"					//*.dot
						,"application/xml-dtd"					//*.dtd
						,"application/json" 					//*.json
						,"application/vnd.ms-project"			//*.mpp
						,"application/ogg","audio/ogg"			//*.ogg
						,"application/vnd.ms-works"				//*.wps
						,"application/xhtml+xml"				//*.xhtml
						,"text/html"							//*.htm
						,"text/html"							//*.html
						,"text/xml","application/xml"			//*.xml
						,"audio/mpeg"							//*.mp3
						,"audio/mp4","video/mp4"				//*.mp4
						*/
					)
					,"newFileName"=> ""							//新文件名
					,"newFileUrl" => ""							//新文件路径（绝对路径显示）
					,"errorNum"   => 0							//错误号
					,"errorMess"  => ""							//错误报告
				);
		private $newFileUrl_arr=array();						//新文件路径（地址）集合
		function __construct()
		{
			date_default_timezone_set("Asia/Shanghai");
			$this->config["server_url"]=$_SERVER['SERVER_NAME']."/";//服务器地址(ip地址或域名)
		}
		function set_config($key,$key_value){
			$this->config[$key]=$key_value;
		}
		function get_config($key){
			return $this->config[$key];
		}
		private function st($s_,$n){//（随机）字符串拼接，配合方法：randomStr
			$str="";
			$str_len=strlen($s_)-1;//获取字符串长度-1
			for ($i=0; $i <$n ; $i++) { 
				$str.=$s_[rand(0,$str_len)];
			}
			return $str;
		}
		/*随机字符串*/
		function randomStr($mode,$num=5){
			$n="1234567890";
			$s="abcdefghijklmnopqrstuvwxyz";
			$S="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			$ns=$n.$s;
			$nS=$n.$S;
			$sS=$s.$S;
			$nsS=$n.$s.$S;
			$all=$nsS."_";
			/*function st($s_,$n){
				$str="";
				$str_len=strlen($s_)-1;//获取字符串长度-1
				for ($i=0; $i <$n ; $i++) { 
					$str.=$s_[rand(0,$str_len)];
				}
				return $str;
			}*/
			switch (true) {
				case $mode==="s":
					return $this->st($s,$num);
				break;
				case $mode==="S":
					return $this->st($S,$num);
				break;
				case $mode==="ns":
					return $this->st($ns,$num);
				break;
				case $mode==="nS":
					return $this->st($nS,$num);
				break;
				case $mode==="sS":
					return $this->st($sS,$num);
				break;
				case $mode==="nsS":
					return $this->st($nsS,$num);
				break;
				case $mode==="all":
					return $this->st($all,$num);
				break;
				default:
					return $this->st($n,$num);
				break;
			}
		}
		private function fileType($f_type){	//文件类型检测
			$leng=count($this->config["file_type"]);
			for ($i=0; $i <$leng ; $i++) { 
				if ($f_type===$this->config["file_type"][$i]) {
					return true;
				}
				else{
					if ($i>=$leng-1 && $f_type!==$this->config["file_type"][$i] ) {
						return false;
					}
				}
				//return $f_type===$this->config["file_type"][$i] ? true : false ;
			}
		}
		private function getExtension($fileName){ //获取文件后缀
			$arr=preg_split("/\./",$fileName);//字符串分割，以数组的形式保存
			return $arr[count($arr)-1];
		}
		/*参数：字段名（input标签name属性值） 单个文件上传*/
		function upFile($keyname){
			$dir=$this->config["root_dir"].$this->config["file_dir"];//保存文件的目录
			if (is_dir($dir)) {
				//目录存在
			}
			else{
				mkdir($dir);		//创建目录
				chmod($dir, 0777);	//设置目录权限
			}
			if ($this->fileType($_FILES[$keyname]["type"]) && $_FILES[$keyname]["size"]<$this->config["file_size"]) {
				if ($_FILES[$keyname]["error"]>0) {
					//文件出错
				}
				else{
					$this->config["newFileName"]=date("YmdHis").$this->randomStr("s",4).".".$this->getExtension($_FILES[$keyname]["name"]);//新文件名
					$move_uploaded_url=$dir.$this->config["newFileName"];//新文件路径(相对路径)
					if (file_exists($move_uploaded_url)) {
						//文件存在
					}
					else{
						move_uploaded_file($_FILES[$keyname]["tmp_name"],$move_uploaded_url);
						$this->config["newFileUrl"]=$this->config["server_url"].$this->config["root_dirName"].$this->config["file_dir"].$this->config["newFileName"];//新文件路径(显示绝对路径)
					}
				}
			}
			else{
				//文件格式错误或超出大小
			}
		}
		/*多文件上传*/
		function upFiles($file_arr,$fixed_keyname="file"){
			$len=count($file_arr);
			$this->newFileUrl_arr=array();
			if ($len>0) {
				for ($i=0; $i < $len; $i++) { 
					$this->upFile($fixed_keyname.$i);
					$this->newFileUrl_arr[$i]=$this->config["newFileUrl"];
				}
				function jsonstr($arr){
					$l=count($arr);
					$st="";
					for($j=0;$j<$l;$j++){
						$st.="\"".$arr[$j]."\",";
					}
					return substr($st,0,strlen($st)-1);

				}
				return "{
							\"errno\": 0,
							\"data\":[".jsonstr($this->newFileUrl_arr)."]
						}";
			}
			else{
				return false;
			}
		}
	}
?>
