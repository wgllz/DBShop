<?php
/**
 * 获取已上传的文件列表
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";

/* 判断类型 */
$fileManagerListPath = __DIR__ . '/../../../upload/';
$imageManagerListPath = __DIR__ . '/../../../upload/';

//获取云存储类型
$configReader  = new \Zend\Config\Reader\Ini();
$storageConfig = $configReader->fromFile(__DIR__ . '/../../../../data/moduledata/Upload/Storage.ini');

switch ($_GET['action']) {
    /* 列出图片 */
    case 'listgoodsimage':
    default:
        $allowFiles = $CONFIG['imageManagerAllowFiles'];
        $listSize = $CONFIG['imageManagerListSize'];
        //$path = $CONFIG['imageManagerListPath'];
		$path = $imageManagerListPath;
}
$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

/* 获取参数 */
$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
$end = $start + $size;

/* 获取文件列表 */
//$path = $_SERVER['DOCUMENT_ROOT'] . (substr($path, 0, 1) == "/" ? "":"/") . $path;

$files = getfiles($path, $allowFiles, $storageConfig);
if (!count($files)) {
    return json_encode(array(
        "state" => "no match file",
        "list" => array(),
        "start" => $start,
        "total" => count($files)
    ));
}

/* 获取指定范围的列表 */
$len = count($files);
//倒序
/*for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
    $list[] = $files[$i];
}*/

//正序
for ($i = 0, $list = array(); $i < $len && $i < $end; $i++){
    $list[] = $files[$i];
}

/* 返回数据 */
$result = json_encode(array(
    "state" => "SUCCESS",
    "list" => $list,
    "start" => $start,
    "total" => count($files)
));

return $result;


/**
 * 遍历获取目录下的指定类型的文件
 * @param $path
 * @param array $files
 * @return array
 */
function getfiles($path, $allowFiles, $storageConfig)
{
    $databaseConfig = include __DIR__ . '/../../../../data/Database.ini.php';
    $goodsId = intval($_GET['dbshop_goods_id']);
    $db      = new PDO($databaseConfig['dsn'], $databaseConfig['username'], $databaseConfig['password'], $databaseConfig['driver_options']);

    $orSql   = ($goodsId == 0 ? '' : 'or goods_id='.$goodsId);
    $sth     = $db->prepare("SELECT * FROM dbshop_goods_image WHERE (goods_id=0 and editor_session_str='".trim($_GET['dbshop_session_str'])."') ".$orSql." order by image_sort ASC, goods_image_id ASC");
    $sth->execute();
    $result  = $sth->fetchAll();
    if(is_array($result) and !empty($result)) {
        $imagePath = str_replace(array('\\', '/public/editor/ueditor/php'), array('/', ''), $GLOBALS['substr_dbshop_str']);
        foreach($result as $key => $value) {
            $imageFile = $imagePath . $value['goods_watermark_image'];

            //判断是否是云存储
            if(stripos($value['goods_watermark_image'],'{qiniu}') !== false) {
                $imageFile = (isset($storageConfig['qiniu_http_type']) ? $storageConfig['qiniu_http_type'] : 'http://').$storageConfig['qiniu_domain'] . '/' .basename($value['goods_watermark_image']);
            }
            if(stripos($value['goods_watermark_image'],'{aliyun}') !== false) {
                $imageFile = (isset($storageConfig['aliyun_http_type']) ? $storageConfig['aliyun_http_type'] : 'http://').$storageConfig['aliyun_domain'] . '/' .basename($value['goods_watermark_image']);
            }

            $files[] = array(
                'url'=> $imageFile,
                //'mtime'=> $key
                'mtime'=> filemtime($imageFile)
            );

        }
    }
    return $files;
}