<?php


namespace led;

class ToolScreen {
	
	/**
	 * 处理 jpg  gif bmp png 格式图片为指定宽高24位bmp格式图片
	 * 
	 * @param string $image_from 原始图片路径
	 * @param string $image_to 生成bmp图片保存路径
	 * @param int $new_width 生成bmp图片宽度
	 * @param int $new_height 生成bmp 图片高度
	 * @param string handle 处理方式 zoom 等比缩放 cut 裁剪
	 * 
	 * @return bmp 图片路径
	 */
	public static function image_resize( $image_from, $image_to, $new_width = 64, $new_height = 64, $handle = 'zoom') {
    
	    //if(!file_exists($image_from)) return false;
	   
	    //$e = strtolower(substr($image_from,strrpos($image_from,".")+1,3));

	    $imageinfo = getimagesize($image_from);

	    if ($imageinfo['mime'] == 'image/jpeg') {
	        $image = ImageCreateFromJpeg($image_from);
	    } elseif ($imageinfo['mime'] == 'image/gif') {
	        $image = ImageCreateFromGif($image_from);
	    } elseif ($imageinfo['mime'] == 'image/x-ms-bmp' || $e == "bmp") {
	        $image = self::imagecreatefrombmp($image_from);
	    } elseif ($imageinfo['mime'] == 'image/png') {
	        $image = ImageCreateFromPng($image_from);
	    } else {
	        return false;
	    }
	    
	    if(!$image) return false;
	    
	    // 获取新的尺寸
	    list($width, $height) = getimagesize($image_from);
	    // 重新取样
	    $image_p = ImageCreateTrueColor($new_width, $new_height);
	    imagecolorallocate($image_p, 0 , 0 , 0);
	    
	    $path = '';
	    if( $image_p ) {
			
			if($handle == 'cut' && $width > $new_width && $height > $new_height){ // 裁剪  原图宽高大于设备宽高
				$width = $new_width;
				$height = $new_height;
			}
	        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	        
	        // 输出
	        $g = strtolower(substr($image_to,strrpos($image_to,".")+1,3));
	        
	        if ($g == "bmp") {
	            $path = self::imagebmp($image_p, $image_to , 24);
	        }
	        imagedestroy($image_p);
	    }
	    imagedestroy($image);
	    return $path;
	}
	
	public static function imagecreatefrombmp($filename) {
		
	    if ( !$f1 = fopen( $filename, "rb" ) ) return FALSE;

    	$FILE = unpack( "vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread( $f1, 14 ) );
    	if ( $FILE['file_type'] != 19778 ) return FALSE;

    	$BMP = unpack( 'Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread( $f1, 40 ) );
    	$BMP['colors'] = pow( 2, $BMP['bits_per_pixel'] );
	    if ( $BMP['size_bitmap'] == 0 )
	        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
	    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
	    $BMP['bytes_per_pixel2'] = ceil( $BMP['bytes_per_pixel'] );
	    $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
	    $BMP['decal'] -= floor( $BMP['width'] * $BMP['bytes_per_pixel'] / 4 );
	    $BMP['decal'] = 4 - (4 * $BMP['decal']);
	    if ( $BMP['decal'] == 4 )
	        $BMP['decal'] = 0;
	
	    $PALETTE = array();
	    if ( $BMP['colors'] < 16777216 ){
	        $PALETTE = unpack( 'V' . $BMP['colors'], fread( $f1, $BMP['colors'] * 4 ) );
	    }
	
	    $IMG = fread( $f1, $BMP['size_bitmap'] );
	    $VIDE = chr( 0 );

	    $res = imagecreatetruecolor( $BMP['width'], $BMP['height'] );
	    $P = 0;
	    $Y = $BMP['height'] - 1;
	    while( $Y >= 0 ){
	        $X = 0;
	        while( $X < $BMP['width'] ){
	            if ( $BMP['bits_per_pixel'] == 32 ){
	                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) );
	                $B = ord(substr($IMG, $P,1));
	                $G = ord(substr($IMG, $P+1,1));
	                $R = ord(substr($IMG, $P+2,1));
	                $color = imagecolorexact( $res, $R, $G, $B );
	                if ( $color == -1 )
	                    $color = imagecolorallocate( $res, $R, $G, $B );
	                $COLOR[0] = $R*256*256+$G*256+$B;
	                $COLOR[1] = $color;
	            }elseif ( $BMP['bits_per_pixel'] == 24 ){
	                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) . $VIDE );
	                
	            }elseif ( $BMP['bits_per_pixel'] == 16 ){
	                $COLOR = unpack( "n", substr( $IMG, $P, 2 ) );
	                
	                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
	                
	            }elseif ( $BMP['bits_per_pixel'] == 8 ){
	                $COLOR = unpack( "n", $VIDE . substr( $IMG, $P, 1 ) );
	                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
	            }elseif ( $BMP['bits_per_pixel'] == 4 ){
	                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
	                if ( ($P * 2) % 2 == 0 )
	                    $COLOR[1] = ($COLOR[1] >> 4);
	                else
	                    $COLOR[1] = ($COLOR[1] & 0x0F);
	                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
	            }elseif ( $BMP['bits_per_pixel'] == 1 ){
	                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
	                if ( ($P * 8) % 8 == 0 )
	                    $COLOR[1] = $COLOR[1] >> 7;
	                elseif ( ($P * 8) % 8 == 1 )
	                    $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
	                elseif ( ($P * 8) % 8 == 2 )
	                    $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
	                elseif ( ($P * 8) % 8 == 3 )
	                    $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
	                elseif ( ($P * 8) % 8 == 4 )
	                    $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
	                elseif ( ($P * 8) % 8 == 5 )
	                    $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
	                elseif ( ($P * 8) % 8 == 6 )
	                    $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
	                elseif ( ($P * 8) % 8 == 7 )
	                    $COLOR[1] = ($COLOR[1] & 0x1);
	                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
	            }else
	                return FALSE;
	            imagesetpixel( $res, $X, $Y, $COLOR[1] );
	            $X++;
	            $P += $BMP['bytes_per_pixel'];
	        }
	        $Y--;
	        $P += $BMP['decal'];
	    }
	    fclose( $f1 );
	
	    return $res;
	} 
	
	/**  
	* 创建bmp格式图片  
	*  
	* @author: legend(legendsky@hotmail.com)  
	* @link: http://www.ugia.cn/?p=96  
	* @description: create Bitmap-File with GD library  
	* @version: 0.1  
	*  
	* @param resource $im             图像资源  
	* @param string      $filename       如果要另存为文件，请指定文件名，为空则直接在浏览器输出  
	* @param integer $bit            图像质量(1、4、8、16、24、32位)  
	* @param integer $compression 压缩方式，0为不压缩，1使用RLE8压缩算法进行压缩  
	*  
	* @return integer  
	*/   
	public static function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0){
		   
       if (!in_array($bit, array(1, 4, 8, 16, 24, 32))){
           $bit = 8;   
       }   
       else if ($bit == 32){   
           $bit = 24;   
       }
       $bits = pow(2, $bit);   
       
       // 调整调色板   
       imagetruecolortopalette($im, true, $bits);   
       $width = imagesx($im);   
       $height = imagesy($im);   
       $colors_num = imagecolorstotal($im);
       $rgb_quad = '';   
       if ($bit <= 8)   
       {   
           // 颜色索引   
           //$rgb_quad = '';   
           for ($i = 0; $i < $colors_num; $i ++)   
           {    
               $colors = imagecolorsforindex($im, $i);   
               $rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";   
           }   
          
           // 位图数据   
           $bmp_data = '';   
  
  
           // 非压缩   
           if ($compression == 0 || $bit < 8)   
           {   
               if (!in_array($bit, array(1, 4, 8)))   
               {   
                   $bit = 8;   
               }   
  
  
               $compression = 0;   
              
               // 每行字节数必须为4的倍数，补齐。   
               $extra = '';   
               $padding = 4 - ceil($width / (8 / $bit)) % 4;   
               if ($padding % 4 != 0)   
               {   
                   $extra = str_repeat("\0", $padding);   
               }   
              
               for ($j = $height - 1; $j >= 0; $j --)   
               {   
                   $i = 0;   
                   while ($i < $width)   
                   {   
                       $bin = 0;   
                       $limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;   
  
  
                       for ($k = 8 - $bit; $k >= $limit; $k -= $bit)   
                       {   
                           $index = imagecolorat($im, $i, $j);   
                           $bin |= $index << $k;   
                           $i ++;   
                       }   
  
  
                       $bmp_data .= chr($bin);   
                   }   
                  
                   $bmp_data .= $extra;   
               }   
           }   
           // RLE8 压缩   
           else if ($compression == 1 && $bit == 8)   
           {   
               for ($j = $height - 1; $j >= 0; $j --)   
               {   
                   $last_index = "\0";   
                   $same_num      = 0;   
                   for ($i = 0; $i <= $width; $i ++)   
                   {   
                       $index = imagecolorat($im, $i, $j);   
                       if ($index !== $last_index || $same_num > 255)   
                       {   
                           if ($same_num != 0)   
                           {   
                               $bmp_data .= chr($same_num) . chr($last_index);   
                           }   
  
  
                           $last_index = $index;   
                           $same_num = 1;   
                       }   
                       else   
                       {   
                           $same_num ++;   
                       }   
                   }   
  
  
                   $bmp_data .= "\0\0";   
               }   
              
               $bmp_data .= "\0\1";   
           }   
  
  
           $size_quad = strlen($rgb_quad);   
           $size_data = strlen($bmp_data);   
       }   
       else   
       {
           // 每行字节数必须为4的倍数，补齐。   
           $extra = '';   
           $padding = 4 - ($width * ($bit / 8)) % 4;   
           if ($padding % 4 != 0)   
           {   
               $extra = str_repeat("\0", $padding);   
           }   
  
  
           // 位图数据   
           $bmp_data = '';   
  
  
           for ($j = $height - 1; $j >= 0; $j --)   
           {   
               for ($i = 0; $i < $width; $i ++)   
               {   
                   $index = imagecolorat($im, $i, $j);   
                   $colors = imagecolorsforindex($im, $index);   
                  
                   if ($bit == 16)   
                   {   
                       $bin = 0 << $bit;   
  
  
                       $bin |= ($colors['red'] >> 3) << 10;   
                       $bin |= ($colors['green'] >> 3) << 5;   
                       $bin |= $colors['blue'] >> 3;   
  
  
                       $bmp_data .= pack("v", $bin);   
                   }   
                   else   
                   {   
                       $bmp_data .= pack("c*", $colors['blue'], $colors['green'], $colors['red']);   
                   }   
                  
                   // todo: 32bit;   
               }
               $bmp_data .= $extra;   
           }   
  
  
           $size_quad = 0;   
           $size_data = strlen($bmp_data);   
           $colors_num = 0;   
       }   
  
  
       // 位图文件头   
       $file_header = "BM" . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);   
  
       // 位图信息头   
       $info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);   
      
       // 写入文件   
       if ($filename != '')   
       {   
           $fp = fopen($filename , "wb");
           fwrite($fp, $file_header);   
           fwrite($fp, $info_header);   
           fwrite($fp, $rgb_quad);   
           fwrite($fp, $bmp_data);   
           fclose($fp);

           return $filename;
       }
       
       return 1;   
	}

    public static function zipBinary($binary){

        $len = strlen($binary);
        // 压缩二进制流
        $gzbuf = gzcompress($binary , 9);
        // 添加4byte原始数据长度
        $shiftBytes = self::getBytes($len);
        // 位移byte转成二进制
        $shift24Binary = self::toStr($shiftBytes);

        $binarys = $shift24Binary . $gzbuf;

        unset($gzbuf , $binary);

        return $binarys;
    }

    public static function getBytes($len) {
        $bytes = array($len & 0xff , $len >> 8 , $len >> 16 , $len >> 24);
        return $bytes;
    }

    public static function toStr($bytes) {
        $str = '';
        foreach($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }
}