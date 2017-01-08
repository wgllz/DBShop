<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2016 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace Plugin\Controller;

use Admin\Controller\BaseController;
use Admin\Service\DbshopOpcache;
use Zend\Config\Reader\Ini;
use Zend\Config\Writer\PhpArray;
use Zend\View\Model\ViewModel;

class IndexController extends BaseController
{
    private $soapclient;
    private $updateUrl= 'http://update.dbshop.net';
    private $location = 'http://update.dbshop.net/packageservice';
    private $uri      = 'dbshop_package_update';
    /**
     * 插件列表
     * @return array
     */
    public function indexAction()
    {
        $array = array();

        $array['plugin_list'] = $this->getDbshopTable()->listPlugin();

        //如果存在插件，检查是否有更新
        $array['plugin_version'] = array();
        if(!empty($array['plugin_list'])) {
            $where = '';
            foreach($array['plugin_list'] as $value) {
                $where .= "plugin_code='".$value['plugin_code']."' or ";
            }
            if(class_exists('SoapClient') and !empty($where)) {
                $where = substr($where, 0, -4);
                try {
                    $pluginVersion = $this->dbshopSoapClient('dbshopPluginList', array($where));
                } catch (\Exception $e) {

                }
                if(!empty($pluginVersion)) {
                    foreach($pluginVersion as $versionValue) {
                        $array['plugin_version'][$versionValue['plugin_code']] = $versionValue;
                    }
                }
            }
        }

        return $array;
    }
    /**
     * 启用插件
     * @return \Zend\Http\Response
     */
    public function startPluginAction()
    {
        $pluginId = (int) $this->request->getQuery('plugin_id');
        if($pluginId > 0) {
            $pluginInfo = $this->getDbshopTable()->infoPlugin(array('plugin_id'=>$pluginId));
            //插件的目录
            $pluginPath = DBSHOP_PATH . '/module/Extendapp/'.$pluginInfo->plugin_code;
            if(isset($pluginInfo->plugin_state) and $pluginInfo->plugin_state == 2 and is_dir($pluginPath)) {
                //插件启用
                $this->getDbshopTable()->updatePlugin(array('plugin_state'=>1), array('plugin_id'=>$pluginId));
                //查看是否有需要引入的url
                if(file_exists($pluginPath . '/data/urlArray.php')) {
                    $iniRead    = new Ini();
                    $urlIni     = $iniRead->fromFile(DBSHOP_PATH . '/data/moduledata/moduleini/url.ini');

                    $urlArray   = include $pluginPath . '/data/urlArray.php';
                    if(is_array($urlArray) and !empty($urlArray)) {
                        $urlTag     = key($urlArray);
                        $pluginCode = strtolower($pluginInfo->plugin_code);

                        $urlIni[$urlTag][$pluginCode]['name']   = key($urlArray[$urlTag]);
                        $urlIni[$urlTag][$pluginCode]['url']    = current($urlArray[$urlTag]);

                        $iniWrite = new \Zend\Config\Writer\Ini();
                        $iniWrite->toFile(DBSHOP_PATH . '/data/moduledata/moduleini/url.ini', $urlIni);
                    }
                }
                //将插件写入module
                $moduleExtend = include DBSHOP_PATH . '/data/moduledata/moduleini/Module.extend.php';
                if(isset($moduleExtend['modules']) and !empty($moduleExtend['modules'])) {
                    if(!in_array($pluginInfo->plugin_code, $moduleExtend['modules'])) {
                        $moduleExtend['modules'][] = $pluginInfo->plugin_code;
                    }
                } else {
                    $moduleExtend['modules'][] = $pluginInfo->plugin_code;
                }
                $phpWrite = new PhpArray();
                $phpWrite->toFile(DBSHOP_PATH . '/data/moduledata/moduleini/Module.extend.php', $moduleExtend);

                //清空ZF2缓存设置
                $this->zfdbshopClearConfigCache();
                //缓存清理
                DbshopOpcache::reset();
            }

        }
        return $this->redirect()->toRoute('plugin/default');
    }
    /**
     * 禁用（关闭）插件
     * @return \Zend\Http\Response
     */
    public function stopPluginAction()
    {
        $pluginId = (int) $this->request->getQuery('plugin_id');
        if($pluginId > 0) {
            $pluginInfo = $this->getDbshopTable()->infoPlugin(array('plugin_id'=>$pluginId));
            //插件的目录
            $pluginPath = DBSHOP_PATH . '/module/Extendapp/'.$pluginInfo->plugin_code;
            if(isset($pluginInfo->plugin_state) and $pluginInfo->plugin_state == 1 and is_dir($pluginPath)) {
                //插件关闭
                $this->getDbshopTable()->updatePlugin(array('plugin_state'=>2), array('plugin_id'=>$pluginId));
                //查看是否有需要去除的url
                if(file_exists($pluginPath . '/data/urlArray.php')) {
                    $iniRead    = new Ini();
                    $urlIni     = $iniRead->fromFile(DBSHOP_PATH . '/data/moduledata/moduleini/url.ini');

                    $urlArray   = include $pluginPath . '/data/urlArray.php';
                    if(is_array($urlArray) and !empty($urlArray)) {
                        $urlTag     = key($urlArray);
                        $pluginCode = strtolower($pluginInfo->plugin_code);
                        if(isset($urlIni[$urlTag][$pluginCode])) {
                            unset($urlIni[$urlTag][$pluginCode]);

                            $iniWrite = new \Zend\Config\Writer\Ini();
                            $iniWrite->toFile(DBSHOP_PATH . '/data/moduledata/moduleini/url.ini', $urlIni);
                        }
                    }
                }
                //将插件从module移除
                $moduleExtend = include DBSHOP_PATH . '/data/moduledata/moduleini/Module.extend.php';
                if(isset($moduleExtend['modules']) and !empty($moduleExtend['modules'])) {
                    $array = array();
                    foreach($moduleExtend['modules'] as $key => $value) {
                        if($value != $pluginInfo->plugin_code) {
                            $array['modules'][$key] = $value;
                        }
                    }
                    if(!isset($array['modules'])) $array['modules'] = array();

                    $phpWrite = new PhpArray();
                    $phpWrite->toFile(DBSHOP_PATH . '/data/moduledata/moduleini/Module.extend.php', $array);
                }
                //清空ZF2缓存设置
                $this->zfdbshopClearConfigCache();
                //缓存清理
                DbshopOpcache::reset();
            }
        }
        return $this->redirect()->toRoute('plugin/default');
    }
    /**
     * 卸载插件
     * @return \Zend\Http\Response
     */
    public function delPluginAction()
    {
        $pluginId = (int) $this->request->getQuery('plugin_id');
        if($pluginId > 0) {
            $pluginInfo = $this->getDbshopTable()->infoPlugin(array('plugin_id'=>$pluginId));
            //插件的目录
            $pluginPath = DBSHOP_PATH . '/module/Extendapp/'.$pluginInfo->plugin_code;
            if(isset($pluginInfo->plugin_state) and $pluginInfo->plugin_state == 2 and is_dir($pluginPath)) {
                //查看是否有需要去除的url
                if(file_exists($pluginPath . '/data/urlArray.php')) {
                    $iniRead    = new Ini();
                    $urlIni     = $iniRead->fromFile(DBSHOP_PATH . '/data/moduledata/moduleini/url.ini');

                    $urlArray   = include $pluginPath . '/data/urlArray.php';
                    if(is_array($urlArray) and !empty($urlArray)) {
                        $urlTag     = key($urlArray);
                        $pluginCode = strtolower($pluginInfo->plugin_code);
                        if(isset($urlIni[$urlTag][$pluginCode])) {
                            unset($urlIni[$urlTag][$pluginCode]);

                            $iniWrite = new \Zend\Config\Writer\Ini();
                            $iniWrite->toFile(DBSHOP_PATH . '/data/moduledata/moduleini/url.ini', $urlIni);
                        }
                    }
                }
                //将插件从module移除
                $moduleExtend = include DBSHOP_PATH . '/data/moduledata/moduleini/Module.extend.php';
                if(isset($moduleExtend['modules']) and !empty($moduleExtend['modules'])) {
                    $array = array();
                    foreach($moduleExtend['modules'] as $key => $value) {
                        if($value != $pluginInfo->plugin_code) {
                            $array['modules'][$key] = $value;
                        }
                    }
                    if(!isset($array['modules'])) $array['modules'] = array();

                    $phpWrite = new PhpArray();
                    $phpWrite->toFile(DBSHOP_PATH . '/data/moduledata/moduleini/Module.extend.php', $array);
                }
                //删除该插件拥有的数据表
                $delSqlFile = $pluginPath . '/sql/deltable.sql';
                if(file_exists($delSqlFile)) {
                    $this->runInstallSql($delSqlFile, $pluginCode);
                }
                //插件记录删除
                $this->getDbshopTable()->delPlugin( array('plugin_id'=>$pluginId));
                //删除插件目录
                $this->delPluginDir($pluginPath);
                //清空ZF2缓存设置
                $this->zfdbshopClearConfigCache();
                //缓存清理
                DbshopOpcache::reset();
            }
        }
        return $this->redirect()->toRoute('plugin/default');
    }
    /**
     * 插件安装列表
     * @return array
     */
    public function installAction()
    {
        if(!class_exists('SoapClient')) return array('soap_state'=>'false');
        $this->checkUpdateUserLogin();

        $array = array();
        //获取插件信息
        try {
            $array['plugin_list'] = $this->dbshopSoapClient('dbshopPluginList');
        } catch (\Exception $e) {
            return array('soap_state'=>'no_link');
        }
        //用于拼接远程图片
        $array['update_url'] = $this->updateUrl;
        //已经安装的插件
        $array['install_plugin'] = array();
        $installPlugin = $this->getDbshopTable()->listPlugin();
        if(is_array($installPlugin) and !empty($installPlugin)) {
            foreach($installPlugin as $value) {
                $array['install_plugin'][] = $value['plugin_code'];
            }
        }

        return $array;
    }
    /**
     * 开始安装插件
     */
    public function startInstallPluginAction()
    {
        $this->checkUpdateUserLogin();

        $pluginId = (int) $this->request->getPost('plugin_id');
        if($pluginId == 0) exit($this->getDbshopLang()->translate('该插件不存在！'));

        $dbshopPluginInfo = $this->getDbshopTable()->infoPlugin(array('plugin_id'=>$pluginId));
        if(isset($dbshopPluginInfo->plugin_id) and $dbshopPluginInfo->plugin_id >0) exit($this->getDbshopLang()->translate('该插件已经安装，无需重复安装！'));

        //系统信息
        include DBSHOP_PATH . '/data/Version.php';
        $installPluginInfo = $this->dbshopSoapClient('dbshopPluginInfo', array('plugin_id'=>$pluginId, 'dbshop_version'=>DBSHOP_VERSION_NUMBER, 'dbshop_url'=>$this->getServiceLocator()->get('frontHelper')->dbshopHttpHost()));
        if(empty($installPluginInfo)) exit($this->getDbshopLang()->translate('该插件不存在！'));
        if(is_array($installPluginInfo) and isset($installPluginInfo->state) and $installPluginInfo->state == 'false') exit($this->getDbshopLang()->translate('系统版本低，无法支持该插件，请您将系统版本升级到').$installPluginInfo->suuport_version);

        $installState = 'false';

        if($this->request->isPost()) {
            $pluginPath = DBSHOP_PATH . '/data/moduledata/Package/plugin';
            if(!is_dir($pluginPath)) mkdir($pluginPath, 0777, true);
            //开始下载
            $ch = curl_init($installPluginInfo->plugin_install_url . $installPluginInfo->plugin_install_package);
            $fp = fopen($pluginPath.'/'.$installPluginInfo->plugin_install_package, "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            //对下载压缩包进行md5比对
            if(md5_file($pluginPath.'/'.$installPluginInfo->plugin_install_package) != $installPluginInfo->plugin_install_md5) exit($this->getDbshopLang()->translate('下载的插件与原始插件包不匹配，无法安装'));
            //开始解压缩
            if(class_exists('ZipArchive')) {//当内置ZipArchive可用时，使用
                $unZip = new \ZipArchive();
                $unZip->open($pluginPath.'/'.$installPluginInfo->plugin_install_package);
                $unZip->extractTo($pluginPath);
                $unZip->close();
            } else {//当内置ZipArchive不可用时，使用第三方组件pclzip
                $unZip = new \PclZip($pluginPath.'/'.$installPluginInfo->plugin_install_package);
                $unZip->extract(PCLZIP_OPT_PATH, $pluginPath);
            }
            //开始安装处理
            $packageDirname = substr($installPluginInfo->plugin_install_package, 0, -4);
            $sourcePath     = $pluginPath.'/'.$packageDirname;
            //数据库更新处理
            if(file_exists($sourcePath . '/module/Extendapp/'.$installPluginInfo->plugin_code.'/sql/table.sql')) {
                $this->runInstallSql($sourcePath . '/module/Extendapp/'.$installPluginInfo->plugin_code.'/sql/table.sql');
            }
            //文件安装处理
            $allInstallFile  = $this->getSunFile($sourcePath);
            if(is_array($allInstallFile) and !empty($allInstallFile)) {
                foreach ($allInstallFile as $installFile) {
                    $coverFile = str_replace('/data/moduledata/Package/plugin/'.$packageDirname, '', $installFile);
                    //更新将新文件覆盖
                    $coverPath = dirname($coverFile);
                    if(!is_dir($coverPath)) mkdir($coverPath, 0777, true);
                    if(!copy($installFile, $coverFile)) exit(sprintf($this->getDbshopLang()->translate('无法正常安装文件，请检查%s对应的目录是否有相关权限'), $coverFile));

                    @unlink($installFile);
                }
                $this->delPluginDir($pluginPath.'/'.$packageDirname);
                @unlink($pluginPath.'/'.$installPluginInfo->plugin_install_package);
            }
            //将插件信息写入数据库
            $pluginArray = array(
                'plugin_id'               =>$installPluginInfo->plugin_id,
                'plugin_name'             =>$installPluginInfo->plugin_name,
                'plugin_author'           =>$installPluginInfo->plugin_author,
                'plugin_author_url'       =>$installPluginInfo->plugin_author_url,
                'plugin_info'             =>$installPluginInfo->plugin_info,
                'plugin_version'          =>$installPluginInfo->plugin_version,
                'plugin_version_num'      =>$installPluginInfo->plugin_version_num,
                'plugin_code'             =>$installPluginInfo->plugin_code,
                'plugin_state'            =>2,
                'plugin_support_url'      =>$installPluginInfo->plugin_support_url,
                'plugin_admin_path'       =>$installPluginInfo->plugin_admin_path,
                'plugin_update_time'      =>time(),
                'plugin_support_version'  =>$installPluginInfo->plugin_support_version_num,
            );
            if($this->getDbshopTable()->addPlugin($pluginArray)) {
                $installState = 'true';
                //清空ZF2缓存设置
                $this->zfdbshopClearConfigCache();
                //缓存清理
                DbshopOpcache::reset();
            }
        }
        exit($installState);
    }
    /**
     * 开始更新插件
     */
    public function startUpdatePluginAction()
    {
        $this->checkUpdateUserLogin();

        $pluginCode = $this->request->getPost('plugin_code');
        if(empty($pluginCode)) exit($this->getDbshopLang()->translate('该插件不存在！'));

        $dbshopPluginInfo = $this->getDbshopTable()->infoPlugin(array('plugin_code'=>$pluginCode));
        if(empty($dbshopPluginInfo)) exit($this->getDbshopLang()->translate('该插件不存在！'));
        //系统信息
        include DBSHOP_PATH . '/data/Version.php';
        $updatePluginInfo = $this->dbshopSoapClient('dbshopPluginVersionInfo', array('plugin_code'=>$pluginCode, 'plugin_version'=>$dbshopPluginInfo->plugin_version_num, 'dbshop_version'=>DBSHOP_VERSION_NUMBER, 'dbshop_url'=>$this->getServiceLocator()->get('frontHelper')->dbshopHttpHost()));
        if(empty($updatePluginInfo)) exit($this->getDbshopLang()->translate('该插件不存在！'));
        if(is_array($updatePluginInfo) and isset($updatePluginInfo->state) and $updatePluginInfo->state == 'false') exit($this->getDbshopLang()->translate('系统版本低，无法支持该插件，请您将系统版本升级到').$updatePluginInfo->suuport_version);

        $updateState = 'false';
        if($this->request->isPost()) {
            $pluginPath = DBSHOP_PATH . '/data/moduledata/Package/plugin/update';
            if(!is_dir($pluginPath)) mkdir($pluginPath, 0777, true);
            //开始下载
            $ch = curl_init($updatePluginInfo->plugin_update_url . $updatePluginInfo->plugin_update_package);
            $fp = fopen($pluginPath.'/'.$updatePluginInfo->plugin_update_package, "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            //对下载压缩包进行md5比对
            if(md5_file($pluginPath.'/'.$updatePluginInfo->plugin_update_package) != $updatePluginInfo->plugin_update_md5) exit($this->getDbshopLang()->translate('下载的更新插件与原始更新插件包不匹配，无法更新'));
            //开始解压缩
            if(class_exists('ZipArchive')) {//当内置ZipArchive可用时，使用
                $unZip = new \ZipArchive();
                $unZip->open($pluginPath.'/'.$updatePluginInfo->plugin_update_package);
                $unZip->extractTo($pluginPath);
                $unZip->close();
            } else {//当内置ZipArchive不可用时，使用第三方组件pclzip
                $unZip = new \PclZip($pluginPath.'/'.$updatePluginInfo->plugin_update_package);
                $unZip->extract(PCLZIP_OPT_PATH, $pluginPath);
            }
            //开始安装处理
            $packageDirname = substr($updatePluginInfo->plugin_update_package, 0, -4);
            $sourcePath     = $pluginPath.'/'.$packageDirname;
            //数据库更新处理
            if(file_exists($sourcePath . '/updatesql/update.sql')) {
                $this->runInstallSql($sourcePath . '/updatesql/update.sql');
            }
            //文件更新处理
            $allUpdateFile  = $this->getSunFile($sourcePath);
            if(is_array($allUpdateFile) and !empty($allUpdateFile)) {
                foreach ($allUpdateFile as $updateFile) {
                    $coverFile = str_replace('/data/moduledata/Package/plugin/update/'.$packageDirname, '', $updateFile);
                    //更新将新文件覆盖
                    $coverPath = dirname($coverFile);
                    if(!is_dir($coverPath)) mkdir($coverPath, 0777, true);
                    if(!copy($updateFile, $coverFile)) exit(sprintf($this->getDbshopLang()->translate('无法正常安装文件，请检查%s对应的目录是否有相关权限'), $coverFile));

                    @unlink($updateFile);
                }
                $this->delPluginDir($pluginPath.'/'.$packageDirname);
                @unlink($pluginPath.'/'.$updatePluginInfo->plugin_update_package);
            }
            //更新插件信息
            $updateArray = array(
                'plugin_version'        => $updatePluginInfo->plugin_update_version,
                'plugin_version_num'    => $updatePluginInfo->plugin_update_version_num,
                'plugin_support_version'=> $updatePluginInfo->plugin_update_support_title,
                'plugin_update_time'    => $updatePluginInfo->plugin_update_time,

                'plugin_name'             =>$updatePluginInfo->plugin_name,
                'plugin_author'           =>$updatePluginInfo->plugin_author,
                'plugin_author_url'       =>$updatePluginInfo->plugin_author_url,
                'plugin_info'             =>$updatePluginInfo->plugin_info,
                'plugin_support_url'      =>$updatePluginInfo->plugin_support_url,
                'plugin_admin_path'       =>$updatePluginInfo->plugin_admin_path,
            );
            if($this->getDbshopTable()->updatePlugin($updateArray, array('plugin_id'=>$dbshopPluginInfo->plugin_id))) {
                $updateState = 'true';
                //清空ZF2缓存设置
                $this->zfdbshopClearConfigCache();
                //缓存清理
                DbshopOpcache::reset();
            }
        }
        exit($updateState);
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName = 'PluginTable')
    {
        if (empty($this->dbTables[$tableName])) {
            $this->dbTables[$tableName] = $this->getServiceLocator()->get($tableName);
        }
        return $this->dbTables[$tableName];
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
     * @param $clientFunction
     * @param array $array
     * @return mixed
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
     * 执行sql文件
     * @param $sqlFile
     * @param string $checkKeyword
     * @return bool
     */
    private function runInstallSql($sqlFile, $checkKeyword='')
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
                if(!empty($checkKeyword)) {//检查sql语句中是否有需要的字符串，如果没有跳过该执行
                    if(strpos($querySql, $checkKeyword) === false) continue;
                }
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
                    if ($file != '.' && $file != '..' && $file != 'updatesql' and $file != '.DS_Store') {
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
     * 删除目录
     * @param $dir
     * @return bool
     */
    private function delPluginDir($dir) {
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!=".." && $file != '.DS_Store') {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    $this->delPluginDir($fullpath);
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
    /**
     * 模板更新后，清空zf2框架的缓存配置
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
}