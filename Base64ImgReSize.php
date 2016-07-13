<?php
/*
$ImgFn = new Base64ImgReSize;
//this file type is jpg or png

$ImgFn->Load('test.jpg',400,300,'put','upload');
//$Img is Base64 Image or File
//set resize width 400
//set resize height 300
//Filename 'test', if filename is '', auto set md5(date('YmdHis').rand(100,1000))
//upload path 'images'(777)
//ps don't set 'test.jpg' or 'test.png', this class auto chack type, and set jpg or png

$ImgFn->ReSize();
//change image size

$ImgFn->Save();
//save imgage to path
//return file path(EX:path/filename)

echo $ImgFn->GetImg();
//return base64 Image
*/

class Base64ImgReSize{
	private $Img;
	private $ImgInfo;
	private $Config;
	private $Filename;
	private $Path;
	private $Type = '';
	function __construct($file = false,$width = false,$height = false,$path = false,$filename = false){
		if(
			$file != false and
			$width != false and
			$height != false
		){
			$this->Load($file, $width, $height, $path, $filename);
		}
	}
	public function Load($file, $width, $height, $path = false, $filename = false) {
		if(is_file($file)){
			$imgData = base64_encode(file_get_contents($file));
			$file = 'data:'.mime_content_type($file).';base64,'.$imgData;
		}
		$this->Img = urldecode($file);
		if(strripos($this->Img,"image/png") != false){
			$this->Type = 'png';
			$this->Img = str_replace('data:image/png;base64,','', $this->Img);
		}
		if(strripos($this->Img,"image/jpeg") != false){
			$this->Type = 'jpeg';
			$this->Img = str_replace('data:image/jpeg;base64,','', $this->Img);
		}
		if($this->Type != ''){
			$this->Img = str_replace(' ', '+', $this->Img);
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
		}else{
			echo "Input Img Error.";
		}
	}
	public function ReSize(){
		if($this->Type != ''){
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
			imagecopyresampled($new_image, $this->Img, 0, 0, 0, 0, $width, $height, $this->ImgInfo[0], $this->ImgInfo[1]);
			$this->Img = $new_image;
		}else{
			echo "Input Img Error.";
			return false;
		}
	}
	public function Save() {
		if($this->Type != ''){
			$this->Filename = ($this->Filename == '')?md5(date('YmdHis').rand(100,1000)):$this->Filename;
			$this->Filename = $this->Path.$this->Filename;
			if($this->Type == 'jpeg'){
				$this->Filename = $this->Filename.'.jpg';
				imagejpeg($this->Img,$this->Filename,75);
			}
			if($this->Type == 'png'){
				$this->Filename = $this->Filename.'.png';
				imagepng($this->Img,$this->Filename);
			}
			/*修改權限*/
			chmod($this->Filename,0755);
			return $this->Filename;
		}else{
			echo "Input Img Error.";
			return false;
		}
	}
	public function GetImg(){
		if($this->Type != ''){
			$ret = "data:image/".$this->Type.";base64,";
			ob_start();
			if($this->Type == 'jpeg') {
				imagejpeg ($this->Img);
			}
			if($this->Type == 'png') {
				imagepng ($this->Img);
			}
			$this->Img = ob_get_contents();
			ob_end_clean();
			$ret .= base64_encode($this->Img);
			return $ret;
		}else{
			echo "Input Img Error.";
			return false;
		}
	}
}