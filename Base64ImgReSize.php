<?php
/*
$ImgFn = new Base64ImgReSize;

$ImgFn->Load($Img,400,300,'test','images');
//$Img is Base64 Image or File
//set resize width 400
//set resize height 300
//Filename 'test', if filename is '', auto set md5(date('YmdHis').rand(100,1000))
//upload path 'images'
//ps don't set 'test.jpg' or 'test.png', this class auto chack type, and set jpg or png

$ImgFn->ReSize();
//change image size

$ImgFn->Save();
//save imgage to path
//return file path(EX:path/filename)

echo $ImgFn->GetImg();

*/

class Base64ImgReSize{
	private $Img;
	private $ImgInfo;
	private $Config;
	private $Filename;
	private $Path;
	public function Load($file,$width,$height,$filename = false, $path = false) {
		if(is_file($file)){
			$imgData = base64_encode(file_get_contents($file));
			$file = 'data:'.mime_content_type($file).';base64,'.$imgData;
		}
		$this->Img = urldecode($file);
		if(strripos($this->Img,"image/png") != false){
			$type = 'png';
			$this->Img = str_replace('data:image/png;base64,','', $this->Img);
			$this->Img = str_replace(' ', '+', $this->Img);
		}
		if(strripos($this->Img,"image/jpeg") != false){
			$type = 'jpeg';
			$this->Img = str_replace('data:image/jpeg;base64,','', $this->Img);
			$this->Img = str_replace(' ', '+', $this->Img);
		}
		$Tmp = base64_decode($this->Img);
		$this->Img = imagecreatefromstring($Tmp);
		$this->ImgInfo = getimagesizefromstring($Tmp);
		$this->Config = array('width'=>$width,'height'=>$height);
		$this->Filename = ($filename)?$filename:'';
		$this->Path = '';
		if($path){
			$tmp = '';
			foreach(explode('/',$path) as $val){
				if($val != ''){
					if($tmp != ''){
						$tmp .= '/';
					}
					$tmp .= $val;
				}
			}
			$this->Path = $tmp . '/';
		}
	}
	public function ReSize(){
		$width = $this->ImgInfo[0];
		$height = $this->ImgInfo[1];
		if(
			$this->ImgInfo[0] > $this->Config['width'] or
			$this->ImgInfo[1] > $this->Config['height']
		){
			if($this->ImgInfo[0] > $this->ImgInfo[1]){
				if($this->ImgInfo[0] > $this->Config['width']){
					$ratio = $this->Config['width'] / $this->ImgInfo[0];
					$ratio = round($ratio, 3);
					$height = ceil($this->ImgInfo[1] * $ratio);
					$width = $this->Config['width'];
				}
			}else{
				if($this->ImgInfo[1] > $this->Config['height']){
					$ratio = $this->Config['height'] / $this->ImgInfo[1];
					$ratio = round($ratio, 3);
					$width = ceil($this->ImgInfo[0] * $ratio);
					$height = $this->Config['height'];
				}
			}
		}
		$new_image = imagecreatetruecolor($width, $height);
		//利用imagecopyresampled resize圖片
		//imagecopyresampled(目地圖片,來源圖片,目地x座標,目地y座標,來源x座標,來源y座標,目地寬度,目地高度,來源寬度,來源高度)
		imagecopyresampled($new_image, $this->Img, 0, 0, 0, 0, $width, $height, $this->ImgInfo[0], $this->ImgInfo[1]);
		//將image變數指向新的圖片
		$this->Img = $new_image;
	}
	public function Save() {
		if($this->Filename == ''){
			$this->Filename = $this->Path.md5(date('YmdHis').rand(100,1000));
		}else{
			$this->Filename = $this->Path.$this->Filename;
		}
		if( $this->ImgInfo['mime'] == 'image/jpeg' ) {
			$this->Filename = $this->Filename.'.jpg';
			imagejpeg($this->Img,$this->Filename,75);
		}elseif( $this->ImgInfo['mime'] == 'image/png' ) {
			$this->Filename = $this->Filename.'.png';
			imagepng($this->Img,$this->Filename);
		}
		//修改權限
		chmod($this->Filename,0755);
		return $this->Filename;
	}
	public function GetImg(){
		$tmp = '';
		ob_start ();
		if( $this->ImgInfo['mime'] == 'image/jpeg' ) {
			$tmp = 'data:image/jpeg;base64,';
			imagejpeg ($this->Img);
		}
		if( $this->ImgInfo['mime'] == 'image/png' ) {
			$tmp = 'data:image/png;base64,';
			imagepng ($this->Img);
		}
		$this->Img = ob_get_contents ();
		ob_end_clean ();
		$this->Img = $tmp.base64_encode ($this->Img);
		return $this->Img;
	}
}