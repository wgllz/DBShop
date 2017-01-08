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

namespace Upload\Common\Service;

/**
 * 系统设置中涉及到的上传操作
 */
class SystemUploadService
{
    private $upload;
    
    public function __construct()
    {
        if(!$this->upload) {
            $this->upload = new ImageUpload();
        }
    }
    /**
     * 附件设置之商品默认图片上传
     * @param unknown $imageName
     * @param unknown $oldImage
     * @return Ambigous <\Upload\Common\Service\multitype:string, multitype:string >
     */
    public function systemGoodsDefaultUpload($imageName, $oldImage=null, $imageWidth=null, $imageHeight=null)
    {
        $imageArray = array(
                'save_path'     => '/public/upload/common/',
                'image_name'    => $imageName,
                'image_rename'  => 'shop_goods_' . time(),
        );
        $thumbArray = array(
                'width'  => $imageWidth,
                'height' => $imageHeight,
                'crop'   => 'false',
        );
        
        $imageInfo = $this->upload->uploadImage($imageArray, $thumbArray);
        $imageInfo['image'] = $this->delOldUpload($imageInfo['image'], $oldImage);
        
        return $imageInfo;
    }
    /** 
     * 附件上传之品牌默认图片
     * @param unknown $imageName
     * @param string $oldImage
     * @param string $imageWidth
     * @param string $imageHeight
     * @return Ambigous <string, string>
     */
    public function systemBrandDefaultUpload ($imageName, $oldImage=null, $imageWidth=null, $imageHeight=null)
    {
        $imageArray = array(
                'save_path'    => '/public/upload/common/',
                'image_name'   => $imageName,
                'image_rename' => 'shop_brand_' . time(),
        );
        $thumbArray = array(
                'width'  => $imageWidth,
                'height' => $imageHeight,
                'crop'   => 'false',
        );
        
        $imageInfo = $this->upload->uploadImage($imageArray, $thumbArray);
        $imageInfo['image'] = $this->delOldUpload($imageInfo['image'], $oldImage);
        
        return $imageInfo;
    }
    /**
     * 附件设置之水印图片上传
     * @param string $imageName
     * @param array $thumbArray
     * @return Ambigous <\Upload\Common\Service\multitype:string, multitype:string >
     */
    public function systemWatermarkImageUpload($imageName=null, $oldImage=null, array $thumbArray=null)
    {
        $imageArray = array(
                'save_path'    => '/public/upload/common/',
                'image_name'   => $imageName,
                'image_rename' => 'watermark_' . time(),
        );
        $thumbArray['crop'] = 'false';
        
        $imageInfo = $this->upload->uploadImage($imageArray,$thumbArray);
        $imageInfo['image'] = $this->delOldUpload($imageInfo['image'], $oldImage);
        
        return $imageInfo;
    }
    /** 
     * 系统设置之前台logo上传
     * @param unknown $imageName
     * @param unknown $imageWidth
     * @param unknown $imageHeight
     * @return Ambigous <\Upload\Common\Service\multitype:string, multitype:string >
     */
    public function systemWebLogoUpload($imageName, $oldImage=null, $imageWidth=null, $imageHeight=null)
    {
        $imageArray = array(
                'save_path'    => '/public/upload/common/',
                'image_name'   => $imageName,
                'image_rename' => 'shop_logo_' . time(),
        );
        $thumbArray = array(
                //'width'  => $imageWidth,
                //'height' => $imageHeight,
                //'crop'   => 'false',
        );
        
        $imageInfo = $this->upload->uploadImage($imageArray, $thumbArray);
        $imageInfo['image'] = $this->delOldUpload($imageInfo['image'], $oldImage);
        
        return $imageInfo;
    }
    /**
     * 系统设置之前台网站ico图片上传
     * @param unknown $imageName
     * @param string $oldImage
     * @param string $imageWidth
     * @param string $imageHeight
     * @return Ambigous <string, string>
     */
    public function systemWebIcoUpload($imageName, $oldImage=null, $imageWidth=null, $imageHeight=null)
    {
        $imageArray = array(
                'save_path'    => '/public/upload/common/',
                'image_name'   => $imageName,
                'image_rename' => 'favicon_' . time(),
        );
        $thumbArray = array(
                'width'  => $imageWidth,
                'height' => $imageHeight,
                'crop'   => 'false',
        );
        
        $imageInfo = $this->upload->uploadImage($imageArray, $thumbArray);
        $imageInfo['image'] = $this->delOldUpload($imageInfo['image'], $oldImage);
        
        return $imageInfo;        
    }
    /**
     * 对新旧图片的对比并删除旧有图片
     * @param string $newImage
     * @param string $oldImage
     * @return string
     */
    private function delOldUpload($newImage=null, $oldImage=null)
    {
        $image = '';
        if(!empty($newImage)) {
            $image = $newImage;
            if(isset($oldImage) and !empty($oldImage) and $newImage != $oldImage) @unlink(DBSHOP_PATH . $oldImage);
        } else {
            if(!empty($oldImage)) $image = $oldImage;
        }
        return $image;
    }
}

?>