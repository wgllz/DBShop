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
 * 其他涉及上传的地方（因为没必要单独用文件表示所以写在一起）
 */
class OtherUploadService
{
    private $upload;
    
    public function __construct()
    {
        if(!$this->upload) {
            $this->upload = new ImageUpload();
        }
    }
    /**
     * 附件设置之友情链接图片上传
     * @param string $imageName
     * @return unknown
     */
    public function linksWebnameLogoUpload($imageName, $oldImage=null, $imageWidth=null, $imageHeight=null)
    {
        $imageArray = array(
                'save_path' => '/public/upload/links/',
                'image_name' => $imageName
        );
        $imageInfo = $this->upload->uploadImage($imageArray);
        $imageInfo['image'] = $this->delOldUpload($imageInfo['image'], $oldImage);
        
        return $imageInfo;
    }
    /**
     * 广告图片上传
     * @param unknown $imageName
     * @param string $oldImage
     * @param string $imageWidth
     * @param string $imageHeight
     * @return Ambigous <string, string>
     */
    public function adImageUpload($imageName, $oldImage=null, $imageWidth=null, $imageHeight=null, $adId=null)
    {
        $imageArray = array(
                'save_path'    => '/public/upload/ad/' . ($adId != '' ? $adId . '/' : ''),
                'image_name'   => $imageName,
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
     * 会员头像上传
     * @param unknown $userId
     * @param unknown $imageName
     * @param string $oldImage
     * @param string $imageWidth
     * @param string $imageHeight
     * @return Ambigous <string, string>
     */
    public function userAvatarUpload($userId, $imageName, $oldImage=null,  $imageWidth=null, $imageHeight=null)
    {
        $imageArray = array(
                'save_path'    => '/public/upload/user/avatar/',
                'image_name'   => $imageName,
                'image_rename'  => $userId .'_' . time(),
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
     * 会员设置默认头像上传（客户设置）
     * @param unknown $imageName
     * @param string $oldImage
     * @param string $imageWidth
     * @param string $imageHeight
     * @return Ambigous <string, string>
     */
    public function userDefaultAvatar($imageName, $oldImage=null,  $imageWidth=null, $imageHeight=null)
    {
        $imageArray = array(
                'save_path'    => '/public/upload/common/',
                'image_name'   => $imageName,
                'image_rename'  => 'default_avatar',
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