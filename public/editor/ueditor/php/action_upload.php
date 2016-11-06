<?php
include
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";

/* 上传配置 */

$base64 = "upload";
$dbfiledir = str_replace(array('\\', '/editor/ueditor/php'), array('/', ''), strstr(__DIR__, $substr_dbshop_str));
//$dbfiledir = str_replace(array('\\', '/editor/ueditor/php'), array('/', ''), substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT'])));

//获取云存储类型
$configReader  = new \Zend\Config\Reader\Ini();
$storageConfig = $configReader->fromFile(__DIR__ . '/../../../../data/moduledata/Upload/Storage.ini');
if($storageConfig['storage_type'] == 'Local' or $storageConfig['storage_type'] == '') {
    $imagePathFormat  = $dbfiledir . '/upload/editor/{yyyy}{mm}{dd}/{time}{rand:6}';
} else {//云存储
    $imagePathFormat  = $dbfiledir . '/upload/editor/{yyyy}/{time}{rand:6}';
}

$scrawlPathFormat = $dbfiledir . '/upload/editor/{yyyy}{mm}{dd}/{time}{rand:6}';
$videoPathFormat  = $dbfiledir . '/upload/editor/video/{yyyy}{mm}{dd}/{time}{rand:6}';
$filePathFormat   = $dbfiledir . '/upload/editor/file/{yyyy}{mm}{dd}/{time}{rand:6}';

switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            //"pathFormat" => $CONFIG['imagePathFormat'],
			"pathFormat" => $imagePathFormat,
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            //"pathFormat" => $CONFIG['scrawlPathFormat'],
			"pathFormat" => $scrawlPathFormat,
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            //"pathFormat" => $CONFIG['videoPathFormat'],
			"pathFormat" => $videoPathFormat,
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            //"pathFormat" => $CONFIG['filePathFormat'],
			"pathFormat" => $filePathFormat,
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */
$imageInfoArray = $up->getFileInfo();

//云操作处理
function yunStorageUpload($image, $storageConfig) {
    $basePath = __DIR__ . '/../../../..';
    if($storageConfig['storage_type'] == 'Qiniu') {
        $auth = new \Qiniu\Auth($storageConfig['qiniu_ak'], $storageConfig['qiniu_sk']);
        $bucket = $storageConfig['qiniu_space_name'];
        $token  = $auth->uploadToken($bucket, null, 3600);
        $uploadMgr = new \Qiniu\Storage\UploadManager();
        list($ret, $err) = $uploadMgr->putFile($token, null, $basePath . $image);
        if($err !== null) {//当上传失败时，使用本地文件
            return $image;
        } else {//当上传成功时，使用云端文件并删除本地文件
            @unlink($basePath . $image);
            return '{qiniu}/'.$ret['key'];
        }
    }
    if($storageConfig['storage_type'] == 'Aliyun') {
        require_once $basePath . '/module/Upload/src/Upload/Plugin/Aliyun/sdk.class.php';
        $aliyunOssDomain = isset($storageConfig['aliyun_oss_domain']) ? $storageConfig['aliyun_oss_domain'] : str_replace($storageConfig['aliyun_space_name'].'.', '', $storageConfig['aliyun_domain']);
        $aliyunService = new \ALIOSS($storageConfig['aliyun_ak'], $storageConfig['aliyun_sk'], $aliyunOssDomain);
        $aliyunService->set_debug_mode(false);
        $response = $aliyunService->upload_file_by_file($storageConfig['aliyun_space_name'], basename($image),  $basePath . $image);
        if($response->status == 200) {
            @unlink($basePath . $image);
            return '{aliyun}/'.basename($image);
        }
    }
    return $image;

}

//当是商品图片上传并且成功时进行处理
if(isset($_GET['dbshop_goods_id']) and $_GET['action'] == 'uploadimage' and $imageInfoArray['state'] == 'SUCCESS') {
    $dbimagefile = '/public' . strstr($imageInfoArray['url'], '/upload/editor/');

    $goodsConfig    = parse_ini_file(__DIR__ . '/../../../../data/moduledata/Upload/Goods.ini', true);
    $waterMarkConfig= parse_ini_file(__DIR__ . '/../../../../data/moduledata/Upload/Watermark.ini', true);
    $trueImageFile  = __DIR__ . '/../../../..' . $dbimagefile;
    //圖片添加水印
    if($goodsConfig['goods']['goods_watermark_state'] == 1 and $waterMarkConfig['config']['watermark_state'] != 'close') {//商品圖片添加水印
        include __DIR__.'/../../../../module/Upload/src/Upload/Plugin/Watermark/WaterMark.php';
        if(!empty($waterMarkConfig['image']['watermark_image'])) $waterMarkConfig['image']['watermark_image'] = __DIR__ . '/../../../..' . $waterMarkConfig['image']['watermark_image'];
        $waterOption = array_merge($waterMarkConfig['config'], $waterMarkConfig['image'], $waterMarkConfig['text']);
        WaterMark::output($trueImageFile, $trueImageFile, $waterOption);
    }
    //圖片縮微圖處理
    $goodsThumbImage = $dbimagefile;
    $thumbOption['width']  = (int) $goodsConfig['goods']['goods_thumb_width'];
    $thumbOption['heigth'] = (int) $goodsConfig['goods']['goods_thumb_heigth'];
    if(($thumbOption['width'] != 0 or $thumbOption['heigth'] != 0) and file_exists($trueImageFile)) {
        $imageName = basename($trueImageFile);
        $imagePath = dirname($trueImageFile);
        $thumbImage= 'thumb_'.$imageName;
        include __DIR__.'/../../../../module/Upload/src/Upload/Plugin/Thumb/ThumbLib.inc.php';
        $thumb = PhpThumbFactory::create($trueImageFile);
        if($thumbOption['width'] != 0 and $thumbOption['heigth'] != 0) {
            if(isset($goodsConfig['goods']['goods_image_crop']) and $goodsConfig['goods']['goods_image_crop'] == 'true') {
                //从中间裁切固定宽高
                $thumb->cropFromCenter($thumbOption['width'], $thumbOption['height']);
            } else {
                //自适应裁切
                $thumb->dbshopResize($thumbOption['width'], $thumbOption['height']);//使用dbshop自定制方法
            }
        }
        $thumb->save($imagePath . '/' . $thumbImage);
        if(file_exists($imagePath . '/' . $thumbImage)) {
            $goodsThumbImage = strstr($imagePath . '/' . $thumbImage, '/public/upload/editor/');
            chmod($imagePath . '/' . $thumbImage, 0766);
        }
    }

    //圖片保存數據庫
    $databaseConfig = include __DIR__ . '/../../../../data/Database.ini.php';
    $db             = new PDO($databaseConfig['dsn'], $databaseConfig['username'], $databaseConfig['password'], $databaseConfig['driver_options']);

    $sql = 'INSERT INTO dbshop_goods_image(goods_image_id, goods_id, goods_title_image, goods_thumbnail_image, goods_watermark_image, goods_source_image, editor_session_str, image_sort, language, image_slide)
    VALUES(
    NULL,:goods_id, :goods_title_image, :goods_thumbnail_image, :goods_watermark_image, :goods_source_image, :editor_session_str, 255, :language, 0)';

    $dbimagefile     = yunStorageUpload($dbimagefile, $storageConfig);
    $goodsThumbImage = yunStorageUpload($goodsThumbImage, $storageConfig);

    $sth = $db->prepare($sql);
    $sth->bindParam(':goods_id', intval($_GET['dbshop_goods_id']));
    $sth->bindParam(':goods_title_image', $dbimagefile);
    $sth->bindParam(':goods_thumbnail_image', $goodsThumbImage);
    $sth->bindParam(':goods_watermark_image', $dbimagefile);
    $sth->bindParam(':goods_source_image', $dbimagefile);
    $sth->bindParam(':editor_session_str', trim($_GET['dbshop_session_str']));
    $sth->bindParam(':language', trim($_GET['dbshop_language']));

    $sth->execute();
}

if($storageConfig['storage_type'] == 'Qiniu'  and isset($_GET['dbshop_goods_id'])) {//七牛云存储
    $imageInfoArray['url']   = 'http://'.$storageConfig['qiniu_domain'].'/'.basename($dbimagefile);
    $imageInfoArray['title'] = basename($dbimagefile);
    $imageInfoArray['original'] = $imageInfoArray['title'];
}
if($storageConfig['storage_type'] == 'Aliyun'  and isset($_GET['dbshop_goods_id'])) {//阿里云云存储
    $imageInfoArray['url']   = 'http://'.$storageConfig['aliyun_domain'].'/'.basename($dbimagefile);
    $imageInfoArray['title'] = basename($dbimagefile);
    $imageInfoArray['original'] = $imageInfoArray['title'];
}

/* 返回数据 */
return json_encode($imageInfoArray);
