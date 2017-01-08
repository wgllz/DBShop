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

class ImageUpload
{
    private $upAdapter;
    private $watermarkConfig;
    private $uploadConfig;
    public function __construct()
    {
        if(!$this->upAdapter) {
            $this->osCheckUploadfileName();
            $this->upAdapter  = new \Zend\File\Transfer\Adapter\Http();
        }
        if(!$this->watermarkConfig) {
            $configReader = new \Zend\Config\Reader\Ini();
            $this->watermarkConfig = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Watermark.ini');
        }
        if(!$this->uploadConfig) {
            if(!isset($configReader)) {
                $configReader = new \Zend\Config\Reader\Ini();
            }
            $this->uploadConfig = $configReader->fromFile(DBSHOP_PATH . '/data/moduledata/Upload/Upload.ini');
        }
    }
    /**
     * 图片上传
     * @param array $imageArray     image_name 上传的图片名称 、 image_rename 是否重命名（TRUE 或 FALSE）
     * @param array $thumbArray     width 缩微图宽度 、height 缩微图高度 、rename 缩微图是否重命名 、crop 是否裁切（TRUE 或 FALSE）
     * @param string $waterMark
     * @return multitype:string
     */
    public function uploadImage(array $imageArray,array $thumbArray=null,$waterMark=FALSE)
    {   
        $returnArray = array('image'=>'');
        $imageInfo   = $this->upAdapter->getFileInfo();
        $savePath    = $imageArray['save_path'];
        $imageName   = $imageInfo[$imageArray['image_name']]['name'];
        $imageExt    = $this->_getExtension($imageInfo[$imageArray['image_name']]['name']);
        //所以注释掉是和下面52行是属于同一操作，因为与从写的重命名操作有冲突，所以新加了上面两行拆分
        //$imageName   = (isset($imageArray['image_rename']) and $imageArray['image_rename'] != '') ? $imageArray['image_rename'] . '.' . $this->_getExtension($imageInfo[$imageArray['image_name']]['name']) : $imageInfo[$imageArray['image_name']]['name'];
        
        if(isset($imageInfo[$imageArray['image_name']]['name']) and !empty($imageInfo[$imageArray['image_name']]['name'])) {
            
            //是否重命名,使用这个的问题在于当同时上传两张图片且重命名时，第二张是无法上传成功的，还没有找到好的解决方法，所以在下面会有重写部分
            /*if(isset($imageArray['image_rename']) and $imageArray['image_rename'] != '') {
                $this->upAdapter->addFilter('Rename', array('target'=>$imageName, 'overwrite'=>true));
            }*/
            //大小检测
            $this->upAdapter->addValidator('Size', FALSE, array( 'max' => $this->uploadConfig['image']['upload_image_max'] . 'kB', 'messages'=>array(
                \Zend\Validator\File\Size::NOT_FOUND => "文件'%value%'无法读取或不存在",
                \Zend\Validator\File\Size::TOO_BIG   => "文件'%value%'的大小'%size%'超出，最大允许'%max%'",
                \Zend\Validator\File\Size::TOO_SMALL => "文件'%value%'的大小'%size%'不足，至少需要'%min%'"
            )));
            //扩展名检测
            $this->upAdapter->addValidator('Extension', FALSE, array(@implode(',', array_filter($this->uploadConfig['image']['upload_image_type'])),'messages'=>array(
                \Zend\Validator\File\Extension::FALSE_EXTENSION => "文件'%value%'扩展名不允许",
                \Zend\Validator\File\Extension::NOT_FOUND       => "文件'%value%'无法读取或不存在"
            )));
            //检查是否为图片
            /*$this->upAdapter->addValidator('MimeType', false,array('messages'=>array(
                \Zend\Validator\File\MimeType::FALSE_TYPE    =>"文件'%value%'不是图片，检测到文件的媒体类型为'%type%'",
                \Zend\Validator\File\MimeType::NOT_DETECTED  => "文件'%value%'的媒体类型无法检测",
                \Zend\Validator\File\MimeType::NOT_READABLE  => "文件'%value%'无法读取或不存在"
            )));*/
            if(class_exists('finfo')) {//推荐的方式，准确的获取文件的图片类型
                $this->upAdapter->addValidator('IsImage', true,array('messages'=>array(
                    \Zend\Validator\File\IsImage::FALSE_TYPE    =>"文件'%value%'不是图片，检测到文件的媒体类型为'%type%'",
                    \Zend\Validator\File\IsImage::NOT_DETECTED  => "文件'%value%'的媒体类型无法检测",
                    \Zend\Validator\File\IsImage::NOT_READABLE  => "文件'%value%'无法读取或不存在"
                ),'magicFile'=>false));
            } else {//不推荐这样处理，但是有些环境就是没有开启finfo
                if(!$this->checkImageType($imageInfo[$imageArray['image_name']]['type'])) {
                    header("Content-type: text/html; charset=utf-8");
                    exit("文件'".$imageName."'不是图片，检测到文件的媒体类型为'" . $imageInfo[$imageArray['image_name']]['type'] . "'" . '<a href="' . $_SERVER['HTTP_REFERER'] . '">返回</a>');
                }
            }
            
            if(!$this->upAdapter->isValid($imageArray['image_name'])) {
                header("Content-type: text/html; charset=utf-8");
                exit(implode('<br>', $this->upAdapter->getMessages()) . '<a href="' . $_SERVER['HTTP_REFERER'] . '">返回</a>');
            }
            
            //检测图片存放目录，设定存放目录
            if(!is_dir(DBSHOP_PATH . $savePath)) {
                mkdir(rtrim(DBSHOP_PATH . $savePath,'/'),0755);
                chmod(DBSHOP_PATH . $savePath, 0755);
            }
            $this->upAdapter->setDestination(DBSHOP_PATH . $savePath);
            
            //上传处理
            if($this->upAdapter->receive($imageArray['image_name'])) {
                $returnArray['image'] = $savePath . $imageName;
                chmod(DBSHOP_PATH . $returnArray['image'], 0766);
            }
            
            //重命名
            if(isset($imageArray['image_rename']) and $imageArray['image_rename'] != '' and file_exists(DBSHOP_PATH . $returnArray['image'])) {
                if(rename(DBSHOP_PATH . $returnArray['image'], DBSHOP_PATH . $savePath . $imageArray['image_rename'] . '.' . $imageExt)) {
                    $imageName = $imageArray['image_rename'] . '.' . $imageExt;
                    $returnArray['image'] = $savePath . $imageName;
                }                    
            }
            
            //进行水印处理
            if($waterMark and file_exists(DBSHOP_PATH . $returnArray['image'])) {
                $this->createWaterMark(DBSHOP_PATH . $returnArray['image'], DBSHOP_PATH . $returnArray['image']);
            }
            
            //进行缩微图处理
            if(!empty($thumbArray) and ($thumbArray['width'] != 0 or $thumbArray['height'] != 0) and file_exists(DBSHOP_PATH . $returnArray['image'])) {
               $returnArray['thumb_image'] = $this->createThumbImage($savePath, $imageName, $thumbArray);
               chmod(DBSHOP_PATH . $returnArray['thumb_image'], 0766);
            }
      
        }
        return $returnArray;
    }
    /**
     * 生成水印图片
     * @param unknown $inputImage
     * @param unknown $outputImage
     * @return unknown
     */
    public function createWaterMark ($inputImage, $outputImage)
    {
        if($this->watermarkConfig['config']['watermark_state'] == 'close') return ;

        if(!empty($this->watermarkConfig['image']['watermark_image'])) $this->watermarkConfig['image']['watermark_image'] = DBSHOP_PATH . $this->watermarkConfig['image']['watermark_image'];

        $waterOption = array_merge($this->watermarkConfig['config'], $this->watermarkConfig['image'], $this->watermarkConfig['text']);
        \WaterMark::output($inputImage, $outputImage , $waterOption);
        //return $outputImage;
    }
    /**
     * 生成缩微图
     * @param unknown $savePath
     * @param unknown $imageName
     * @param array $thumbOption
     * @return string
     */
    public function createThumbImage ($savePath, $imageName, array $thumbOption)
    {
        $thumbOption['width']  = (int) $thumbOption['width'];
        $thumbOption['height'] = (int) $thumbOption['height'];
        
        $thumbImage = (isset($thumbOption['rename']) and $thumbOption['rename'] == TRUE) ? 'thumb_' . $imageName : $imageName;
        
        if($this->_getExtension($imageName) == 'ico') return $savePath . $thumbImage;
        
        $thumb      = \PhpThumbFactory::create(DBSHOP_PATH . $savePath . $imageName);
        if($thumbOption['width'] != 0 and $thumbOption['height'] != 0) {
            if(isset($thumbOption['crop']) and $thumbOption['crop'] == 'true') {
                //从中间裁切固定宽高
                $thumb->cropFromCenter($thumbOption['width'], $thumbOption['height']);
            } else {
                //自适应裁切
                $thumb->dbshopResize($thumbOption['width'], $thumbOption['height']);//使用dbshop自定制方法
                //$thumb->resize($thumbOption['width'], $thumbOption['height']);
            }            
        }

        $thumb->save(DBSHOP_PATH . $savePath . $thumbImage);
        
        return $savePath . $thumbImage;
    }
    /**
     * 获取图片后缀
     * @param unknown $imageName
     * @return unknown
     */
    private function _getExtension ($imageName=null)
    {
        if(empty($imageName)) return ;
        if(function_exists('pathinfo')) {
            return pathinfo($imageName, PATHINFO_EXTENSION);
        }
        return end(explode('.', $imageName));
        /*$exts = split("[/.]", $imageName);
        $exts = $exts[count($exts)-1];*/

    }
    /**
     * 在系统中判断上传的图片中是否包含中文名称，如果包含，则进行重命名处理（上传中文名称图片系统会报错）
     */
    private function osCheckUploadfileName()
    {
        if(is_array($_FILES) and !empty($_FILES)) {
            foreach($_FILES as $key => $fileVale) {
                if(isset($fileVale['name']) and !empty($fileVale['name']) and preg_match("/[\x7f-\xff]/", $fileVale['name'])) {
                    $_FILES[$key]['name'] = md5(time().rand(1, 100)) . '.' . $this->_getExtension($fileVale['name']);
                }
            }
        }
    }
    /**
     * 检查图片类型
     * @param $imageType
     * @return bool
     */
    private function checkImageType($imageType)
    {
        return  $imageType == 'image/pjpeg' ||
                $imageType == 'image/x-png' ||
                $imageType == 'image/png'   ||
                $imageType == 'image/gif'   ||
                $imageType == 'image/jpeg'  ||
                $imageType == 'image/jpg'   ||
                $imageType == 'image/bmp'   ||
                $imageType == 'image/x-ms-bmp' ||
                $imageType == 'image/x-ico' ||
                $imageType == 'image/x-icon' ||
                $imageType == 'application/octet-stream';
    }
}

?>