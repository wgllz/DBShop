<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2015 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

class WaterMark
{
    const WATER_MARK_POS         = 6;            //水印位置
    const WATER_MARK_TRANS       = 90;           //水印透明度，数值越小越透明
    
    const WATER_MARK_TYPE        = 'text';       //水印类型，text为文字类型|image图片类型
    
    const WATER_MARK_FONT        = 'DBShop.net'; //默认水印文字内容
    const WATER_MARK_FONT_SIZE   = '6';         //默认水印文字大小
    const WATER_MARK_FONT_COLOR  = '#333399';    //默认水印颜色
    
    const WATER_MARK_IMAGE       = '';           //水印图片
    const WATER_MARK_IMAGE_WIDTH = '80';         //水印图片宽度
    const WATER_MARK_IMAGE_HEIGHT= '30';         //水印图片高度
    
    public static function output($input, $output = null, $options = null)
    {
        $water_mark_pos          = (isset($options['watermark_location'])     ? $options['watermark_location']     : self::WATER_MARK_POS);
        $water_mark_trans        = (isset($options['watermark_trans'])        ? $options['watermark_trans']        : self::WATER_MARK_TRANS);
        $water_mark_type         = (isset($options['watermark_type'])         ? $options['watermark_type']         : self::WATER_MARK_TYPE);
        $water_mark_font         = (isset($options['watermark_text'])         ? $options['watermark_text']         : self::WATER_MARK_FONT);
        $water_mark_font_size    = (isset($options['watermark_text_size'])    ? $options['watermark_text_size']    : self::WATER_MARK_FONT_SIZE);
        $water_mark_font_color   = (isset($options['watermark_text_color'])   ? $options['watermark_text_color']   : self::WATER_MARK_FONT_COLOR);
        $water_mark_image        = (isset($options['watermark_image'])        ? $options['watermark_image']        : self::WATER_MARK_IMAGE);
        $water_mark_image_width  = (isset($options['watermark_image_width'])  ? $options['watermark_image_width']  : self::WATER_MARK_IMAGE_WIDTH);
        $water_mark_image_height = (isset($options['watermark_image_height']) ? $options['watermark_image_height'] : self::WATER_MARK_IMAGE_HEIGHT);
        
        if($water_mark_type == 'image' and !file_exists($water_mark_image)) die('Watermark image can not be found');
        if(!function_exists('getimagesize')) die('the getImageSize can not be used');
        
        //检查GD支持的类型
        $gdAllowTypes = array();
        if (function_exists('imagecreatefromgif'))  $gdAllowTypes['image/gif']  = 'imagecreatefromgif';
		if (function_exists('ImagecreateFrompng'))  $gdAllowTypes['image/png']  = 'ImagecreateFrompng';
		if (function_exists('imagecreatefromjpeg')) $gdAllowTypes['image/jpeg'] = 'imagecreatefromjpeg';
        
		$imageInfo    = getimagesize($input);
		
		if($water_mark_type == 'image') {
		    $waterMarkImageInfo = getimagesize($water_mark_image);
		    if($imageInfo[0] < $waterMarkImageInfo[0] || $imageInfo[1] < $waterMarkImageInfo[1]) return ;
		    if(array_key_exists($imageInfo['mime'],$gdAllowTypes) and array_key_exists($waterMarkImageInfo['mime'],$gdAllowTypes)) {
		        $temp   = $gdAllowTypes[$imageInfo['mime']]($input);
		        $tempWm = $gdAllowTypes[$waterMarkImageInfo['mime']]($water_mark_image);
		    }
		} else {
		    $temp                  = $gdAllowTypes[$imageInfo['mime']]($input);
		    $tempWm                = $gdAllowTypes[$imageInfo['mime']]($input);
		    $imageTemp             = imagettfbbox(ceil($water_mark_font_size * 2), 0, __DIR__ . '/verdana.ttf', $water_mark_font);
		    $waterMarkImageInfo[0] = $imageTemp['2'] - $imageTemp['6'];
		    $waterMarkImageInfo[1] = $imageTemp['3'] - $imageTemp['7'];
		    unset($imageTemp);
		    if(empty($waterMarkImageInfo[0]) and empty($waterMarkImageInfo[1])) {
		        $waterMarkImageInfo[0] = strlen($water_mark_font) * 10;
		        $waterMarkImageInfo[1] = 20;
		    }
		}
		
		//水印所在图片位置
		switch ($water_mark_pos) {
		    case 1 ://顶部左边
		        $dstX = 10;
		        $dstY = 5;
		        break;
		    case 2 ://顶部中间
                $dstX = ($imageInfo[0] - $waterMarkImageInfo[0]) / 2;
                $dstY = 5;
		        break;
		    case 3 ://顶部右边
		        $dstX = $imageInfo[0] - $waterMarkImageInfo[0] - 20;
		        $dstY = 5;
		        break;
		    case 4 ://中部左边
		        $dstX = 10;
		        $dstY = ($imageInfo[1] - $waterMarkImageInfo[1])/2;
		        break;
		    case 5 ://中部中间
		        $dstX = ($imageInfo[0] - $waterMarkImageInfo[0])/2;
		        $dstY = ($imageInfo[1] - $waterMarkImageInfo[1])/2;
		        break;
		    case 6 ://中部右边
		        $dstX = $imageInfo[0] - $waterMarkImageInfo[0] - 20;
		        $dstY = ($imageInfo[1] - $waterMarkImageInfo[1])/2;    
		        break;
		    case 7 ://底部左边
		        $dstX = 10;
		        $dstY = $imageInfo[1] - $waterMarkImageInfo[1] - 10;
		        break;
		    case 8 ://底部中间
		        $dstX = ($imageInfo[0] - $waterMarkImageInfo[0])/2;
		        $dstY = $imageInfo[1] - $waterMarkImageInfo[1] - 10;
		        break;
		    case 9 ://底部右边
		        $dstX = $imageInfo[0] - $waterMarkImageInfo[0] - 20;
		        $dstY = $imageInfo[1] - $waterMarkImageInfo[1] - 10;    
		        break;
		    default://随机位置
		        $dstX = mt_rand(0, $imageInfo[0] - $waterMarkImageInfo[0]);
		        $dstY = mt_rand(0, $imageInfo[1] - $waterMarkImageInfo[1]);      
		       break;
		}
		
		if(function_exists('imagealphablending')) imagealphablending($tempWm, TRUE); //设定图像混色模式
		if(function_exists('imagesavealpha'))     imagesavealpha($tempWm,     TRUE); //保存完整的 alpha 通道信息
		
		//为图片添加水印
		if($water_mark_type == 'image') {
		    if(function_exists('imagecopymerge')) {
		        imagecopymerge($temp, $tempWm, $dstX, $dstY, 0, 0, $waterMarkImageInfo[0], $waterMarkImageInfo[1], $water_mark_trans);
		    }
		} else {
		    if(!empty($water_mark_font_color) and strlen($water_mark_font_color) == 7) {
		        $R = hexdec(substr($water_mark_font_color, 1, 2));
		        $G = hexdec(substr($water_mark_font_color, 3, 2));
		        $B = hexdec(substr($water_mark_font_color, 5));
		    } else {
		        return ;
		    }
		    
		    if(empty($waterMarkImageInfo[0]) and empty($waterMarkImageInfo[1])) {
		        imagettftext($temp, $water_mark_font_size, 0, $dstX, $dstY, imagecolorallocate($temp, $R, $G, $B), __DIR__ . '/verdana.ttf', $water_mark_font);
		    } else {
		        imagestring($temp, $water_mark_font_size, $dstX, $dstY, $water_mark_font, imagecolorallocate($temp, $R, $G, $B));
		    }
		}
		
		//保存图片
		switch ($imageInfo['mime']) {
		    case 'image/jpeg' :
		    case 'image/pjpeg':
		   // case 'application/octet-stream':
		        imagejpeg($temp, $output);
		        break;
		    case 'image/png'  :
		    case 'image/x-png':
		        imagepng($temp, $output);
		        break;
		    case 'image/gif'  :
		        imagegif($temp, $output);
		        break;
		}
		
		imagedestroy($temp);
		imagedestroy($tempWm);
		return $output;
    }
}

?>