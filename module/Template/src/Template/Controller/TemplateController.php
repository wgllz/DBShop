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

namespace Template\Controller;

use Admin\Controller\BaseController;
use Zend\View\Model\ViewModel;

class TemplateController extends BaseController
{
    private $soapclient;
    private $updateUrl= 'https://update.dbshop.net';
    private $location = 'https://update.dbshop.net/packageservice';
    private $uri      = 'dbshop_package_update';

    /** 
     * 模板列表首页
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $array = array();
        //系统信息
        include DBSHOP_PATH . '/data/Version.php';

        $templateIniReader = new \Zend\Config\Reader\Ini();
        $dbshopTemplate    = DBSHOP_TEMPLATE;
        
        $templateName   = trim($this->params('template_name'));
        if(!empty($templateName)) {
            $templatePath = DBSHOP_PATH . '/module/Shopfront/view/'.$templateName.'/';
            $templateCss  = DBSHOP_PATH . '/public/css/'.$templateName.'/css/'.$templateName.'.css';
            
            if(is_dir($templatePath) and file_exists($templateCss)) $dbshopTemplate = $templateName;
        }

        $where = '';
        //已启用模板信息
        $defaultIni = $templateIniReader->fromFile(DBSHOP_PATH . '/module/Shopfront/view/'.$dbshopTemplate.'/shopfront/template.ini');
        $defaultTem = array(
            'template_name'=>$dbshopTemplate,
            'template_info'=>(isset($defaultIni['template_info']) ? $defaultIni['template_info'] : array())
        );
        //组合where语句，用语webservice
        $where .= "shop_template.template_str='".$dbshopTemplate."' or ";

        //未启用模板信息
        $array['other_template'] = array();
        $array['other_template'][] = $defaultTem;//已启用模板放在数组第一位

        $templatePath = DBSHOP_PATH . '/module/Shopfront/view/';
        if(is_dir($templatePath)) {
            $dh = opendir($templatePath);
            while (false !== ($dirName = readdir($dh))) {
                if($dirName != '.' and $dirName != '..' and $dirName != $dbshopTemplate and $dirName != '.DS_Store') {
                    $otherIni = array();
                    $otherIni = $templateIniReader->fromFile($templatePath . $dirName . '/shopfront/template.ini');
                    $array['other_template'][] = array(
                        'template_name' => $dirName,
                        'template_info' => (isset($otherIni['template_info']) ? $otherIni['template_info'] : array())
                    );
                    //组合where语句，用语webservice
                    $where .= "shop_template.template_str='".$dirName."' or ";
                }
            }
        }
        //webservice检查更新，必须开启soap
        $onlineTemplate = array();
        if(class_exists('SoapClient') and !empty($where)) {
            $where = substr($where, 0, -4);
            try {
                $onlineTemplate = $this->dbshopSoapClient('dbshopTemplateList', array($where, 'v.support_version<='.DBSHOP_VERSION_NUMBER));
            } catch (\Exception $e) {

            }
            if(!empty($onlineTemplate)) {
                foreach($onlineTemplate as $templateValue) {
                    $array['onlineTemplte'][$templateValue['template_str']] = $templateValue;
                }
            }
        }

        if(!empty($templateName)) {
            //清空ZF2缓存设置
            $this->zfdbshopClearConfigCache();
        }

        return $array;
    }
    /** 
     * 选择模板
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function changeTemplateAction()
    {
        $templateName = trim($this->params('template_name'));
        $templatePath = DBSHOP_PATH . '/module/Shopfront/view/'.$templateName.'/';
        $templateCss  = DBSHOP_PATH . '/public/css/'.$templateName.'/css/'.$templateName.'.css';
        //如果模板目录或者对应css不存在则不进行处理
        if(!is_dir($templatePath) or !file_exists($templateCss)) return $this->redirect()->toRoute('template/default');
        
        $this->getServiceLocator()->get('adminHelper')->setDbshopSetshopFile(array('DBSHOP_TEMPLATE'=>$templateName, 'DBSHOP_TEMPLATE_CSS'=>$templateName));

        //查看缓存是否开启，如果开启则进行缓存清除
        if(defined('FRONT_CACHE_STATE') and FRONT_CACHE_STATE == 'true') {
            $this->getServiceLocator()->get('frontCache')->flush();
        }

        //记录操作日志
        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('模板设置操作'), 'operlog_info'=>$this->getDbshopLang()->translate('启用模板') . '&nbsp;' . $templateName));

        return $this->redirect()->toRoute('template/default/templateinfo', array('controller'=>'Template', 'action'=>'index', 'template_name'=>$templateName));
    }
    /**
     * 在线模板安装列表
     * @return array
     */
    public function installTemplateAction()
    {
        if(!class_exists('SoapClient')) return array('soap_state'=>'false');
        $this->checkUpdateUserLogin();

        $array = array();
        try {
            $array['online_template_list'] = $this->dbshopSoapClient('dbshopTemplateList');
        } catch (\Exception $e) {
            return array('soap_state'=>'no_link');
        }
        $array['update_url'] = $this->updateUrl;

        //已安装模板信息
        $array['installed_template'] = array();
        $templatePath = DBSHOP_PATH . '/module/Shopfront/view/';
        if(is_dir($templatePath)) {
            $dh = opendir($templatePath);
            while (false !== ($dirName = readdir($dh))) {
                if($dirName != '.' and $dirName != '..' and $dirName != '.DS_Store') {
                    $array['installed_template'][] = $dirName;
                }
            }
        }

        return $array;
    }
    /**
     * 安装模板
     */
    public function startInstallTemplateAction()
    {
        set_time_limit(0);
        $loginUserInfo = $this->checkUpdateUserLogin();

        $templateId = (int) $this->request->getPost('template_id');
        if($templateId == 0) exit($this->getDbshopLang()->translate('该模板不存在！'));

        $templateInfo = $this->dbshopSoapClient('dbshopTemplateInfo', array('v.template_id'=>$templateId, 'dbshop_version'=>DBSHOP_VERSION_NUMBER, 'dbshop_url'=>$this->getServiceLocator()->get('frontHelper')->dbshopHttpHost()));
        if(empty($templateInfo)) exit($this->getDbshopLang()->translate('该模板不存在！'));
        if(is_array($templateInfo) and isset($templateInfo['state']) and $templateInfo['state'] == 'false') exit($this->getDbshopLang()->translate('系统版本低，无法支持该模板，请您将系统版本升级到').' V'.$templateInfo['suuport_version']);

        if($this->request->isPost()) {
            $packagePath  = DBSHOP_PATH . '/data/moduledata/Package/template/';
            if(!is_dir($packagePath)) mkdir($packagePath, 0777, true);

            $installState = 'false';
            //如果php版本是5.4以上的，进行模板包重命名(被注释掉的代码是之前程序加密时使用的)
            /*if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
                $templateInfo->template_package = 'PHP5.4_' . $templateInfo->template_package;
            }*/
            //开始下载
            $ch = curl_init($templateInfo->template_package_download_url . $templateInfo->template_package);
            $fp = fopen($packagePath.$templateInfo->template_package, "w");
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            //对下载压缩包进行md5比对(被注释掉的代码是之前程序加密时使用的)
            /*if(version_compare(PHP_VERSION, '5.4.0', '>=')) {
                if(md5_file($packagePath.$templateInfo->template_package) != $templateInfo->template_package_md5_54) exit($this->getDbshopLang()->translate('下载的模板包与原始模板包不匹配，无法更新'));
            } else {
                if(md5_file($packagePath.$templateInfo->template_package) != $templateInfo->template_package_md5) exit($this->getDbshopLang()->translate('下载的模板包与原始模板包不匹配，无法更新'));
            }*/
            //对下载压缩包进行md5比对
            if(md5_file($packagePath.$templateInfo->template_package) != $templateInfo->template_package_md5) exit($this->getDbshopLang()->translate('下载的模板包与原始模板包不匹配，无法更新'));
            //开始解压缩
            if(class_exists('ZipArchive')) {//当内置ZipArchive可用时，使用
                $unZip = new \ZipArchive();
                $unZip->open($packagePath.$templateInfo->template_package);
                $unZip->extractTo($packagePath);
                $unZip->close();
            } else {//当内置ZipArchive不可用时，使用第三方组件pclzip
                $unZip = new \PclZip($packagePath.$templateInfo->template_package);
                $unZip->extract(PCLZIP_OPT_PATH, $packagePath);
            }
            //开始更新处理
            $packageDirname = substr($templateInfo->template_package, 0, -4);
            $sourcePath     = $packagePath . $packageDirname;

            //文件更新处理
            $allUpdateFile  = $this->getSunFile($sourcePath);
            if(is_array($allUpdateFile) and !empty($allUpdateFile)) {
                foreach ($allUpdateFile as $updateFile) {
                    $coverFile = str_replace('/data/moduledata/Package/template/'.$packageDirname, '', $updateFile);
                    //更新将新文件覆盖
                    $coverPath = dirname($coverFile);
                    if(!is_dir($coverPath)) mkdir($coverPath, 0777, true);
                    if(!copy($updateFile, $coverFile)) exit(sprintf($this->getDbshopLang()->translate('无法正常更新文件，请检查%s对应的目录是否有相关权限'), $coverFile));

                    @unlink($updateFile);
                }
                $this->delTemplateDir($packagePath.$packageDirname);
                @unlink($packagePath.$templateInfo->template_package);
                $updateState = 'true';
            }
            exit($updateState);
        }
        exit('true');
    }
    /**
     * ajax获取模板信息
     * @return ViewModel
     */
    public function templateInfoAction()
    {
        $templateId = (int) $this->request->getPost('template_id');
        if($templateId == 0) exit('false');

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        $array = array();

        $array['template_info'] = $this->dbshopSoapClient('dbshopTemplateInfo', array('v.template_id'=>$templateId));

        return $viewModel->setVariables($array);
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
                    if ($file != '.' && $file != '..' && $file != 'sql' and $file != '.DS_Store') {
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
    private function delTemplateDir($dir) {
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!=".." && $file != '.DS_Store') {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    $this->delTemplateDir($fullpath);
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
