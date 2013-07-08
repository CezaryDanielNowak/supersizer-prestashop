<?php

# function super sizer adjusted to work with Prestashop
# Cezary Nowak

#CMS - CMS Made Simple
#(c)2004 by Ted Kulp (wishy@users.sf.net)
#This project's homepage is: http://cmsmadesimple.sf.net
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program; if not, write to the Free Software
#Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

class SuperSizer {
	 var $strOrgImgPath;
	 var $strRImgPath;
	 var $arrOrgDetails;
	 var $arrRDetails;
	 var $resOrgImg; 
	 var $resRImg;
	 var $es;
	 var $orientation;
	 var $boolProtect = true; 
	  
	 function __constructor($SS){
			$this->SuperSizer($SS); 
	 }



	 function SuperSizer($SS){
		if($SS->filename!=''){
			//ini_set('memory_limit', '64M');  /// may be?
			$this->strOrgImgPath = rawurldecode($SS->path); 
			$this->strRImgPath = $SS->filename; 
			$this->boolProtect = $SS->Protect; 
			if($SS->fill!=false){
				if ((substr($SS->fill, 0,1))=="#"){
					$SS->fill = $this->color_hex2dec(ltrim($SS->fill,"/"));
				}else{
					$SS->fill=explode(',',$SS->fill);
				}	
			}
			//get the image dimensions
			if(!@getimagesize($this->strOrgImgPath)){
				$this->errors[]= '<br/><font color="#FF0000">There is a path issue with the orginal image!</font><br/><strong>Does this look right?<br/>Path:</strong>'.$this->strOrgImgPath.'<br/>';
				return false;
			}else{
				$this->arrOrgDetails = getimagesize($this->strOrgImgPath);
				$GIS = $this->arrOrgDetails;
				if(($GIS[0]<$GIS[1])){
					$this->orientation = 'P';
				}elseif(($GIS[0]>$GIS[1])){
					$this->orientation = 'L';
				}elseif($GIS[0]==$GIS[1]){
					$this->orientation = 'S';
				}
			}
			$this->arrRDetails = $this->arrOrgDetails;
			//create an image resouce to work with
			$this->resOrgImg = $this->createImage($this->strOrgImgPath);
			if(!@imagesx($this->resOrgImg)){
				$this->errors[]=  '<br/><font color="#FF0000">Your orginal image has a corrupted header or is the wrong file type.</font><br/>';
				return false;
			}
			//select the image resize type
			switch(strtoupper($SS->size_by)){
			   case 'P':
				  $this->resizeToPercent($SS->size_mesure, $SS);
				  break;
			   case 'H':
				  $this->resizeToHeight($SS->size_mesure, $SS);
				  break;
			   case 'C':
				  $this->resizeToCustom($SS->size_mesure, $SS);
				  break;
			   case 'W':
			   default:
				  $this->resizeToWidth($SS->size_mesure, $SS); 
				  break;
			} 
		} 
	 }
	function color_hex2dec ($color) {
		if ($color[0] == '#')
		$color = substr($color, 1);
		if (strlen($color) == 6){
			list($r, $g, $b) = array($color[0].$color[1],$color[2].$color[3],$color[4].$color[5]);
		}elseif (strlen($color) == 3){
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		}else{
			return false;
		}
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return array($r, $g,$b);
	}
	 function findResourceDetails($resRImg){
		//check to see what image is being requested
		if(isset($resRImg)&&isset($this->resRImg)&&$resRImg==$this->resRImg){                              
		   //return new image details
		   return $this->arrRDetails;
		}else{
		   //return original image details
		   return $this->arrOrgDetails;
		}
	 }
	
	 function updateNewDetails(){ 
		if(!@imagesx($this->resRImg)){
			$this->errors[]=  '<br/><font color="#FF0000">The resized image has not be created. Possible Memory limit reeached</font><br/>';
			return false;
		}
		$this->arrRDetails[0] = imagesx($this->resRImg);
		$this->arrRDetails[1] = imagesy($this->resRImg);
	 }
	
	 function createImage($strImgPath){
		//get the image details
		$arrDetails = $this->findResourceDetails($strImgPath);
		  
		//choose the correct function for the image type  
		switch($arrDetails['mime']){
		   case 'image/jpeg':
			  return imagecreatefromjpeg($strImgPath);
			  break;
		   case 'image/png':
			  return imagecreatefrompng($strImgPath);
			  break;
		   case 'image/gif':
			  return imagecreatefromgif($strImgPath);
			  break;
		}
	 } 
	
	  function filters($filter, $fa1='null', $fa2='null', $fa3='null', $fa4=0){
		//choose the correct function for the image type  
		switch($filter){
		   case 'NEGATE':
			  return imagefilter($this->resRImg, IMG_FILTER_NEGATE);
			  break;
		   case 'GRAYSCALE':
			 return imagefilter($this->resRImg, IMG_FILTER_GRAYSCALE);
			  break;
		   case 'BRIGHTNESS':
			  return imagefilter($this->resRImg, IMG_FILTER_BRIGHTNESS, $fa1);
			  break;
		   case 'CONTRAST':
			 return imagefilter($this->resRImg, IMG_FILTER_CONTRAST, $fa1);
			  break;
		   case 'COLORIZE':
			  return imagefilter($this->resRImg, IMG_FILTER_COLORIZE, $fa1, $fa2, $fa3, $fa4);
			  break;
		   case 'EDGEDETECT':
			  return imagefilter($this->resRImg, IMG_FILTER_EDGEDETECT);
			  break;
		   case 'EMBOSS':
			  return imagefilter($this->resRImg, IMG_FILTER_EMBOSS);
			  break;
		   case 'GAUSSIAN_BLUR':
			  return imagefilter($this->resRImg, IMG_FILTER_GAUSSIAN_BLUR);
			  break;
		   case 'SELECTIVE_BLUR':
			  return imagefilter($this->resRImg, IMG_FILTER_SELECTIVE_BLUR);
			  break;
		   case 'MEAN_REMOVAL':
			 return imagefilter($this->resRImg, IMG_FILTER_MEAN_REMOVAL);
			  break;
		   case 'SMOOTH':
			  return imagefilter($this->resRImg, IMG_FILTER_SMOOTH, $fa1!='null'?$fa1:100);
			  break;
		   case 'PIXELATE':
			  return imagefilter($this->resRImg, IMG_FILTER_PIXELATE, $fa1, $fa2);
			  break;
		   case 'IMAGEHUE':
			  $this->imagehue($this->resRImg, $fa1, $fa2, $fa3, $fa4);
			  break;
		}
	} 

	function imagehue($im,$angle,$red='',$g,$b){
		if($red!=''){
			$rgb = $red+$g+$b;
			$col = array($red/$rgb,$b/$rgb,$g/$rgb);
		}
		if($angle % 360 == 0&&$red=='') return; 
		$h = imagesy($im);
		$w = imagesx($im);
			for($x = 0; $x < $w; $x++) { 
				for($y = 0; $y < $h; $y++) { 

					$rgb = imagecolorat($im, $x, $y); 
					$r = ($rgb >> 16) & 0xFF; 
					$g = ($rgb >> 8) & 0xFF; 
					$b = $rgb & 0xFF; 
					if($red!=''){
						$newR = $r*$col[0] + $g*$col[1] + $b*$col[2];
						$newG = $r*$col[2] + $g*$col[0] + $b*$col[1];
						$newB = $r*$col[1] + $g*$col[2] + $b*$col[0];
						$r=$newR;
						$g=$newG;
						$b=$newB;
					}
					if($red==''){
						list($h, $s, $l) = $this->rgb2hsl($r, $g, $b); 
						$h += $angle / 360; 
						if($h > 1) $h--; 
						list($r, $g, $b) = $this->hsl2rgb($h, $s, $l); 
					}
					imagesetpixel($im, $x, $y, imagecolorallocate($im, $r, $g, $b)); 
				} 
			} 

		return $im;
	}
	function rgb2hsl($r, $g, $b) { 
	   $var_R = ($r / 255); 
	   $var_G = ($g / 255); 
	   $var_B = ($b / 255); 
	 
	   $var_Min = min($var_R, $var_G, $var_B); 
	   $var_Max = max($var_R, $var_G, $var_B); 
	   $del_Max = $var_Max - $var_Min; 
	 
	   $v = $var_Max; 
	  $h = 0; 
	  $s = 0; 
	  $max=0;
	   if ($del_Max == 0) { 
		  $h = 0; 
		  $s = 0; 
	   } else { 
		  $s = $del_Max / $var_Max; 
	 
		  $del_R = ( ( ( $max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max; 
		  $del_G = ( ( ( $max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max; 
		  $del_B = ( ( ( $max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max; 
	 
		  if      ($var_R == $var_Max) $h = $del_B - $del_G; 
		  else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B; 
		  else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R; 
	 
		  if ($h < 0) $h++; 
		  if ($h > 1) $h--; 
	   } 
	 
	   return array($h, $s, $v); 
	} 
	 
	function hsl2rgb($h, $s, $v) { 
		if($s == 0) { 
			$r = $g = $B = $v * 255; 
		} else { 
			$var_H = $h * 6; 
			$var_i = floor( $var_H ); 
			$var_1 = $v * ( 1 - $s ); 
			$var_2 = $v * ( 1 - $s * ( $var_H - $var_i ) ); 
			$var_3 = $v * ( 1 - $s * (1 - ( $var_H - $var_i ) ) ); 
	 
			if       ($var_i == 0) { $var_R = $v     ; $var_G = $var_3  ; $var_B = $var_1 ; } 
			else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $v      ; $var_B = $var_1 ; } 
			else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $v      ; $var_B = $var_3 ; } 
			else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $v     ; } 
			else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $v     ; } 
			else                   { $var_R = $v     ; $var_G = $var_1  ; $var_B = $var_2 ; } 
	 
			$r = $var_R * 255; 
			$g = $var_G * 255; 
			$B = $var_B * 255; 
		}     
		return array($r, $g, $B); 
	}
	
	function imagefillfromfile($image, $w, $h) {
		$imgW = imagesx($image);
		$imgH = imagesy($image);
		$newImg = imagecreatetruecolor($w, $h);
		for ($imageX = 0; $imageX < $w; $imageX += $imgW) {
			for ($imageY = 0; $imageY < $h; $imageY += $imgH) {
				imagecopy($newImg, $image, $imageX, $imageY, 0, 0, $imgW, $imgH);
			}
		}
		return($newImg);
	}
 
	
	
	 function saveImg($SS){
		if($this->strRImgPath==$this->strOrgImgPath){
			if(!is_writable($this->strOrgImgPath)){
				@chmod($this->strOrgImgPath,777);   
			}
			if(!is_writable($this->strOrgImgPath)){
				$this->errors[]=  '<br/><font color="#FF0000">Your orginal needs to have the permissions changed.  The file has <pre>'.substr(sprintf('%o', fileperms($this->strOrgImgPath)), -4).'</pre></font><br/>';  
			}
		}

		switch($this->arrRDetails['mime']){
		   case 'image/jpeg':
				imagejpeg($this->resRImg, $this->strRImgPath, $SS->q);
				break;
		   case 'image/png':
			  // imagepng = [0-9] (not [0-100]) 
			  $newNumber=$SS->q/10;
			  $SS->q = number_format($newNumber, 0, '.', '');
			  imagepng($this->resRImg, $this->strRImgPath, $SS->q);
			  break;
		   case 'image/gif':
			  imagegif($this->resRImg, $this->strRImgPath); 
			  break;
		}
		
		
		imagedestroy($this->resRImg);
		unset($this->resRImg);
	 }

	 function _resize($numW, $numH, $SS){
		//check for image protection
		if($this->_imageProtect($numW, $numH)){ 
			$this->resRImg = imagecreatetruecolor($numW, $numH);
			if($this->arrOrgDetails['mime']=='image/jpeg'){
			  //JPG image
				imageinterlace($this->resRImg, false);
				//imageantialias($this->resRImg, true);
			}else if($this->arrOrgDetails['mime']=='image/gif'){
			  //GIF image
				imagetruecolortopalette($this->resRImg, true, 256);
				//imageantialias($this->resRImg, true);
				imagealphablending($this->resRImg, false);
				imagesavealpha($this->resRImg,true);
				$transparent = imagecolorallocatealpha($this->resRImg, 255, 255, 255, 127);
				imagefilledrectangle($this->resRImg, 0, 0, $numW, $numH, $transparent);
				imagecolortransparent($this->resRImg, $transparent);
			}else if($this->arrOrgDetails['mime']=='image/png'){  
			  //PNG image  
				imagecolortransparent($this->resRImg, imagecolorallocate($this->resRImg, 0, 0, 0));   
				// Turn off transparency blending (temporarily)
				imagealphablending($this->resRImg, false);
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($this->resRImg, 0, 0, 0, 127);
				// Completely fill the background of the new image with allocated color.
				imagefill($this->resRImg, 0, 0, $color);
				// Restore transparency blending
				imagesavealpha($this->resRImg, true);
			}


//$tcolor = imagecolorat($src,0,$h-1);
//$dest = imagerotate($src,30,$tcolor,0);
//imagecolortransparent($dest,$tcolor);
//imagepng($dest,'uploads/test.png');


			$src_x = 0;
			$src_y = 0;
			$dst_x = 0;
			$dst_y = 0;
			$dst_w=$numW;
			$dst_h=$numH;
			$src_w=$this->arrOrgDetails[0];
			$src_h=$this->arrOrgDetails[1];	
			//$c="true";
			$zoomFlag = false;
				if($SS->c!=false){
					//if the image is smaller than crop size
					//we're always going to crop from center
					if($SS->c=='true'){
						$aspect_ratio = $numW / $numH;
						$Original_aspect_ratio = $src_w / $src_h;
						$src_x = $src_y = 0;
						if($aspect_ratio < $Original_aspect_ratio){
							//Aspect Ratio is less than original, need to Crop sides of original.
							$src_x = round(($src_w - ($src_h * $aspect_ratio)) / 2);
							$src_w = $src_h * $aspect_ratio;
						}else{
							//Aspect Ratio is less than original, need to Crop Tops of original.
							$src_y = round(( $src_h / 2 ) -  (($src_w/$aspect_ratio) / 2));
							$src_h = $src_w/$aspect_ratio;
						}
						$this->resRImg  = imagecreatetruecolor($numW, $numH);
						$dst_w=$numW;
						$dst_h=$numH;

					}else{
						$SS->c=explode(",", $SS->c);
						if(!isset($SS->c[2])||$SS->c[2]==''){$SS->c[2]=100;}
						if($SS->c[2] <> '100'){
							$zoomFlag = true;
							$zoomWidth = $src_w * ($SS->c[2] /100);
							$zoomHeight = $src_h * ($SS->c[2] /100);
							
							$resZoomSourceImage = imagecreatetruecolor($zoomWidth, $zoomHeight);
							imagecopyresampled($resZoomSourceImage, $this->resOrgImg, 0, 0, 0, 0,  $zoomWidth, $zoomHeight, $src_w, $src_h);
	
							$this->arrOrgDetails[0]= $zoomWidth;
							$this->arrOrgDetails[1]= $zoomHeight;
						}
						$src_w=$this->arrOrgDetails[0];
						$src_h=$this->arrOrgDetails[1];
						$dst_w=$src_w*($numW/$src_w);
						$dst_h=$src_h*($numH/$src_h);

	
						switch($SS->c[0]){
						   case 'left':
							  $src_x=0;
							  break;
						   case 'right':
							  $src_x=$src_w-$dst_w;
							  break;
						   case 'center':
							  $src_x=($src_w*.5)-($dst_w*.5); 
							  break;
						}	
						switch($SS->c[1]){
						   case 'top':
							  $src_y=0;
							  break;
						   case 'bottom':
							  $src_y=$src_h-$dst_h;
							  break;
						   case 'center':
							  $src_y=($src_h*.5)-($dst_h*.5); 
							  break;
						}
						$src_w=$numW;
						$src_h=$numH;
					}	
			}else{
				$dst_w=$numW;
				$dst_h=$numH;
				$src_w=$this->arrOrgDetails[0];
				$src_h=$this->arrOrgDetails[1];
			}
			
			if($SS->fit===true){
				
			}elseif($SS->fit==='hard'){
				

/****************************/
/* AREA TEST FOR FIT AND FILL */
				$aspect_ratio = $numW / $numH;
				$Original_aspect_ratio = $src_w / $src_h;
				$aspect_ratio = $numW / $numH; // this is the ratio of the target area
				if($aspect_ratio < $Original_aspect_ratio){
				//New image is wide to find the image and make it fit
					$src_x = 0; 
					$src_y = ( $src_h / 2 ) -  (($src_w/$aspect_ratio) / 2); 
					$src_h = $src_w/$aspect_ratio; 
				}else{ 
				//New image is tall to find the image and make it fit
					$max_width=900;
					$max_height=374;
					$ratio=min($numW/$src_w,$numH/$src_h);
					$dst_w=(int) ($ratio*$src_w+.5);
					$dst_h=(int) ($ratio*$src_h+.5);
					$dst_x=($numW-$dst_w)/2;			
				}
/****************************/	
			}

			if($zoomFlag){			
				$OriginalImage=$resZoomSourceImage;
			}else{
				$OriginalImage=$this->resOrgImg;
			}



/****************************/
/* AREA TEST FOR FIT AND FILL --- this needs to happen after for hor .. */
			if($SS->fill!=false){ 
				imagefill($this->resRImg, 0, 0, imagecolorallocate($this->resRImg, $SS->fill[0], $SS->fill[1], $SS->fill[2]) ); 
			} 
/****************************/		
			if($this->arrOrgDetails['mime']=='image/gif'){
			  //GIF image
				imagetruecolortopalette($this->resRImg, true, 256);
				//imageantialias($this->resRImg, true);
				imagealphablending($this->resRImg, false);
				imagesavealpha($this->resRImg,true);
				$transparent = imagecolorallocatealpha($this->resRImg, 255, 255, 255, 127);
				imagefilledrectangle($this->resRImg, 0, 0, $numW, $numH, $transparent);
				imagecolortransparent($this->resRImg, $transparent);
			}else if($this->arrOrgDetails['mime']=='image/png'){  
			  //PNG image  
				imagecolortransparent($this->resRImg, imagecolorallocate($this->resRImg, 0, 0, 0));   
				// Turn off transparency blending (temporarily)
				imagealphablending($this->resRImg, false);
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($this->resRImg, 0, 0, 0, 127);
				// Completely fill the background of the new image with allocated color.
				imagefill($this->resRImg, 0, 0, $color);
				// Restore transparency blending
				imagesavealpha($this->resRImg, true);
			}



			//update the image size details  
			$this->updateNewDetails();  
			if($SS->sample==true){
				//do the actual image resize  
				imagecopyresampled($this->resRImg, $OriginalImage, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
				
			}else{
				imagecopyresized ($this->resRImg, $OriginalImage, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
			}

/****************************/
/* AREA TEST FOR FIT AND FILL --- this needs to happen after for ver .. */
			if($SS->fill!=false){ 
				imagefill($this->resRImg, 0, 0, imagecolorallocate($this->resRImg, $SS->fill[0], $SS->fill[1], $SS->fill[2]) ); 
			} 
/****************************/		


			if($SS->filter!=""){
				//filter
				// Here we split the variables. 
				$FiltersArray = explode(",", $SS->filter); 
				$Fa1Array     = explode(",", $SS->fa1); 
				$Fa2Array     = explode(",", $SS->fa2); 
				$Fa3Array     = explode(",", $SS->fa3); 
				$Fa4Array     = explode(",", $SS->fa4); 
				$i = 0; 
				foreach ($FiltersArray as $Filter){ 
					$this->filters($FiltersArray[$i], $Fa1Array[$i], $Fa2Array[$i], $Fa3Array[$i], $Fa4Array[$i]); 
					$i++;
				}
			}  	

			$this->arrRDetails['full_Resizement'] = array('dst_x'=>$dst_x,'dst_y'=>$dst_y,'src_x'=>$src_x,'src_y'=>$src_y,'dst_w'=>$dst_w,'dst_h'=>$dst_h,'src_w'=>$src_w,'src_h'=>$src_h);

		   //saves the image  
		   $this->saveImg($SS);
		}  
	 }
		
	 function _imageProtect($numW, $numH){  
		if($this->boolProtect && ($numW > $this->arrOrgDetails[0] || $numH > $this->arrOrgDetails[1])){  
		   return 0;
		}
		return 1;
	 }  
	
	 function resizeToWidth($numW, $SS){ 
		$numH=(int)(($numW*$this->arrOrgDetails[1])/$this->arrOrgDetails[0]);
		$this->_resize($numW, $numH, $SS);
	 }
	 	
	 function resizeToHeight($numH, $SS){
		$numW=(int)(($numH*$this->arrOrgDetails[0])/$this->arrOrgDetails[1]);
		$this->_resize($numW, $numH, $SS);
	 }
	
	
	 function resizeToPercent($numPercent, $SS){
		$numW = (int)(($this->arrOrgDetails[0]/100)*$numPercent);
		$numH = (int)(($this->arrOrgDetails[1]/100)*$numPercent);
		$this->_resize($numW, $numH, $SS); 
	 }
	 
	
	 function resizeToCustom($size, $SS){
		if(!is_array($size)){
		   $numW=(int)$size;
		   $numH=(int)$size;
		}else{
		   $numW=(int)$size[0];
		   $numH=(int)$size[1];
		}
		$this->_resize($numW, $numH, $SS); 
	 }


	function recursive_rmdir($dir)
	 {
		if(is_dir($dir)){
			$dir=_PS_ROOT_DIR_.str_replace(_PS_ROOT_DIR_, '',$dir);
			$dir_handle=opendir($dir);
		}else{
			return false;
		}
		while($file=readdir($dir_handle))
		{
		if($file!="." && $file!="..")
		{
		if(!is_dir($dir."/".$file))unlink ($dir."/".$file);
		else $this->recursive_rmdir($dir."/".$file);
		}
		}
		closedir($dir_handle);
		rmdir($dir);
		return true;
	}
}



function smarty_cms_function_supersizer($p,$smarty) {
		$SS = new stdClass;
		$SS->capture = isset($p['capture']) ? trim($p['capture']):false;
		
		if (!function_exists('copy_alt')) {
			function myErrorHandler($code, $message, $file, $line) {
				if($code===E_ERROR||$code===E_USER_ERROR){
					//debug_to_log('Error: '.$message);
					//debug_to_log('Error: memory_limit: '.ini_get('memory_limit').' @'.$line);
					ini_set( 'memory_limit', preg_replace( '/[M]/', '',@ini_get('memory_limit'))+20 . 'M' );
					//debug_to_log('Error: memory_limit: '.ini_get('memory_limit'));
					return(true); //And prevent the PHP error handler from continuing
				}
				//debug_to_log('failed to get to the fricken fatal->'.$message.' @'.$line);
				return(false); //Otherwise, use PHP's error handler
			}
			function fatalErrorShutdownHandler(){
				$last_error = error_get_last();
				if ($last_error['type'] === E_ERROR||$last_error['type'] === E_USER_ERROR) {
					myErrorHandler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
				}
			}
		}
		set_error_handler('myErrorHandler');
		register_shutdown_function('fatalErrorShutdownHandler');

		
		if (!function_exists('copy_alt')) {
			function copy_alt($file1,$file2){ 
				$contentx =@file_get_contents($file1); 
				$openedfile = fopen($file2, "w"); 
				fwrite($openedfile, $contentx); 
				fclose($openedfile); 
				$status=$contentx === FALSE?false:true; 
				return $status; 
			}
		}
		
		if (!function_exists('resetMemory')) {	
			function resetMemory($old_limit){
				if(function_exists('ini_restore')){
					ini_restore ("memory_limit");
				}else if(function_exists('ini_set')){
					ini_set( 'memory_limit', $old_limit . 'M' );
				}
			}
		}	
		if (!function_exists('set_memory')) {
			function set_memory($filename,$memory_limit=0,$w_sized=0,$h_sized=0){
				$TWEAKFACTOR = 3;  // Or whatever works for you
				$imageInfo = @getimagesize($filename);
				if(!isset($imageInfo['channels']))return;
				$current=preg_replace( '/[M]/', '', get_memory());
				$memoryNeeded = round(($w_sized * $h_sized * $imageInfo['channels'] + $imageInfo[0] * $imageInfo[1] * $imageInfo['channels'] )*$TWEAKFACTOR / 1024 / 1024 )+20+$current;
				if ($current){
					if ($memoryNeeded > $current-10){
						$newLimit = ceil($memoryNeeded);
						ini_set( 'memory_limit', $newLimit . 'M' );
						return;
					}
					return;
				}else{
					return;
				}
			}
		}
		if (!function_exists('get_memory')) {
			function get_memory($useage=false){
				if(function_exists('ini_get')){
					return @ini_get('memory_limit'); 
				}else{
					$useage=true;
				}
				if($useage){
					 if ( substr(PHP_OS,0,3) == 'WIN'){ 
						   if ( substr( PHP_OS, 0, 3 ) == 'WIN' ){ 
								$output = array(); 
								exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );
								return preg_replace( '/[\D]/', '', $output[5] );// * 1024; 
							} 
					}else{ 
						$pid = getmypid(); 
						exec("ps -eo%mem,rss,pid | grep $pid", $output); 
						$output = explode("  ", $output[0]); 
						return $output[1];// * 1024; 
					}
				}
				return false;
			}
		}		
		
		$SS->debug = isset($p['debug']) ? $p['debug']:false;
		$SS->no_output = isset($p['no_output']) ? $p['no_output']:false;
		$SS->assign = isset($p['assign']) ? $p['assign']:'';
		$SS->path_orgin=$SS->path=isset($p['path']) ? trim($p['path']):false;
	
		$SS->from=isset($p['from']) ? trim($p['from']):false;
		$SS->to=isset($p['to']) ? trim($p['to']):false;
		if($SS->debug==true){error_reporting(E_ALL);}
		
		//$path_orgin=$path;
		$SS->prefix = isset($p['prefix']) ? $p['prefix']:'';
		$SS->suffix = isset($p['suffix']) ? $p['suffix']:'';
		$SS->subdir = isset($p['subdir']) ? $p['subdir'] : "";
		$SS->strip_tags  = isset($p['strip_tags']) ? $p['strip_tags'] : false;
	
		if($SS->from!=false){$SS->path=$SS->from;}
		if($SS->capture!=false){$SS->path=$SS->capture;}
		if($SS->strip_tags){
			if (preg_match('#<(.*?)src="(.*?)"(.*?)/>#', $SS->path, $matches)) {$SS->path =  $matches[2];}
			if ((substr($SS->path, 0,5))=="https"){$L=strlen(_PS_BASE_URL_SSL_);$SS->path = substr_replace($SS->path, '', 0, $L);}	
			if ((substr($SS->path, 0,4))=="http"){$L=strlen(_PS_BASE_URL_);$SS->path = substr_replace($SS->path, '', 0, $L);}
			$matches = array(); 
			if (preg_match('#[http|https]://(.*?)/(.*?)#', $SS->path, $matches)) {$SS->path =  $matches[0];}
		}
		$SS->es = isset($p['errors']) ? $p['errors']:true;
		$SS->unique  = isset($p['unique']) ? $p['unique'] : true;
		$SS->url = isset($p['url']) ? $p['url']:'';

		$SS->OlDextension = substr($SS->path, (strrpos($SS->path,".")+1));
		$SS->ext = strtolower(substr($SS->path, (strrpos($SS->path,".")+1)));
		$SS->filebasename = basename($SS->path, $SS->OlDextension);
		if($SS->capture){
			if(!file_exists(_PS_ROOT_DIR_."/uploads/SuperSizerTmp/captured")){mkdir(_PS_ROOT_DIR_."/uploads/SuperSizerTmp/captured", 0777, true);}
			$SS->path="/uploads/SuperSizerTmp/captured/".$SS->prefix.$SS->filebasename.$SS->suffix.'.'.$SS->ext;
			if(!file_exists(_PS_ROOT_DIR_.$SS->path)){
				if (!function_exists('copy')) {
					copy_alt($SS->capture,_PS_ROOT_DIR_.$SS->path);
				}else{
					copy($SS->capture,_PS_ROOT_DIR_.$SS->path);
				}
			}
		}
		$SS->c  = isset($p['crop']) ? $p['crop'] : false;
		$SS->sample = isset($p['sample']) ? $p['sample']:true;
		$SS->filter = isset($p['filter']) ? $p['filter'] : "";
		
		if(!isset($p['width']) && !isset($p['height']) && !isset($p['percentage'])) {
			$SS->percent = 25;
		}else{
			$SS->w = isset($p['width']) ? intval($p['width']) : 0;
			$SS->h = isset($p['height']) ? intval($p['height']) : 0;
			$SS->percent = isset($p['percentage']) ? intval($p['percentage']) : 0;
		}
	
		if($SS->subdir!=''){
			$SS->subdir = rtrim(ltrim($SS->subdir,"/"),"/");
			$SS->subdir .='/';
		}
	
		if($SS->path!=''){
			if ((substr($SS->path, 0,5))=="http:"){$SS->path = ltrim($SS->path,'http://');}
			if ((substr($SS->path, 0,6))=="https:"){$SS->path = ltrim($SS->path,'https://');}
			$path = ltrim($SS->path,"/");
		}else{
			if($SS->es){echo "<font color=\"#FF0000\">You have forgoten to define the orginal image. Please do so.</font>";return false;}
		}

		$SS->q = isset($p['quality']) ? intval($p['quality']) : 85;
		$SS->fa1 = isset($p['arg_1']) ? $p['arg_1'] : '';
		$SS->fa2 = isset($p['arg_2']) ? $p['arg_2'] : '';
		$SS->fa3 = isset($p['arg_3']) ? $p['arg_3'] : '';
		$SS->fa4 = isset($p['arg_4']) ? $p['arg_4'] : '';
		
		$SS->cpName='';
		if($SS->unique!=true){$SS->U='';}else{if($SS->c!=false){$SS->cpName=str_replace(',','',$SS->c);}$SS->U="-w{$SS->w}-h{$SS->h}-p{$SS->percent}-q{$SS->q}-F{$SS->filter}-{$SS->fa1}-{$SS->fa2}-{$SS->fa3}-{$SS->fa4}-S{$SS->sample}-c{$SS->cpName}";}
		
		$SS->path=_PS_ROOT_DIR_."/".$SS->path;
		$SS->root = isset($p['root']) ? $p['root'] : __PS_BASE_URI__;
		$SS->overwrite = isset($p['overwrite']) ? $p['overwrite'] : false;
		
		if($SS->overwrite){
			$SS->filename=$SS->path;
		}elseif($SS->to!=false){
			$SS->path_orgin=$SS->to;
			$SS->to = ltrim($SS->to,"/");
			
			$SS->filename = _PS_ROOT_DIR_.'/'.$SS->to;
		}else{
			$SS->filename=_PS_UPLOAD_DIR_."/SuperSizerTmp/".$SS->subdir.$SS->prefix.$SS->filebasename.$SS->U.$SS->suffix.".".$SS->ext;
		}
		
		$SS->fileLINK=$SS->root."/SuperSizerTmp/".$SS->subdir.$SS->prefix.$SS->filebasename.$SS->U.$SS->suffix.".".$SS->ext;
		$SS->Protect= isset($p['protect']) ? $p['protect'] : true;
		
		if(!$SS->overwrite){
			if(file_exists($SS->filename)){
				if($SS->debug){
						unlink ($SS->filename);
				}elseif(@filemtime($SS->filename) < @filemtime($SS->path)){
						unlink ($SS->filename);
				}
			}
		}
		
		$SS->class = isset($p['class']) ? ' class="'.$p['class'].'" ':'';
		$SS->alt = isset($p['alt']) ? $p['alt']:'';
		$SS->id = isset($p['id']) ? ' id="'.$p['id'].'" ':'';
		$SS->title = isset($p['title']) ? ' title="'.$p['title'].'" ':'';
		$SS->style = isset($p['style']) ? ' style="'.$p['style'].'" ':'';
		$SS->w_attr = isset($p['width_attr']) ? ' width="'.$p['width_attr'].'" ':'';
		$SS->h_attr = isset($p['height_attr']) ? ' height="'.$p['height_attr'].'" ':'';
		$SS->usemap = isset($p['usemap']) ? ' usemap="#'.$p['usemap'].'" ':'';
		$SS->bustcache = isset($p['cachebuster']) ? $p['cachebuster']:true;
		
		$SS->dynamic = isset($p['dynamic']) ? $p['dynamic']:false;
		$SS->b64 = isset($p['base64']) ? $p['base64']:false;
		$SS->return = isset($p['return']) ? $p['return']:false;
		$SS->passthru = isset($p['passthru']) ? $p['passthru']:false;
		if(!file_exists($SS->filename)||$SS->overwrite||$SS->to!=false||$SS->debug||$SS->dynamic){
			$SS->auto_memory = isset($p['auto_memory']) ? $p['auto_memory']:true;
			if($SS->auto_memory){
				$SS->old_limit=get_memory();
				$SS->memory_limit = isset($p['memory_limit']) ? $p['memory_limit']:0;				
				set_memory($SS->path,$SS->memory_limit,$SS->w,$SS->h);
				$SS->new_limit=get_memory();
			}
			if(!file_exists(_PS_UPLOAD_DIR_."/SuperSizerTmp/".$SS->subdir)){
				mkdir(_PS_UPLOAD_DIR_."/SuperSizerTmp/".$SS->subdir, 0777, true);
			}
			$SS->capture=_PS_ROOT_DIR_.$SS->path;
			
			$SS->fill = isset($p['fill']) ? $p['fill']:false;
			$SS->fit = isset($p['fit']) ? $p['fit']:false;
			if($SS->fit!==false&&$SS->fit!=='hard'){
				$GIS=@getimagesize($SS->path);
				if($SS->h==0||$SS->w==0){
					echo "Fit only works if both the width and height are set";
				}elseif($GIS[0]>$GIS[1]&&$SS->percent!=''){
					echo "Fit doesn't work with percentage";
				}else{
					if($GIS[0]<$GIS[1]){
							$SS->size_by='H';
							$SS->size_mesure=$SS->h;
					}elseif($GIS[0]>$GIS[1]){
							$SS->size_by='W';
							$SS->size_mesure=$SS->w;
					}elseif($GIS[0]==$GIS[1]){
						if($SS->w>$SS->h){
							$SS->size_by='W';
							$SS->size_mesure=$SS->w;
						}else{
							$SS->size_by='H';
							$SS->size_mesure=$SS->h;
						}
					}
				}
			}else{
				if($SS->w>0 || $SS->h>0) {
					if ($SS->w>0 && $SS->h==0){
						$SS->size_by='W';
						$SS->size_mesure=$SS->w;
					}elseif($SS->h>0 && $SS->w==0){
						$SS->size_by='H';
						$SS->size_mesure=$SS->h;
					}else{
						$SS->size_by='C';
						$SS->size_mesure=array($SS->w, $SS->h);
					}
				}else{
					$SS->size_by='P';
					$SS->size_mesure=$SS->percent;
				}
			}
			if(isset($SS->size_mesure)){
				$SS->start=memory_get_usage();
				$objR = new SuperSizer($SS);
				$SS->end=memory_get_usage();
				$SS->used=$SS->end - $SS->start;
			}
			if($SS->es){
				if(isset($objR->errors)&&$objR->errors !=''){
					foreach($objR->errors as $e){
						echo $e;
					}
				}
			}
			if($SS->debug){
				echo '<h2>image object</h2><pre>'; print_r($objR); echo '</pre><h2>Original Image</h2><pre>'; print_r($objR->findResourceDetails($objR->resOrgImg)); echo '</pre><h2>Resized Image</h2><pre>'; if(isset($objR->resRImg)){print_r($objR->findResourceDetails($objR->resRImg));} echo '</pre><h2>Params</h2><pre>'; print_r($p); echo '</pre>';
			}
			if($SS->no_output){return false;}

			
			if(($SS->passthru==true&&!$SS->b64)||$SS->overwrite==true||$SS->to!=false){
				if($SS->bustcache==true){
					$SS->path_orgin=$SS->path_orgin."?".@filemtime($SS->path_orgin);
				}
				if($SS->overwrite==true||$SS->to!=false){ // removed $objR->resRImg==''|| as I'm unsure in what drunken state I woud have added that.
					if($SS->url!=true){
						if($SS->assign!=''){
							$smarty->assign($SS->assign, "<img src=\"{$SS->path_orgin}\" {$SS->title} {$SS->id} {$SS->class} alt=\"{$SS->alt}\" {$SS->style} {$SS->w_attr} {$SS->h_attr} {$SS->usemap} />");
						}else{
							echo "<img src=\"{$SS->path_orgin}\" {$SS->title} {$SS->id} {$SS->class} alt=\"{$SS->alt}\" {$SS->style} {$SS->w_attr} {$SS->h_attr} {$SS->usemap} />";
						}
					}else{
						if($SS->assign!=''){
							$smarty->assign($SS->assign,$SS->path_orgin);
						}else{
							echo $SS->path_orgin;
						}
					}
					return false;
				}
			}
		}
		if($SS->no_output){return false;}
		if($SS->url==true&&$SS->b64){echo 'you may not use both the url and base64 params';}
		$ie_match=preg_match('/MSIE ([0-8]\.[0-8])/',$_SERVER['HTTP_USER_AGENT'],$reg);
		$ie_match=$ie_match>0?true:false;
		$SS->fileLINK=$SS->bustcache==true&&(!$SS->b64||$ie_match)?$SS->fileLINK."?".@filemtime($SS->filename):$SS->fileLINK;
		if($ie_match){
			if($SS->b64===true||$SS->b64=='$SS->b64'){
				$SS->url=true;
			}
			$SS->b64=false;
		}
		if($SS->b64){
			if(!file_exists(_PS_UPLOAD_DIR_."/SuperSizerTmp/b64/".$SS->subdir)){
				mkdir(_PS_UPLOAD_DIR_."/SuperSizerTmp/b64/".$SS->subdir, 0777, true);
			}	
			$SS->path="/uploads/SuperSizerTmp/b64/".$SS->prefix.$SS->filebasename.$SS->suffix.'.tmp';
			$SS->fileLINK=$SS->passthru==true?$SS->path_orgin:$SS->fileLINK;
			if(!file_exists(_PS_ROOT_DIR_.$SS->path)||$SS->dynamic==true){
				if (!function_exists('copy')) {
					copy_alt($SS->fileLINK,_PS_ROOT_DIR_.$SS->path,'/');
				}else{
					copy($SS->fileLINK,_PS_ROOT_DIR_.$SS->path);
				}
				$data=base64_encode(file_get_contents($SS->fileLINK));
				// and put it out there
				$fn = _PS_ROOT_DIR_.$SS->path;
				$fp = fopen($fn, "w");
				if (!$fp) {
					return 0;
				}
				fwrite($fp, $data);
				fclose($fp);
			}else{
				$data=file_get_contents(_PS_ROOT_DIR_.$SS->path);
			}
			if($SS->b64==='tag'){
				$img= @getimagesize($fileLINK);
				if($SS->alt==''){$alt=$fileLINK;}
				$obj="<img src='data:".$img['mime'].";base64,".$data."' {$SS->title} {$SS->id} {$SS->class} alt=\"{$SS->alt}\" {$SS->style} {$SS->w_attr} {$SS->h_attr} {$SS->usemap} />";
			}else if($SS->b64==='stright'){
				$obj=$data;
			}else if($SS->b64===true){
				$img= @getimagesize($SS->fileLINK);
				$obj="data:".$img['mime'].";base64,".$data;
			}
			
		}else if($SS->url!=true){
			$SS->fill_attr = isset($p['fill_attr']) ? $p['fill_attr']:false;
			if($SS->fill_attr==true&&$SS->w_attr==''&&$SS->h_attr==''){
				if(isset($objR->resRImg)){
					$RImage=$objR->findResourceDetails($objR->resRImg);
					$SS->w_attr = isset($RImage[0])?' width="'.$RImage[0].'"':'';
					$SS->h_attr = isset($RImage[1])?' height="'.$RImage[1].'"':'';
				}else{
					$RImage=@getimagesize($SS->fileLINK);
					$SS->w_attr = isset($RImage[0])?' width="'.$RImage[0].'"':'';
					$SS->h_attr = isset($RImage[1])?' height="'.$RImage[1].'"':'';
				}
			}
			$obj="<img src=\"{$SS->fileLINK}\" {$SS->title} {$SS->id} {$SS->class} alt=\"{$SS->alt}\" {$SS->style} {$SS->w_attr} {$SS->h_attr} {$SS->usemap} />";
		}else{
			$obj=$SS->fileLINK;
		}
		if($SS->assign!=''){
			$smarty->assign($SS->assign,$obj);
		}else if($SS->return===true){
			return $obj;
		}else{
			echo $obj;
		}
		if(isset($SS->auto_memory)&&$SS->auto_memory){
			resetMemory($SS->old_limit);
			$SS->reset_limit=get_memory();
		}
		if($SS->debug){print_r($SS);}
		if(isset($SS))unset($SS);
		if(isset($objR))unset($objR);
		memory_get_usage(true);
		restore_error_handler();
		return;
}


function smarty_cms_help_function_supersizer() {
	echo'<fieldset style=" color:#333333; background-color:#FFCC00;"><img src="http://www.corbensproducts.com/uploads/module/supersizer/superSizer300x225.min.png" style="float:left; padding-right:35px;" />';
	echo'<form action="" method="post">
<input type="hidden" name="clearcache" value="1"><input name="" type="submit" value="Clear the cache" />
</form>';
	if(isset($_POST['clearcache'])&&$_POST['clearcache']==1){
		$objR = new SuperSizer();
		$dirpath=_PS_UPLOAD_DIR_."/SuperSizerTmp/";
		$deleted = $objR->recursive_rmdir($dirpath);
		if($deleted){
			echo "<h2>Cleared $dirpath</h2>";
		}else{
			echo "<h2>Sorry: $dirpath<br/><font color=\"red\"><strong>DOES NOT yet exist run the plug-in on an image first.</strong></font><br/></h2>";
		}
	}else{
		echo "<br/><span style=\"color:#0066CC; font-weight:900;\">Your cache path is: "._PS_UPLOAD_DIR_."/SuperSizerTmp/</span>";
	}
	
	?>
    

	<h3>What does this tag do?</h3>
	<ul>
	<li>Creates a resized version of an image on the fly.</li>
    <li>Creates a cached version of the image to be served.</li>
    <li>Supporting <strong>Subdomains, Caching, filters and more</strong></li>
    <li>For a small slow changing sites, to huge user based sites<strong> GAIN FULL CONTROL!!!!</strong></li>
	</ul>
    </fieldset>
	<h3>How do I use this tag?</h3>
<h2><a href="http://wiki.cmsmadesimple.org/index.php/User_Handbook/Admin_Panel/Tags/supersizer#How_do_I_use_this_tag.3F" target="_blank">Get Detailed useage here.</a>
</h2>
<h3>What parameters does it take?</h3>
<h2><a href="http://wiki.cmsmadesimple.org/index.php/User_Handbook/Admin_Panel/Tags/supersizer#What_parameters_does_it_take.3F" target="_blank">Get Detailed parameters here.</a>
</h2>
   
<br /><br />
<h3>Why so little help here?</h3>
<h4>to keep it light and fast. <a href="http://wiki.cmsmadesimple.org/index.php/User_Handbook/Admin_Panel/Tags/supersizer" target="_blank"> Use wiki for help.</a>
</h4>
<h4>Also On:<br/>
 <a href="http://groups.google.com/group/supersizer?hl=en" target="_blank">
 <img src="http://groups.google.com/intl/en/images/logos/groups_logo_sm.gif" style="padding-right:35px;" />
 </a><br/>
and<br/>
 <a href="http://forum.cmsmadesimple.org/index.php/topic,38094.0.html" target="_blank">
 <img src="http://www.cmsmadesimple.org/images/cmsmslogo.gif" style="padding-right:35px;" />
 </a>


</h4>
<br /><br />  
   
    <p><strong>Author:</strong> jeremyBass &lt;jeremybass@cableone.net&gt;<br />
    <strong>Website:</strong> <a href="http://www.corbensproducts.com" target="_blank">CorbensProducts.com</a><br />
    <strong>Support more mods like this:</strong><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="8817675">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form><br/>

    <strong>Version:</strong> BETA 0.9.6, 4.23.2010</p>
	<?php
}

function smarty_cms_about_function_supersizer() {
	?>
	<p>Author: jeremyBass &lt;jeremybass@cableone.net&gt;<br />
	<br />
    Version: BETA 0.9.6, 4.23.2010</p>
	<?php
}
?>
