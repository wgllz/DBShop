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

namespace Dbsql\Controller;

use Zend\Db\ResultSet\ResultSet;
use Admin\Controller\BaseController;

class DbsqlController extends BaseController
{
    private $dbQuery;
    private $resultSet;
    private $dumpSql;
    private $errorSql;
    private $dbshopPdo;
    private $maxSize=0;
    private $sqlNum=0;
    private $offSet=300;
    private $isShort=false;
    /** 
     * 备份列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction ()
    {
        $array = array();

        $realList = array();
        $backPath = DBSHOP_PATH . '/data/moduledata/Dbsql/';
        $folder   = opendir($backPath);
        while (false !== ($file = readdir($folder))) {
            if(strpos($file, '.sql') !== false) {
                $realList[] = $file;
            }
        }
        natsort($realList);
        
        $match = array();
        foreach ($realList as $file) {
            if (preg_match('/_([0-9])+\.sql$/', $file, $match)) {
                if ($match[1] == 1) {
                    $mark = 1;
                } else {
                    $mark = 2;
                }
            } else {
                $mark = 0;
            }
            
            $fileSize = filesize($backPath . $file);
            $info     = $this->getHead($backPath . $file);
            $array[]  = array('name' => $file, 'add_time' => $info['date'], 'vol' => $info['vol'], 'file_size' => $this->numBitunit($fileSize), 'mark' => $mark);
        }
        //排序操作
        usort($array, function ($a, $b) {
            if($a['add_time'] == $b['add_time']) {
                return 0;
            }
            return ($a['add_time'] > $b['add_time']) ? -1 : 1;
        });
        
        return array('sql'=>$array);
    }
    /** 
     * 删除sql备份文件
     */
    public function delbackupAction ()
    {
        $sqlFileName = str_replace(array('/', '\\'), array('', ''),trim($this->request->getPost('sql_file_name')));
        if(!empty($sqlFileName)) {
            $name = substr($sqlFileName, 0, strrpos($sqlFileName, '_'));
            $backPath = DBSHOP_PATH . '/data/moduledata/Dbsql/';
            $folder   = opendir($backPath);
            while (false !== ($file = readdir($folder))) {
                if(strpos($file, '.sql') !== false) {
                    if($sqlFileName == $file) {
                        @unlink($backPath . $file);
                    } else {
                        $fileName = substr($file, 0, strrpos($file, '_'));
                        if(!empty($name) and !empty($fileName) and $fileName == $name) unlink($backPath . $file);
                    }
                }
            }
            //删除备份的config的data目录
            $delDir = (empty($name) ? substr($sqlFileName, 0, strrpos($sqlFileName, '.')) : $name);
            if(!empty($delDir)) {
                $this->getServiceLocator()->get('adminHelper')->delDirAndFile(DBSHOP_PATH . '/data/moduledata/moduledataback/'.$delDir);
            }

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('数据库备份'), 'operlog_info'=>$this->getDbshopLang()->translate('成功删除备份文件') . '&nbsp;' . $sqlFileName));
            
            exit('true');
        }
        exit('false');
    }
    /** 
     * 备份操作
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function backupAction ()
    {
        $backState = false;
        $backPath = DBSHOP_PATH . '/data/moduledata/Dbsql/';                //sql备份的目录
        $dataBackPath = DBSHOP_PATH . '/data/moduledata/moduledataback/';   //同时间下data的备份目录
        $backState = array();
        //检查sql备份的目录
        if(!@fopen($backPath . 'test.txt', 'w')) {
            exit($backPath . $this->getDbshopLang()->translate('目录没有写权限！'));
        }
        @unlink($backPath . 'test.txt');
        //检查data的备份目录
        if(!is_dir($dataBackPath)) mkdir(rtrim($dataBackPath),0777);
        if(!@fopen($dataBackPath . 'test.txt', 'w')) {
            exit($dataBackPath . $this->getDbshopLang()->translate('目录没有写权限！'));
        }
        @unlink($dataBackPath . 'test.txt');
        
        $backState = $this->runDbshopBack($backPath);
        if($backState[0] == 0) {
            exit($backState[1]['name'] . $this->getDbshopLang()->translate(' 备份失败！'));
        }
        
        if($backState[0] == 1) {//备份成功
            //开始备份data
            $this->backConfigFile($dataBackPath);

            //记录操作日志
            $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('数据库备份'), 'operlog_info'=>$this->getDbshopLang()->translate('成功备份数据库') . '&nbsp;' . $backState[1]['name']));
            
            return $this->redirect()->toRoute('dbsql/default',array('controller'=>'index'));
        }
        
        if($backState[0] == 2) {
            return $this->redirect()->toRoute('dbsql/default/backup',array('action'=>'backup', 'sql_file_name'=>$backState[1]['sql_file_name'], 'vol'=>$backState[1]['vol']));
        }
    }
    /** 
     * 导入操作
     */
    public function importbackupAction ()
    {
        @set_time_limit(500);
        $sqlFileName = str_replace(array('/', '\\'), array('', ''),$this->params('sql_file_name'));
        if(empty($sqlFileName)) {
            exit($this->getDbshopLang()->translate('导入文件不存在！') . '&nbsp;<a href="'.$this->url()->fromRoute('dbsql/default').'">' . $this->getDbshopLang()->translate('返回') . '</a>');
        }
        
        $backPath = DBSHOP_PATH . '/data/moduledata/Dbsql/';
        if (preg_match('/_[0-9]+\.sql$/', $sqlFileName)) {
            $name = substr($sqlFileName, 0, strrpos($sqlFileName, '_'));
            
            $folder   = opendir($backPath);
            $postList = array();
            while (false !== ($file = readdir($folder))) {
                if(preg_match('/_[0-9]+\.sql$/', $file) and is_file($backPath . $file)) {
                        $fileName = substr($file, 0, strrpos($file, '_'));
                        if($fileName == $name) $postList[] = $file;
                    }
                }
                natsort($postList);
                if(is_array($postList) and !empty($postList)) {
                    foreach ($postList as $value) {
                        //$info = $this->getHead($backPath . $value);
                        if(!$this->importSql($backPath . $value)) return false;
                    }     
                }
        } else {
            if(!$this->importSql($backPath . $sqlFileName)) return false;
        }
        //还原config的备份文件
        $importDir = (empty($name) ? substr($sqlFileName, 0, strrpos($sqlFileName, '.')) : $name);
        if(!empty($importDir)) {
            $this->importConfigFile(DBSHOP_PATH . '/data/moduledata/moduledataback/' .$importDir, $importDir);
        }
        //记录操作日志
        $this->insertOperlog(array('operlog_name'=>$this->getDbshopLang()->translate('数据库备份'), 'operlog_info'=>$this->getDbshopLang()->translate('成功导入备份文件') . '&nbsp;' . $sqlFileName));
       
        exit('<strong>' . $this->getDbshopLang()->translate('数据导入成功！') . '</strong>&nbsp;<a href="'.$this->url()->fromRoute('dbsql/default').'">' . $this->getDbshopLang()->translate('返回') . '</a>'.(!empty($this->errorSql) ? '<br><br><strong>'.$this->getDbshopLang()->translate('下面的语句导入失败，失败原因包含非法数据或者其他，您可以尝试进行手工处理或者忽略').'</strong><br>'.$this->errorSql : ''));
    }
    /** 
     * sql导入
     * @param unknown $sqlFilename
     * @return boolean
     */
    private function importSql ($sqlFilename)
    {
        $sqlStr = '';
        $lines  = file($sqlFilename);
        
        foreach ($lines as $v) {
            if(substr($v, 0, 2) != '--') $sqlStr .= $v; 
        }
        $sqlStr   = str_replace("\r", '', $sqlStr);
        $ret      = explode(";\n", $sqlStr);
        $retCount = count($ret);
        
        for($i=0; $i<$retCount; $i++) {
            $ret[$i] = trim($ret[$i], " \r\n;");
            if (!empty($ret[$i])) {
                if ((strpos($ret[$i], 'CREATE TABLE') !== false) && (strpos($ret[$i], 'DEFAULT CHARSET=utf8')=== false)) {
                    $ret[$i] = $ret[$i] . ' DEFAULT CHARSET=utf8';
                }
                $this->importSqlQuery($ret[$i]);
                //$this->getQuery($ret[$i]);
            }
        }
        return true;
    }
    private function importSqlQuery($sql)
    {
        if(empty($this->dbshopPdo)) {
            $databaseConfig  = include DBSHOP_PATH.'/data/Database.ini.php';
            $this->dbshopPdo = new \PDO($databaseConfig['dsn'], $databaseConfig['username'], $databaseConfig['password'], $databaseConfig['driver_options']);
        }
        $sql = str_replace(';<!--rn-->', ";\r\n", $sql);
        $sql = str_replace(';<!--n-->', ";\n", $sql);
        if(!$this->dbshopPdo->query($sql)){
            $this->errorSql .= $sql . ';<br>';
        }
    }
    /** 
     * 备份数据库操作
     * @param unknown $backPath
     */
    private function runDbshopBack ($backPath)
    {
        @set_time_limit(1000);

        $sqlFileName = $this->params('sql_file_name');
        $vol = $this->params('vol');
        $type = $this->params('type');

        $runLog = $backPath . 'run.log';

        $maxSize = 1024;
        $allowMaxSize = intval(ini_get('upload_max_filesize'));

        if ($allowMaxSize > 0 and $maxSize > ($allowMaxSize * 1024)) {
            $maxSize = $allowMaxSize * 1024;
        }
        if($maxSize > 0) {
            $this->maxSize = $maxSize * 1024;    
        }
 
        $tables = array();
        switch ($type) {
        case 'full':
            $temp = $this->getResultSet($this->getQuery('SHOW TABLES'))->toArray();
            foreach ($temp as $table) {
                $tables[current($table)] = -1;
            }
            $this->putTableList($runLog, $tables);
            break;
            
        case 'stand':
            $temp = $this->getResultSet($this->getQuery("SHOW TABLES LIKE 'dbshop_%'"))->toArray();
            foreach ($temp as $table) {
                $tables[current($table)] = -1;
            }
            $this->putTableList($runLog, $tables);
            break;
        }

        //开始备份
        $tables = $this->dumpTable($runLog, $vol);
        if($tables === false) {
            exit('dataup error');
        }
        if(empty($tables)) {
            if($vol > 1) {
                if(!@file_put_contents($backPath . $sqlFileName . '_' . $vol . '.sql', $this->dumpSql)) {
                    return array(0, array('name'=>$sqlFileName . '_' . $vol . '.sql'));
                }
                $list = array();
                for($i=0; $i <= $vol; $i++) {
                    $list[] = $sqlFileName . '_' . $i . '.sql';
                }
                return array(1, $list);
            } else {
                if(!@file_put_contents($backPath . $sqlFileName . '.sql', $this->dumpSql)) {
                    return array(0, array('name'=>$sqlFileName . '_' . $vol . '.sql'));
                }
                return array(1, array('name'=>$sqlFileName . '.sql'));
            }  
        } else {
            if(!@file_put_contents($backPath . $sqlFileName . '_' . $vol . '.sql', $this->dumpSql)) {
                return array(0, array('name'=>$sqlFileName . '_' . $vol . '.sql'));
            }
            
            return array(2, array('file_name'=>$sqlFileName . '_' . $vol . '.sql', 'sql_file_name'=>$sqlFileName, 'vol_size'=>$maxSize, 'vol'=>$vol+1));
        }
    }
    
    private function dumpTable($path, $vol)
    {
        $tables = $this->getTableList($path);
        if($tables === false) {
            exit($path . ' is not exists');
        }
        if(empty($tables)) {
            return $tables;
        }
        
        $this->dumpSql = $this->makeHead($vol);
        foreach ($tables as $table => $pos) {
            if($pos == -1) {
               //获取表定义，如果没有超过限制则保存
               $tableDf = $this->getTableDf($table, true);
               if(strlen($this->dumpSql) + strlen($tableDf) > $this->maxSize - 32) {
                   if($this->sqlNum == 0) {
                       $this->dumpSql .= $tableDf;
                       $this->sqlNum  += 2;
                       $tables[$table] = 0;
                   }
                   break;
                   
               } else {
                   $this->dumpSql .= $tableDf;
                   $this->sqlNum  += 2;
                   $pos = 0;
               }
            }
            $postPos = $this->getTableData($table, $pos);
            if($postPos == -1) {
                unset($tables[$table]);//该表完成，清除该表
            } else {
                $tables[$table] = $postPos;//该表未完成。说明将要到达上限,记录备份数据位置
                break;
            }
        }

        $this->dumpSql .= "\r\n\r\n-- END dbshop SQL Dump";
        $this->putTableList($path, $tables);
        
        return $tables;
    }
    
    private function getTableData($table, $pos)
    {
        $postPos = $pos;
        $total1  = $this->getResultSet($this->getQuery("SELECT COUNT(*) FROM $table"))->toArray();
        $total   = current($total1[0]);
        
        if($total == 0 or $pos >= $total) {
            return -1;
        }
        
        $cycleTime = ceil(($total - $pos)/$this->offSet);
        
        for($i=0; $i<$cycleTime; $i++) {
            $data      = $this->getResultSet($this->getQuery("SELECT * FROM $table LIMIT " . ($this->offSet * $i + $pos) . ', ' . $this->offSet))->toArray();
            $dataCount = count($data);
            
            for ($k=0; $k < count($data[0]); $k++) {
                unset($data[0][$k]);
            }
            unset($k);
            $fields   = array_keys($data[0]);
            $startSql = "INSERT INTO `$table` ( `" . implode("`, `", $fields) . "` ) VALUES ";
            
            for($j=0; $j < $dataCount; $j++) {
                for($k=0; $k < count($data[$j]); $k++) {
                    unset($data[$j][$k]);
                }
                unset($k);

                if(is_array($data[$j]) and !empty($data[$j])) {
                    foreach($data[$j] as $data_key => $data_value) {
                        $data[$j][$data_key] = str_replace("'", "\'", $data_value);
                        $data[$j][$data_key] = str_replace(";\r\n", ';<!--rn-->', $data[$j][$data_key]);
                        $data[$j][$data_key] = str_replace(";\n\r", ';<!--rn-->', $data[$j][$data_key]);
                        $data[$j][$data_key] = str_replace(";\n", ';<!--n-->', $data[$j][$data_key]);
                    }
                }
               $record = $data[$j];
               //$record = str_replace("'", "\'", $data[$j]);

                if($this->isShort) {
                    if($postPos == $total -1) {
                        $tmpDumpSql = " ( '" . implode("', '" , $record) . "' );\r\n";
                    } else {
                        $tmpDumpSql = " ( '" . implode("', '" , $record) . "' ),\r\n";
                    }
                    
                    if($postPos == $pos) {
                        $tmpDumpSql = $startSql . "\r\n" . $tmpDumpSql;
                    }
                } else {
                    $tmpDumpSql = $startSql . " ('" . implode("', '" , $record) . "');\r\n";
                }
                
                $tmpDumpSql = str_replace("''", "NULL", $tmpDumpSql);
                
                if(strlen($this->dumpSql) + strlen($tmpDumpSql) > $this->maxSize - 32) {
                    if($this->sqlNum == 0) {
                        $this->dumpSql .= $tmpDumpSql;
                        $this->sqlNum++;
                        $postPos++;
                        if($postPos == $total) {
                            return -1;//所有数据已经完成
                        }
                    }
                    return $postPos;
                } else {
                    $this->dumpSql .= $tmpDumpSql;
                    $this->sqlNum++;
                    $postPos++;
                }
            }
        }
        return -1;//所有数据已经完成
    }
    
    private function getTableDf($table, $addDrop=false)
    {
        $tableDf = '';
        if($addDrop) $tableDf = "DROP TABLE IF EXISTS `$table`;\r\n";
        
        $tmpArray = $this->getResultSet($this->getQuery("SHOW CREATE TABLE `$table`"))->toArray();
        $tmpSql   = $tmpArray[0]['Create Table'];
        $tableDf  .= $tmpSql . ";\r\n";
        
        //注释的部分，去除了Create Table中的 ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 类似内容，这样没有 AUTO_INCREMENT=3的可能会导致出错
        //$tmpSql   = substr($tmpSql, 0, strrpos($tmpSql, ")") + 1); //去除行尾定义
        //$tableDf .= $tmpSql . " ENGINE=MYISAM DEFAULT CHARSET=utf8;\r\n";
        
        return $tableDf;
    }
    
    private function  makeHead($vol)
    {
        /* 系统信息 */
        $sysInfo['php_ver']    = PHP_VERSION;
        $sysInfo['mysql_ver']  = $this->getResultSet($this->getQuery("select version()"))->toArray();
        $sysInfo['date']       = date('Y-m-d H:i:s');
        
        $head = "-- dbshop SQL Dump\r\n".
                "-- \r\n".
                "-- DATE : ".$sysInfo["date"]."\r\n".
                "-- MYSQL SERVER VERSION : ".current($sysInfo['mysql_ver'][0])."\r\n".
                "-- PHP VERSION : ".$sysInfo['php_ver']."\r\n".
                "-- Vol : " . $vol . "\r\n\r\n\r\n";
        
        return $head;        
    }
    
    private function getTableList($path)
    {
        if(!file_exists($path)) {
            return false;
        }
        $array = array();
        $str   = file_get_contents($path);
        if(!empty($str)) {
            $tmpStr = explode("\n", $str);
            foreach ($tmpStr as $val) {
                $val = trim($val, "\r;");
                if (!empty($val)) {
                    list($table, $count) = explode(':',$val);
                    $array[$table] = $count;
                }
            }
        }
        return $array;
    }
    
    private function putTableList($path, $array)
    {
        if(is_array($array)) {
            $str = '';
            foreach ($array as $key => $value) {
                $str .= $key . ':' . $value . ";\r\n";
            }
            @file_put_contents($path, $str);
            return true;
        } else {
            exit('It need a array');
        }
    }
    /** 
     * 用于备份列表
     * @param unknown $path
     * @return multitype:string number unknown
     */
    private function getHead($path) {
        /* 获取sql文件头部信息 */
        $sqlInfo = array('date'=>'', 'mysql_ver'=> '', 'php_ver'=>0, 'vol'=>0);
        $fp = fopen($path,'rb');
        $str = fread($fp, 250);
        fclose($fp);
        $arr = explode("\n", $str);
    
        foreach ($arr AS $val) {
            $pos = strpos($val, ':');
            if ($pos > 0) {
                $type = trim(substr($val, 0, $pos), "-\n\r\t ");
                $value = trim(substr($val, $pos+1), "/\n\r\t ");
                if ($type == 'DATE') {
                    $sqlInfo['date'] = $value;
                } elseif ($type == 'MYSQL SERVER VERSION') {
                    $sqlInfo['mysql_ver'] = $value;
                } elseif ($type == 'PHP VERSION') {
                    $sqlInfo['php_ver'] = $value;
                } elseif ($type == 'Vol') {
                    $sqlInfo['vol'] = $value;
                }
            }
        }
    
        return $sqlInfo;
    }
    /** 
     * 用于备份列表
     * @param unknown $num
     * @return string
     */
    private function numBitunit($num) {
        $bitunit = array(' B',' KB',' MB',' GB');
        for ($key = 0, $count = count($bitunit); $key < $count; $key++) {
            if ($num >= pow(2, 10 * $key) - 1) {// 1024B 会显示为 1KB
                $numBitunitStr = (ceil($num / pow(2, 10 * $key) * 100) / 100) . " $bitunit[$key]";
            }
        }
    
        return $numBitunitStr;
    }
    
    private function getQuery ($sql)
    {
        if (empty($this->dbQuery)) {
            $this->dbQuery = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        }
        $query = $this->dbQuery->createStatement($sql);
        $query->prepare();
        return $query->execute(); 
    }
    private function getResultSet($result)
    {
        if(empty($this->resultSet)) {
            $this->resultSet = new ResultSet;
        }
        return $this->resultSet->initialize($result);
    }
    /**
     * 备份config文件
     * @param $dataBackPath
     */
    private function backConfigFile($dataBackPath)
    {
        $sqlFileName = $this->params('sql_file_name');
        $backConfigPath = $dataBackPath . $sqlFileName;
        if(!is_dir($backConfigPath)) mkdir(rtrim($backConfigPath),0777);
        $allBackConfigFile = $this->getSunFile(DBSHOP_PATH . '/data/moduledata');
        if(is_array($allBackConfigFile) and !empty($allBackConfigFile)) {
            foreach ($allBackConfigFile as $cpFile) {
                $backFile = str_replace('moduledata', 'moduledata/moduledataback/'.$sqlFileName, $cpFile);
                $backDir    = dirname($backFile);
                if(!is_dir($backDir)) mkdir($backDir, 0777, true);
                copy($cpFile, $backFile);
            }
        }
    }
    /**
     * 导入备份配置文件
     * @param $dataBackPath
     * @param $name
     */
    private function importConfigFile($dataBackPath, $name)
    {
        $allImportFile = $this->getSunFile($dataBackPath);
        if(is_array($allImportFile) and !empty($allImportFile)) {
            foreach($allImportFile as $configFile) {
                $importFile = str_replace('/moduledataback/'.$name, '', $configFile);
                $importDir  = dirname($importFile);
                if(!is_dir($importDir)) mkdir($importDir, 0777, true);
                copy($configFile, $importFile);
            }
        }
    }
    /**
     * 列出目录下的所有文件，包括子目录文件,不包含sql目录
     * @param unknown $dirName
     * @return Ambigous <multitype:, multitype:string >
     */
    private function getSunFile($dirName, $array=array('Dbsql', 'moduledataback', 'Errorlog', 'back', 'template', 'updatepack', '.DS_Store'))
    {
        $dirName = rtrim($dirName, '/\\');
        $ret = array();
        if (is_dir($dirName)) {
            if (($dh = @opendir($dirName)) !== false) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && $file != 'sql' && !in_array($file, $array)) {
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
}
