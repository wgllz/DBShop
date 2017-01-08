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

namespace Install\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class InstallController extends AbstractActionController
{
    private $checkState = 'true';
    private $dbTables = array();
    private $translator;
    private $installStepArray = array();
    private $dbQuery;

    
    public function indexAction()
    {
        //检测php版本，如果版本过低提示
        if (version_compare(phpversion(), '5.3.23', '<') === true or version_compare(phpversion(), '7.0', '>=') === true) {
            die('ERROR: Your PHP version is ' . phpversion() . '. DBShop requires PHP 5.3.23 or newer(Version is less than 7.0).<br><br>错误：您的 PHP 版本是 ' . phpversion() . '。DBShop系统支持 PHP5.3.23或者更高版本（目前系统不支持 PHP 7.0 版本）。');
        }
        //检查程序是否安装
        if(file_exists(DBSHOP_PATH . '/data/install.lock')) {
            return $this->redirect()->toRoute('shopfront/default');
        }
        if(file_exists(DBSHOP_PATH . '/data/DatabaseCache.ini.php')) @unlink(DBSHOP_PATH . '/data/DatabaseCache.ini.php');
        
        //清空ZF2缓存设置
        $this->getServiceLocator()->get('adminHelper')->clearZfConfigCache();
        
        $array = array();
        //默认语言包
        $defaultLanguage = $this->getServiceLocator()->get('translator')->getLocale();
        $this->layout()->dbshop_language = $defaultLanguage;
        //安装步骤
        $this->installStepArray = new Container('install_step');
        $this->installStepArray['step_1'] = 'step_1';
        
        //授权协议信息
        $contentPath = DBSHOP_PATH . '/module/Install/data/content/';
        $contentFile = $contentPath . 'zh_CN.php';
        if(file_exists($contentPath . $defaultLanguage . '.php') and $defaultLanguage != 'zh_CN') {
            $contentFile = $contentPath . $defaultLanguage . '.php';
        }
        $installContent           = file_get_contents($contentFile);
        $array['install_content'] = str_replace('{year}', date('Y'), $installContent);
        
        return $array;
    }
    /** 
     * 安装第二步
     * @return multitype:string unknown Ambigous <unknown, string>
     */
    public function installStepAction()
    {
        $this->checkInstallStep($this->params('step'));
        
        $array = array();
        
        $yesIco= '<i class="cus-tick"></i>';        //正确ico图片
        $noIco = '<i class="cus-exclamation"></i>'; //错误ico图片
        /*===========================函数依赖性检查===========================*/
        //操作系统信息
        $array['os_version']  = $yesIco . PHP_OS;
        //WEB服务器信息
        $array['web_version'] = $yesIco . $_SERVER['SERVER_SOFTWARE'];
        //PHP信息
        $array['php_version'] = $yesIco . PHP_VERSION;
        //GD库信息
        $gdInfo = function_exists('gd_info') ? gd_info() : array();
        $array['gd_version'] = empty($gdInfo['GD Version']) ? $noIco : $yesIco . $gdInfo['GD Version'];
        if($array['gd_version'] == '<i class="cus-exclamation"></i>') $this->checkState = 'false';
        
        /*===========================函数依赖性检查===========================*/
        $array['dir_file_check'] = $this->checkDirNamePower();
        
        /*===========================函数依赖性检查===========================*/
        //curl是否开启
        $array['curl_open'] = function_exists('curl_init') ? $yesIco : $noIco;
        if($array['curl_open'] == '<i class="cus-exclamation"></i>') $this->checkState = 'false';
        //file_get_contents是否开启
        $array['file_get_contents_open'] = function_exists('file_get_contents') ? $yesIco : $noIco;
        if($array['file_get_contents_open'] == '<i class="cus-exclamation"></i>') $this->checkState = 'false';
        //file_put_contents是否开启
        $array['file_put_contents_open'] = function_exists('file_put_contents') ? $yesIco : $noIco;
        if($array['file_put_contents_open'] == '<i class="cus-exclamation"></i>') $this->checkState = 'false';
        //PDO判断是否开启
        $array['pdo_open'] = class_exists('PDO') ? $yesIco : $noIco;
        if($array['pdo_open'] == '<i class="cus-exclamation"></i>') {
            $this->checkState = 'false';
            $array['pdo_open'] = $array['pdo_open'] . '&nbsp;<a href="http://help.dbshop.net/index.php/%E7%B3%BB%E7%BB%9F%E9%97%AE%E9%A2%98%E6%B1%87%E6%80%BB#PDO.E5.BC.80.E5.90.AF" target="_blank">'.$this->getDbshopLang()->translate('点击查看开启方法').'</a>';
        }

        //soap判断
        $array['php_soap'] = class_exists('SoapClient') ? $yesIco : $noIco;
        if($array['php_soap'] == '<i class="cus-exclamation"></i>') {
            $this->checkState = 'false';
            $array['php_soap'] = $array['php_soap'] . '&nbsp;<a href="http://help.dbshop.net/index.php/%E7%B3%BB%E7%BB%9F%E9%97%AE%E9%A2%98%E6%B1%87%E6%80%BB#SOAP.E5.BC.80.E5.90.AF" target="_blank">'.$this->getDbshopLang()->translate('点击查看开启方法').'</a>';
        }
        //rewrite判断
        if($array['curl_open'] == '<i class="cus-tick"></i>') {
            $array['curl_open_state'] = 1;//curl状态，用于在rewrite得判断上
            $checkRewriteUlr = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('install/default', array('controller'=>'Install', 'action'=>'installCheckRewrite'));
            $checkRewriteUlr = str_replace('index.php/', '', $checkRewriteUlr);

            //模拟get方式获取信息
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $checkRewriteUlr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $output = curl_exec($ch);
            curl_close($ch);
            $array['rewrite_open'] = ($output == 'true' ? $yesIco : $noIco);
            if($array['rewrite_open'] == '<i class="cus-exclamation"></i>') $this->checkState = 'false';
        }

        $array['check_state'] = $this->checkState;
        
        return $array;
    }
    /** 
     * 安装第三步进入安装信息填写
     * @return multitype:NULL
     */
    public function installStepStartAction()
    {
        $this->checkInstallStep($this->params('step'));
        
        $array = array();
        
        //时区
        $timeZoneFile = 'zh_CN';
        if(file_exists(DBSHOP_PATH . '/data/moduledata/System/timezone/' . $this->getDbshopLang()->getLocale() . '.php') and $timeZoneFile != $this->getDbshopLang()->getLocale()) {
            $timeZoneFile = $this->getDbshopLang()->getLocale();
        }
        $array['time_zone_array'] = include DBSHOP_PATH . '/data/moduledata/System/timezone/' . $timeZoneFile . '.php';

        return $array;
    }
    /** 
     * 安装第四步开始安装并显示安装结果
     */
    public function installFinishAction()
    {
        $this->checkInstallStep($this->params('step'));
        
        $array = array();
        if($this->request->isPost()) {
            $installArray = $this->request->getPost()->toArray();
            
            //安装基础数据表
            $this->runInstallSql('DBShop.sql');
            
            //安裝不同語言對應的內容
            $this->runInstallSql('insertData/' . $this->getServiceLocator()->get('translator')->getLocale() . '.sql');

            //安装地区数据
            if(isset($installArray['installregion']) and $installArray['installregion'] == 1) {
                $this->runInstallSql('insertData/region_' . $this->getServiceLocator()->get('translator')->getLocale() . '.sql');
            }
            
            //系统创始人信息添加
            $addAdminSql = "INSERT INTO dbshop_admin (admin_id, admin_group_id, admin_name, admin_passwd, admin_email, admin_add_time) VALUES (1, 1, '". trim($installArray['adminuser']) ."', '" . $this->getServiceLocator()->get('frontHelper')->getPasswordStr($installArray['adminpasswd']) . "', '" . trim($installArray['adminemail']) . "', '" . time() . "')"; 
            $this->getQuery($addAdminSql);
            
            //修改配置信息
            $configContent = file_get_contents(DBSHOP_PATH . '/module/Install/data/Install/config.ini');
            $configContent = str_replace('{webname}', str_replace('"', '\"', trim($installArray['webname'])), $configContent);
            file_put_contents(DBSHOP_PATH . '/data/moduledata/System/config.ini', $configContent);
            
            //修改时区和模板
            $this->getServiceLocator()->get('adminHelper')->setDbshopSetshopFile('DBSHOP_TIMEZONE', $installArray['webtimezone']);
            
            //安装demo信息
            if(isset($installArray['dbshoptestdata']) and $installArray['dbshoptestdata'] == 1) {
                $this->runInstallSql('DBShopDemo.sql');
            }
            
            //对广告进行重新写入，因为广告中对应的图片和当前安装图片不对应
            $adArray = $this->getDbshopTable('AdTable', 'listAd');
            if(is_array($adArray) and !empty($adArray)) {
                foreach ($adArray as $adVal) {
                    //写入广告内容
                    file_put_contents(DBSHOP_PATH . '/data/moduledata/Ad/default/' . $adVal['ad_class'] . '_' . $adVal['ad_place'] . '_' . $adVal['ad_id'] . '.php', $this->createAdContent($adVal));
                }
            }
            
            //写入系统安装时间
            $versionContent = file_get_contents(DBSHOP_PATH . '/module/Install/data/Install/Version.php');
            $versionContent = str_replace('{install_time}', date("Y-m-d", time()), $versionContent);
            file_put_contents(DBSHOP_PATH . '/data/Version.php', $versionContent);
            
            //写入已经安装文件
            file_put_contents(DBSHOP_PATH . '/data/install.lock', 'lock');
        }
       
        $this->installStepArray = array();
        
        //输出到页面的内容
        $array['webname_front_url'] = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('shopfront/default');
        $array['webname_admin_url'] = $this->getServiceLocator()->get('frontHelper')->dbshopHttpOrHttps() . $this->getServiceLocator()->get('frontHelper')->dbshopHttpHost() . $this->url()->fromRoute('admin/default');
        $array['admin_name']        = $installArray['adminuser'];
        $array['admin_passwd']      = $installArray['adminpasswd'];

        @unlink(DBSHOP_PATH . '/data/DatabaseCache.ini.php');
        @file_get_contents('http://update.dbshop.net/index/installhostname?webShopUrl=' . urlencode($array['webname_front_url']));

        //清空ZF2缓存设置
        $this->getServiceLocator()->get('adminHelper')->clearZfConfigCache();
        
        return $array;
    }
    /** 
     * 检查MySql数据库连接是否正确
     */
    public function checkMysqlConnectAction()
    {
        $ConnectState = 'flase';
        if($this->request->isPost()) {
            try {
                $mysqlArray = $this->request->getPost()->toArray();
                $mysqlArray['dbuser']   = str_replace("'", "\'", $mysqlArray['dbuser']);//将可能存在的单引号转移
                $mysqlArray['dbpasswd'] = str_replace("'", "\'", $mysqlArray['dbpasswd']);
                
                $dsn = "mysql:host={$mysqlArray['dbhost']};port={$mysqlArray['dbport']};dbname={$mysqlArray['dbname']}";
                $db  = new \PDO($dsn, $mysqlArray['dbuser'], $mysqlArray['dbpasswd']);
                
                $ConnectState = 'true';
                
                //获取数据库连接模板文件，替换对应标签
                $dataBaseIni = file_get_contents(DBSHOP_PATH . '/module/Install/data/Install/Database.ini.php');
                $dataBaseIni = str_replace(array('{dbname}', '{dbport}', '{hostname}', '{username}', '{password}'), array($mysqlArray['dbname'], $mysqlArray['dbport'], $mysqlArray['dbhost'], $mysqlArray['dbuser'], (isset($mysqlArray['dbpasswd']) ? $mysqlArray['dbpasswd'] : '')), $dataBaseIni);
                file_put_contents(DBSHOP_PATH . '/data/Database.ini.php', $dataBaseIni);
                file_put_contents(DBSHOP_PATH . '/data/DatabaseCache.ini.php', $dataBaseIni);
                                              
            } catch (\Exception $e) {
                $ConnectState = 'flase';
            }
        }
        exit($ConnectState);
    }
    /**
     * 检测InnoDB是否启用
     */
    public function checkMysqlInnoDBAction()
    {
        if($this->request->isPost()) {
            $state = 'connectflase';
            try {
                $mysqlArray = $this->request->getPost()->toArray();
                $mysqlArray['dbuser']   = str_replace("'", "\'", $mysqlArray['dbuser']);//将可能存在的单引号转移
                $mysqlArray['dbpasswd'] = str_replace("'", "\'", $mysqlArray['dbpasswd']);

                $dsn = "mysql:host={$mysqlArray['dbhost']};port={$mysqlArray['dbport']};dbname={$mysqlArray['dbname']}";
                $db  = new \PDO($dsn, $mysqlArray['dbuser'], $mysqlArray['dbpasswd']);

                $state = 'connecttrue';
            } catch (\Exception $e) {
                $state = 'connectflase';
            }
            if($state == 'connectflase') exit($state);

            $sql = 'SHOW ENGINES';
            $sth = $db->prepare($sql);
            $sth->execute();
            $enginesArray = $sth->fetchAll();
            $innodbState = 'innodbfalse';
            if(is_array($enginesArray) and !empty($enginesArray)) {
                foreach($enginesArray as $value) {
                    if(strtolower($value['Engine']) == 'innodb') {
                        $supportState = strtolower($value['Support']);
                        if($supportState == 'yes' or $supportState == 'default') {
                            $innodbState = 'innodbtrue';
                            break;
                        }
                    }
                }
            }
            exit($innodbState);
        }
        exit();
    }
    /**
     * 检测rewrite
     */
    public function installCheckRewriteAction()
    {
        //检查程序是否安装
        if(file_exists(DBSHOP_PATH . '/data/install.lock')) {
            return $this->redirect()->toRoute('shopfront/default');
        }
        exit('true');
    }
    /**
     * 执行安装sql文件
     * @param unknown $sqlFile
     */
    private function runInstallSql($sqlFile)
    {
        $sql = file_get_contents(DBSHOP_PATH . '/module/Install/data/Install/' . $sqlFile);
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
            $this->dbQuery = new \Zend\Db\Adapter\Adapter(include DBSHOP_PATH . '/data/DatabaseCache.ini.php');
        }
        $query = $this->dbQuery->createStatement($sql);
        $query->prepare();
        return $query->execute();
    }
    private function getResultSet($result)
    {
        if(empty($this->resultSet)) {
            $this->resultSet = new ResultSet();
        }
        return $this->resultSet->initialize($result);
    }
    /** 
     * 检查正确的安装顺序
     * @param unknown $step
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    private function checkInstallStep($step)
    {
        //检查程序是否安装
        if(file_exists(DBSHOP_PATH . '/data/install.lock')) {
            return $this->redirect()->toRoute('shopfront/default');
        }
        
        if(empty($this->installStepArray)) {
            $this->installStepArray = new Container('install_step');
        }
        if(!isset($this->installStepArray['step_1']) and $step == 'step_1') {
            return $this->redirect()->toRoute('install/default');
        }
        if(!isset($this->installStepArray['step_1']) and $step == 'step_2') {
            return $this->redirect()->toRoute('install/default');
        }
        if(!isset($this->installStepArray['step_2']) and $step == 'step_3') {
            return $this->redirect()->toRoute('install/default');
        }
        $this->installStepArray[$step] = $step;
    }
    /** 
     * 检查目录及文件权限
     * @return multitype:unknown string
     */
    private function checkDirNamePower()
    {
        $yesIco= '<i class="cus-tick"></i>';        //正确ico图片
        $noIco = '<i class="cus-exclamation"></i>'; //错误ico图片
        
        $array       = array();
        $dirnameItem = array(
            './data/'                       => '/data/',
			'./data/cache/'                 => '/data/cache/',
			'./data/moduledata/'            => '/data/moduledata/',
			'./data/moduledata/Ad/'         => '/data/moduledata/Ad/',
			'./data/moduledata/Currency/'   => '/data/moduledata/Currency/',
			'./data/moduledata/Dbsql/'      => '/data/moduledata/Dbsql/',
			'./data/moduledata/Email/'      => '/data/moduledata/Email/',
			'./data/moduledata/Errorlog/'   => '/data/moduledata/Errorlog/',
			'./data/moduledata/Express/'    => '/data/moduledata/Express/',
			'./data/moduledata/Navigation/' => '/data/moduledata/Navigation/',
            './data/moduledata/User/'       => '/data/moduledata/User/',
            './data/moduledata/Payment/'    => '/data/moduledata/Payment/',
			'./data/moduledata/Shopfront/'  => '/data/moduledata/Shopfront/',
            './data/moduledata/System/'     => '/data/moduledata/System/',
            './data/moduledata/Upload/'     => '/data/moduledata/Upload/',
            './data/moduledata/Package/'    => '/data/moduledata/Package/',
			
			'./data/Version.php'            => '/data/Version.php',
			'./data/Database.ini.php'       => '/data/Database.ini.php',
						
            './public/'                     => '/public/',
            './public/upload/ad/'           => '/public/upload/ad/',
            './public/upload/brand/'        => '/public/upload/brand/',
            './public/upload/captcha/'      => '/public/upload/captcha/',
            './public/upload/class/'        => '/public/upload/class/',
            './public/upload/common/'       => '/public/upload/common/',
            './public/upload/editor/'       => '/public/upload/editor/',
            './public/upload/goods/'        => '/public/upload/goods/',
            './public/upload/links/'        => '/public/upload/links/',
			
			'./config/autoload/'            => '/config/autoload/',
           
            './module/'                     => '/module/',
        );
        
        foreach($dirnameItem as $key => $item) {
            $itemDir   = DBSHOP_PATH . $item;
            if(is_dir($itemDir)) {
                if(!$this->dirWriteable($itemDir)) {
                    $array[$key]      = $noIco;
                    $this->checkState = 'false';
                } else {
                    $array[$key] = $yesIco;
                }
            } else {
                if(file_exists($itemDir)) {
                    if(is_writable($itemDir)) {
                        $array[$key] = $yesIco;
                    } else {
                        $array[$key]      = $noIco;
                        $this->checkState = 'false';
                    }
                }
            }
        }
        return $array;
    }
    /** 
     * 检查是否可写
     * @param unknown $dir
     * @return number
     */
    function dirWriteable($dir)
    {
        $writeable = 0;
        if(!is_dir($dir)) {
            @mkdir($dir, 0777);
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
     * 安装时选择默认语言并设置
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function changeDefaultLanguageAction ()
    {
        $defaultLanguage = $this->request->getQuery('language');
        $fromPath        = DBSHOP_PATH . '/module/Install/data/Install/languageConf/' . $defaultLanguage . '/language.local.php';
        $toPath          = DBSHOP_PATH . '/config/autoload/language.local.php';
        
        if(file_exists($fromPath)) {
            copy($fromPath, $toPath);
        }
        
        return $this->redirect()->toRoute('install/default');
    }
    /**
     * 生成广告内容文件
     * @param unknown $content
     * @param unknown $adType
     * @param unknown $adId
     */
    private function createAdContent($adInfo)
    {
        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
        $content  = file_get_contents(DBSHOP_PATH . '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/ad/' . $adInfo['ad_type'] . '.phtml');
        $template = file_get_contents(DBSHOP_PATH . '/module/Shopfront/view/' . DBSHOP_TEMPLATE . '/shopfront/ad/' . $adInfo['ad_class'] . '_' . $adInfo['ad_place'] . '.phtml');
        switch ($adInfo['ad_type']) {
            case 'text':
            case 'code':
                $content = str_replace(array('{ad_url}', '{ad_body}'), array($adInfo['ad_url'], $adInfo['ad_body']), $content);
                break;
            case 'image':
                $content = str_replace(array('{ad_url}', '{ad_body}'), array($adInfo['ad_url'], $renderer->basePath($adInfo['ad_body'])), $content);
                break;
            case 'slide':
                $adTemplate    = explode('<dbshop>', $content);
                $adTemplate[0] = str_replace('{randid}', time(), $adTemplate[0]);
                $adTemplate[2] = str_replace('{randid}', time(), $adTemplate[2]);
                $slideArray    = $this->getDbshopTable('AdSlideTable', 'listAdSlide', array('ad_id'=>$adInfo['ad_id']));
                $slideContent  = '';
                if(is_array($slideArray) and !empty($slideArray)) {
                    foreach ($slideArray as $key => $value) {
                        $slideContent .= str_replace(array('{ad_url}', '{image}', '{ad_body}'), array($value['ad_slide_url'], $renderer->basePath($value['ad_slide_image']), $value['ad_slide_info']), ($key == 0 ? str_replace('{active}', 'active ', $adTemplate[1]) : str_replace('{active}', '', $adTemplate[1])));
                    }
                }
                $content = $adTemplate[0] . $slideContent . $adTemplate[2];
                break;
        }
        $content = str_replace('{content}', $content, $template);
    
        return $content;
    }
    /**
     * 数据表调用
     * @param string $tableName
     * @return multitype:
     */
    private function getDbshopTable ($tableName, $selectInfo, $where=array())
    {
        $result = '';
        if($tableName == 'AdTable') {
            if($selectInfo == 'listAd') {
                $dbshopSql = new TableGateway('dbshop_ad', $this->dbQuery);
                $result    = $dbshopSql->select()->toArray();
                
            }
        }
        if($tableName == 'AdSlideTable') {
            if($selectInfo == 'listAdSlide') {
                $dbshopSql = new TableGateway('dbshop_ad_slide', $this->dbQuery);
                $result    = $dbshopSql->select(function (Select $select) use ($where) {
                    $select->where($where)->order('ad_slide_sort ASC');
                })->toArray();
            }
        }
        return $result;
    }
    /**
     * 语言包调用
     * @return Ambigous <object, multitype:, \Zend\I18n\Translator\Translator>
     */
    private function getDbshopLang ()
    {
        if (!$this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }
}
