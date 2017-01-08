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
use OSS\Core\OssException;
use OSS\OssClient;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

/**
 * 模块Goods中的附件上传处理
 */
class GoodsUploadService
{
    private $upload;
    private $uploadConfig;
    private $storageConfig;

    public function __construct()
    {
        if(!$this->upload) {
            $this->upload = new ImageUpload();
        }
        if(!$this->uploadConfig) {
            $configReader = new \Zend\Config\Reader\Ini();
            $this->uploadConfig  = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Goods.ini');
            $this->storageConfig = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Storage.ini');
        }
    }
    /**
     * 商品编辑与添加中的商品图片上传
     * @param string $imageName
     * @return Ambigous <\Upload\Common\Service\multitype:string, multitype:string >
     */
    public function goodsMoreImageUpload ($imageName=null)
    {
        $imageArray = array(
            'save_path'   => ($this->storageConfig['storage_type'] == 'Qiniu' ? '/public/upload/goods/' : '/public/upload/goods/' . date("Ymd") . '/'),
            'image_name'  => $imageName,
            'image_rename'=> ($this->uploadConfig['goods']['goods_image_name_type'] == 'random' ? md5(time().rand(1, 100)) : '')
        );
        
        $thumbArray = array(
            'width' => $this->uploadConfig['goods']['goods_thumb_width'],
            'height'=> $this->uploadConfig['goods']['goods_thumb_heigth'],
            'crop'  => $this->uploadConfig['goods']['goods_image_crop'],
            'rename'=> true
        );
        
        $waterMarkState = ($this->uploadConfig['goods']['goods_watermark_state'] == 1 ? true : false);
        
        $imageInfo = $this->upload->uploadImage($imageArray, $thumbArray, $waterMarkState);
        
        return $imageInfo;
    }
    /**
     * 七牛云存储
     * @param $imageName
     * @return string
     */
    public function goodsMoreImageYunStorage($imageName)
    {
        if($this->storageConfig['storage_type'] == 'Qiniu') {
            $auth   = new Auth($this->storageConfig['qiniu_ak'], $this->storageConfig['qiniu_sk']);
            $bucket = $this->storageConfig['qiniu_space_name'];
            $token  = $auth->uploadToken($bucket, null, 3600);
            $uploadMgr = new UploadManager();
            $key    = basename($imageName);
            list($ret, $err) = $uploadMgr->putFile($token, $key, DBSHOP_PATH . $imageName);
            if($err !== null) {//当上传失败时，使用本地文件
                return $imageName;
            } else {//当上传成功时，使用云端文件并删除本地文件
                @unlink(DBSHOP_PATH . $imageName);
                return '{qiniu}/'.$ret['key'];
            }
        } elseif($this->storageConfig['storage_type'] == 'Aliyun') {
            $aliyunOssDomainType    =  $this->storageConfig['aliyun_domain_type'] == 'true' ? true : false;
            $aliyunOssDomain        = $this->storageConfig['aliyun_http_type'] . isset($this->storageConfig['aliyun_oss_domain']) ? $this->storageConfig['aliyun_oss_domain'] : str_replace($this->storageConfig['aliyun_space_name'].'.', '', $this->storageConfig['aliyun_domain']);
            try {
                $ossClient = new OssClient($this->storageConfig['aliyun_ak'], $this->storageConfig['aliyun_sk'], $aliyunOssDomain, $aliyunOssDomainType);
                $ossClient->uploadFile($this->storageConfig['aliyun_space_name'], basename($imageName), DBSHOP_PATH.$imageName);
                @unlink(DBSHOP_PATH . $imageName);
                return '{aliyun}/'.basename($imageName);
            }catch (OssException $e) {
                //print $e->getMessage();
                return $imageName;
            }
        }
        return $imageName;
    }
    /**
     * 商品分类中的分类ICO上传
     * @param string $icoName
     */
    public function classIcoUpload ($icoName=null, $oldImage=null)
    {
        $imageArray = array(
            'save_path'  => '/public/upload/class/',
            'image_name' => $icoName
        );
        $thumbArray = array(
            'width'  => $this->uploadConfig['class']['class_ico_width'],
            'height' => $this->uploadConfig['class']['class_ico_height'],
            'crop'   => $this->uploadConfig['class']['class_ico_crop'],
        );
        
        $icoInfo    = $this->upload->uploadImage($imageArray,$thumbArray);
        $icoInfo['image'] = $this->delOldUpload($icoInfo['image'], $oldImage);
        
        return $icoInfo;
    }
    /**
     * 商品分类中image上传
     * @param string $imageName
     * @return Ambigous <\Upload\Common\Service\multitype:string, multitype:string >
     */
    public function classImageUpload ($imageName=null, $oldImage=null)
    {
        $imageArray      = array(
            'save_path' => '/public/upload/class/',
            'image_name' => $imageName);
        $thumbArray = array(
            'width'  => $this->uploadConfig['class']['class_image_width'],
            'height' => $this->uploadConfig['class']['class_image_height'],
            'crop'  => $this->uploadConfig['class']['class_image_crop'],
        );
        $classImageInfo  = $this->upload->uploadImage($imageArray,$thumbArray);
        $classImageInfo['image'] = $this->delOldUpload($classImageInfo['image'], $oldImage);
        
        return $classImageInfo;
    }
    /**
     * 商品品牌上传logo
     * @param string $logoName
     * @return Ambigous <\Upload\Common\Service\multitype:string, multitype:string >
     */
    public function brandLogoUpload ($logoName=null, $oldImage=null)
    {
        $imageArray = array(
            'save_path'  => '/public/upload/brand/',
            'image_name' => $logoName
        );
        $thumbArray = array(
            'width'  =>$this->uploadConfig['brand']['brand_logo_width'],
            'height' =>$this->uploadConfig['brand']['brand_logo_height'],
            'crop'  => $this->uploadConfig['brand']['brand_logo_crop'],
        );
        
        $logoInfo   = $this->upload->uploadImage($imageArray,$thumbArray);
        $logoInfo['image'] = $this->delOldUpload($logoInfo['image'], $oldImage);
        
        return $logoInfo;
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