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

namespace Package\Controller;

use Admin\Controller\BaseController;

class PackageController extends BaseController
{
    private $soapclient;
    private $location = 'https://update.dbshop.net/packageservice';
    private $uri      = 'dbshop_package_update';
    
    public function indexAction()
    {   
        if(!class_exists('SoapClient')) return array('soap_state'=>'false');
        $array = array();
        include DBSHOP_PATH.'/data/Version.php';
        try {
            $array = $this->dbshopSoapClient('listDbshopUpdatePackage',array(
                    'dbshop_version_number' => DBSHOP_VERSION_NUMBER,
                    'dbshop_version'        => DBSHOP_VERSION
            ));
        } catch (\Exception $e) {
            return array('soap_state'=>'no_link');
        }
        
        $this->checkUpdateUserLogin();
        
        return $array;
    }
    public function downloadupdateAction()
    {
        $loginUserInfo = $this->checkUpdateUserLogin();
        
        $array     = array();
        include DBSHOP_PATH . '/data/Version.php';
        $packageId = (int)$this->params('package_id', 0);
        $array['packageInfo'] = $this->dbshopSoapClient('infoUpdatePackage', array('v_id'=>$packageId, 'client_version_number'=>DBSHOP_VERSION_NUMBER));
        
        if(isset($array['packageInfo']) and empty($array['packageInfo'])) return $this->redirect()->toRoute('package/default');
        if(DBSHOP_VERSION_NUMBER >= $array['packageInfo']->version_number) return $this->redirect()->toRoute('package/default');
        
        /*此段代码是之前dbshop加密时写入的，现在已经开源，不需要区分php版本了
         $phpVersionStr = '';
        if(version_compare(PHP_VERSION, '5.4.0', '>=')) $phpVersionStr = 'PHP5.4_';
        $array['php_version_str'] = $phpVersionStr;
        */

        return $array;
    }
    /**
     * 在线更新处理
     * @return multitype:NULL
     */
    public function onlineupdateAction() 
    {   
        set_time_limit(0);
        $loginUserInfo = $this->checkUpdateUserLogin();
        
        $array     = array();
        include DBSHOP_PATH . '/data/Version.php';
        $packageId = (int)$this->params('package_id', 0);
        $array['packageInfo'] = $this->dbshopSoapClient('infoUpdatePackage', array('v_id'=>$packageId, 'client_version_number'=>DBSHOP_VERSION_NUMBER));
        
        if(isset($array['packageInfo']) and empty($array['packageInfo'])) return $this->redirect()->toRoute('package/default');
        if(DBSHOP_VERSION_NUMBER >= $array['packageInfo']->version_number) return $this->redirect()->toRoute('package/default');

        if($this->request->isPost()) {
            $packagePath = DBSHOP_PATH . '/data/moduledata/Package/updatepack';
            //如果php版本是5.4以上的，进行更新包重命名(程序已经开源，此代码暂不需要)
            /*if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
                $array['packageInfo']->update_package = 'PHP5.4_' . $array['packageInfo']->update_package;
            }*/

            //获取更新的目录
            $packageDirname = substr($array['packageInfo']->update_package, 0, -4);
            $sourcePath     = $packagePath . '/' . $packageDirname;

            if($this->request->getPost('update_state') == 'check_permission') {//检查权限是否允许更新
                if(!is_dir($sourcePath))
                {
                    //开始下载
                    $ch = curl_init($array['packageInfo']->package_download_url . $array['packageInfo']->update_package);
                    $fp = fopen($packagePath."/".$array['packageInfo']->update_package, "w");
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);
                    //对下载压缩包进行md5比对(注释掉的代码，为之前程序加密时使用，现在程序已经开源)
                    /*if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
                        if(md5_file($packagePath."/".$array['packageInfo']->update_package) != $array['packageInfo']->package_md5_54) exit($this->getDbshopLang()->translate('下载的更新包与原始更新包不匹配，无法更新'));
                    } else {
                        if(md5_file($packagePath."/".$array['packageInfo']->update_package) != $array['packageInfo']->package_md5) exit($this->getDbshopLang()->translate('下载的更新包与原始更新包不匹配，无法更新'));
                    }*/
                    //对下载压缩包进行md5比对
                    if(md5_file($packagePath."/".$array['packageInfo']->update_package) != $array['packageInfo']->package_md5) exit($this->getDbshopLang()->translate('下载的更新包与原始更新包不匹配，无法更新'));
                    //开始解压缩
                    if(class_exists('ZipArchive')) {//当内置ZipArchive可用时，使用
                        $unZip = new \ZipArchive();
                        $unZip->open($packagePath."/".$array['packageInfo']->update_package);
                        $unZip->extractTo($packagePath);
                        $unZip->close();
                    } else {//当内置ZipArchive不可用时，使用第三方组件pclzip
                        $unZip = new \PclZip($packagePath."/".$array['packageInfo']->update_package);
                        $unZip->extract(PCLZIP_OPT_PATH, $packagePath);
                    }
                }
                //获取更新目录的所有需要更新的文件
                $allUpdateFile  = $this->getSunFile($sourcePath);
                if(is_array($allUpdateFile) and !empty($allUpdateFile)) {//如果这个目录不是空的，进行检查处理
                    $checkString = '';//检查结果的字符串
                    foreach ($allUpdateFile as $updateFile) {
                        //要被覆盖的目标文件
                        $coverFile = str_replace('/data/moduledata/Package/updatepack/'.$packageDirname, '', $updateFile);
                        if(file_exists($coverFile)) {//如果该文件存在，检查是否可写
                            if(!is_writable($coverFile)) $checkString .= $coverFile . '&nbsp;[<font color="red">' . $this->getDbshopLang()->translate('无写权限') . '</font>]<br>';
                        } else {//如果此文件不存在，则进行目录检查是否可写
                            $dirName = dirname($coverFile);
                            if(!$this->dirWriteable($dirName)) $checkString .= $dirName . '&nbsp;[<font color="red">' . $this->getDbshopLang()->translate('无写权限或不可创建') . '</font>]<br>';
                        }
                    }
                } else {//如果这个目录是空的，直接跳出
                    exit();
                }
                //如果字符串不为空，说明有不符合权限的文件或者目录存在
                if(!empty($checkString)) exit($checkString);
                file_put_contents($packagePath . '/updateok.txt', 'ok');
                exit('permission_ok');
            }


            if($this->request->getPost('update_state') == 'start_update') {//权限允许，进行更新处理
                if(!file_exists($packagePath . '/updateok.txt')) exit($this->getDbshopLang()->translate('权限不足，无法更新'));
                $updateState = 'false';

                //数据库更新处理
                if(file_exists($sourcePath . '/sql/update.sql')) {
                    $this->runInstallSql($sourcePath . '/sql/update.sql');
                }
                //文件更新处理
                $allUpdateFile  = $this->getSunFile($sourcePath);
                if(is_array($allUpdateFile) and !empty($allUpdateFile)) {
                    //$backPath = DBSHOP_PATH . '/data/moduledata/Package/back/'.$packageDirname.'_'.date("Y-m-d");
                    //if(!is_dir($backPath)) mkdir(rtrim($backPath),0777);
                    foreach ($allUpdateFile as $updateFile) {
                        $coverFile = str_replace('/data/moduledata/Package/updatepack/'.$packageDirname, '', $updateFile);
                        //备份原始文件到back目录
                        /*if(file_exists($coverFile)) {
                            $backFile  = $backPath.str_replace(DBSHOP_PATH, '', $coverFile);
                            $backDir   = dirname($backFile);
                            if(!is_dir($backDir)) mkdir($backDir, 0777, true);
                            copy($coverFile, $backFile);
                        }*/
                        //更新将新文件覆盖
                        $coverPath = dirname($coverFile);
                        if(!is_dir($coverPath)) mkdir($coverPath, 0777, true); 
                        if(!copy($updateFile, $coverFile)) exit(sprintf($this->getDbshopLang()->translate('无法正常更新文件，请检查%s对应的目录是否有相关权限'), $coverFile));
                        @chmod($coverFile, 0755);

                        //删除更新文件
                        @unlink($updateFile);
                    }
                    $this->delUpdateDir($packagePath.'/'.$packageDirname);
                    @unlink($packagePath."/".$array['packageInfo']->update_package);
                    //更新版本信息
                    if(!empty($array['packageInfo']->version_name)) {
                        $versionContent = "<?php\n";
                        $versionContent .= "define('DBSHOP_VERSION','".$array['packageInfo']->version_name."');\n";
                        $versionContent .= "define('DBSHOP_VERSION_NUMBER',".$array['packageInfo']->version_number.");\n";
                        $versionContent .= "define('DBSHOP_VERSION_UPDATE_DATE',".str_replace('-', '', $array['packageInfo']->update_time).");\n";
                        $versionContent .= "define('DBSHOP_CHARSET','UTF-8');\n";
                        $versionContent .= "define('DBSHOP_INSTALL_TIME','".DBSHOP_INSTALL_TIME."');\n";
                        $versionContent .= "define('DBSHOP_UPDATE_TIME','".$array['packageInfo']->update_time."');\n";
                        file_put_contents(DBSHOP_PATH . '/data/Version.php', $versionContent);
                    }

                    $this->dbshopSoapClient('addDbshopUpdateLog',array(
                            'v_id'=>$packageId,
                            'web_url'=>$this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default'),
                            'user_name'=>$loginUserInfo['username'])
                    );

                    @unlink($packagePath . '/updateok.txt');//删除权限标记文件

                    //删除原始ZF2配置缓存
                    //$this->getServiceLocator()->get('adminHelper')->clearZfConfigCache();//此为调用Helper的公用清除缓存方法
                    $this->zfdbshopClearConfigCache();//调用当前类中的私有方法
                    //如果开启opcache或者有此函数，都进行一次处理
                    if (function_exists('opcache_reset')) opcache_reset();

                    $updateState = 'true';
                }
                
                
                exit($updateState);
            }
        }
        
        return $array;
    }
    /** 
     * 会员第一次登录操作，获取更新服务器的返回信息
     */
    public function updateUserLoginAction() 
    {
        if($this->request->isPost()) {
            $userName   = trim($this->request->getPost('username'));
            $password   = trim($this->request->getPost('password'));
            $loginState = $this->dbshopSoapClient('serverUcenterUserLogin', array('username'=>$userName, 'password'=>$password));
            if(!$loginState) exit('false');
            $configWriter = new \Zend\Config\Writer\Ini();
            $configWriter->toFile(DBSHOP_PATH . '/data/moduledata/Package/config.ini', array('username'=>$userName, 'loginkey'=>$loginState));
            exit('true');
        }
        $array = array();
        $array['http_referer'] = $this->getRequest()->getServer('HTTP_REFERER');
        return $array;
    }
    /** 
     * 检查是否是有效会员
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    private function checkUpdateUserLogin()
    {
        $configFile = DBSHOP_PATH . '/data/moduledata/Package/config.ini';
        if(!file_exists($configFile)) return $this->redirect()->toRoute('package/default',array('controller'=>'package', 'action'=>'updateUserLogin'));
        
        $configRead = new \Zend\Config\Reader\Ini();
        $config     = $configRead->fromFile($configFile);
        
        if(!$this->dbshopSoapClient('checkUcenterUser', array('user_name'=>$config['username'], 'user_passwd'=>$config['loginkey']))) return $this->redirect()->toRoute('package/default',array('controller'=>'package', 'action'=>'updateUserLogin'));
        return $config;
    }
    /**
     * soap客户端调用
     * @param unknown $clientFunction
     * @param string $array
     */
    private function dbshopSoapClient($clientFunction, $array=array()) 
    {
        if(!$this->soapclient) {
            $this->soapclient = new \SoapClient(null, array(
                'location' => $this->location,
                'uri'      => $this->uri,
            ));
        }
        return $this->soapclient->$clientFunction($array);
    }
    /**
     * 列出目录下的所有文件，包括子目录文件,不包含sql目录
     * @param unknown $dirName
     * @return Ambigous <multitype:, multitype:string >
     */
    private function getSunFile($dirName)
    {
        $dirName = rtrim($dirName, '/\\');
        $ret = array();
        if (is_dir($dirName)) {
            if (($dh = @opendir($dirName)) !== false) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && $file != 'sql' && $file != '.DS_Store') {
                        $path = $dirName . DIRECTORY_SEPARATOR . $file;
                        //$ret[] = $path;如果不加下面的if则连同目录一起列出
                        if(!is_dir($path)) $ret[] = $path;
                        is_dir($path) && $ret = array_merge($ret, $this->getSunFile($path));
                    }
                }
                closedir($dh);
            }
        }
        return $ret;
    }
    /**
     * 执行安装sql文件
     * @param unknown $sqlFile
     */
    private function runInstallSql($sqlFile)
    {
        $sql = file_get_contents($sqlFile);
        if(!isset($sql) or empty($sql)) return false;
    
        $sql = str_replace("\r\n", "\n", $sql);
        $sql = str_replace("\r", "\n", $sql);
    
        $sqlArray = explode(";\n", $sql);
        foreach ($sqlArray as $sqlStr) {
            $querySql = '';
            $querySql = trim(str_replace("\n", '', $sqlStr));
            if($querySql) {
                $this->getQuery($querySql);
            }
        }
    }
    /**
     * 连接数据库，生成sql语句
     * @param unknown $sql
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    private function getQuery ($sql)
    {
        if (empty($this->dbQuery)) {
            $this->dbQuery = new \Zend\Db\Adapter\Adapter(include DBSHOP_PATH . '/data/Database.ini.php');
        }
        $query = $this->dbQuery->createStatement($sql);
        $query->prepare();
        return $query->execute();
    }
    /**
     * 系统更新后，清空zf2框架的缓存配置
     */
    private function zfdbshopClearConfigCache()
    {
        $zfConfigCachePathArray = array(
            DBSHOP_PATH . '/data/cache/modulecache/'
        );

        foreach ($zfConfigCachePathArray as $valuePath) {
            $folder = @opendir($valuePath);
            while (false !== ($file = @readdir($folder))) {
                if($file != '.' and $file != '..' and is_file($valuePath.$file)) {
                    @unlink($valuePath.$file);
                }
            }
        }
    }
    /**
     * 检查是否可写
     * @param unknown $dir
     * @return number
     */
    private function dirWriteable($dir)
    {
        $writeable = 0;
        if(!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        if(is_dir($dir)) {
            if($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }
        return $writeable;
    }
    /**
     * 删除目录
     * @param $dir
     * @return bool
     */
    private function delUpdateDir($dir) {
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!=".." && $file != '.DS_Store') {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    $this->delUpdateDir($fullpath);
                }
            }
        }
        closedir($dh);
        if(@rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }
}
