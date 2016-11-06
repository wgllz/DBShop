<?php
/**
 * DBShop 电子商务系统
 *
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2015 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 */
return array(
    'Upload\Module'                      => __DIR__ . '/Module.php',
    'Upload\Controller\UploadController' => __DIR__ . '/src/Upload/Controller/UploadController.php',
    'Upload\Common\Service\ImageUpload' => __DIR__ . '/src/Upload/Service/ImageUpload.php',
    //缩微图处理
    'ThumbBase'        => __DIR__ . '/src/Upload/Plugin/Thumb/ThumbBase.inc.php',
    'GdThumb'          => __DIR__ . '/src/Upload/Plugin/Thumb/GdThumb.inc.php',
    'PhpThumbFactory'  => __DIR__ . '/src/Upload/Plugin/Thumb/ThumbLib.inc.php',
    'PhpThumb'         => __DIR__ . '/src/Upload/Plugin/Thumb/PhpThumb.inc.php',
    'GdReflectionLib'  => __DIR__ . '/src/Upload/Plugin/Thumb/thumb_plugins/gd_reflection.inc.php',
    //二维码处理
    'QRcode'           => __DIR__ . '/src/Upload/Plugin/Phpqrcode/phpqrcode.php',
    //图表处理，chart
    'pData'            => __DIR__ . '/src/Upload/Plugin/pChart/class/pData.class.php',  //图表基础类
    'pImage'           => __DIR__ . '/src/Upload/Plugin/pChart/class/pImage.class.php', //图表基础类
    'pCache'           => __DIR__ . '/src/Upload/Plugin/pChart/class/pCache.class.php',
    'pBarcode39'       => __DIR__ . '/src/Upload/Plugin/pChart/class/pBarcode39.class.php',
    'pBarcode128'      => __DIR__ . '/src/Upload/Plugin/pChart/class/pBarcode128.class.php',
    'pBubble'          => __DIR__ . '/src/Upload/Plugin/pChart/class/pBubble.class.php',
    'pIndicator'       => __DIR__ . '/src/Upload/Plugin/pChart/class/pIndicator.class.php',
    'pPie'             => __DIR__ . '/src/Upload/Plugin/pChart/class/pPie.class.php',
    'pRadar'           => __DIR__ . '/src/Upload/Plugin/pChart/class/pRadar.class.php',
    'pScatter'         => __DIR__ . '/src/Upload/Plugin/pChart/class/pScatter.class.php',
    'pSplit'           => __DIR__ . '/src/Upload/Plugin/pChart/class/pSplit.class.php',
    
    //水印处理
    'WaterMark' => __DIR__ . '/src/Upload/Plugin/Watermark/WaterMark.php',
    
    //模块中上传处理类，依次是 Goods、System 、Other
    'Upload\Common\Service\GoodsUploadService'  => __DIR__ . '/src/Upload/Service/GoodsUploadService.php',
    'Upload\Common\Service\SystemUploadService' => __DIR__ . '/src/Upload/Service/SystemUploadService.php',
    'Upload\Common\Service\OtherUploadService' => __DIR__ . '/src/Upload/Service/OtherUploadService.php',
);
