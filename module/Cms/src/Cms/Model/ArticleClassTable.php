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

namespace Cms\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Cms\Model\ArticleClass as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class ArticleClassTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_article_class';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加文章分类
     * @param array $data
     * @return int|null
     */
    public function addArticleClass (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addArticleClassData($data));
        if($row) {
            $articleClassId   = $this->getLastInsertValue();
            if(isset($data['article_class_path']) and $data['article_class_path'] == 0) {
                $articleClassPath = $articleClassId;
            } else {
                $info = $this->select(array('article_class_id'=>$data['article_class_top_id']))->current();
                $articleClassPath = $info->article_class_path . ',' . $articleClassId;
                //去除重复的处理
                $array = explode(',', $articleClassPath);
                $array = array_unique($array);
                $articleClassPath = implode(',', $array);
            }
            $this->update(array('article_class_path'=>$articleClassPath), array('article_class_id'=>$articleClassId));
            
            return $articleClassId;
        }
        return null;
    }
    /**
     * 文章分类列表
     * @param $language
     * @param array $where
     * @return array
     */
    public function listArticleClass ($language, array $where=array())
    {
        $result = $this->select(function (Select $select) use ($where, $language) {
            $select->join(array('a'=>'dbshop_article_class'), 'a.article_class_top_id=dbshop_article_class.article_class_id', array(), 'left')
            ->columns(array('*', new Expression('COUNT(a.article_class_id) as has_children, (SELECT count(ar.article_id) FROM dbshop_article as ar WHERE ar.article_class_id=dbshop_article_class.article_class_id) as article_num, (SELECT e.article_class_name FROM dbshop_article_class_extend AS e WHERE e.article_class_id=dbshop_article_class.article_class_id and e.language=\'' . $language . '\') as article_class_name')))
            ->where($where)
            ->group('dbshop_article_class.article_class_id')
            ->order(array('dbshop_article_class.article_class_top_id ASC','dbshop_article_class.article_class_sort ASC'));
        });
        return $result->toArray();
    }
    /**
     * 文章分类列表
     * @param $where
     * @param array $order
     * @return array|null
     */
    public function selectArticleClass ($where, array $order=array())
    {
        $result = $this->select(function (Select $select) use ($where,$order) {
            $select->join(array('e'=>'dbshop_article_class_extend'), 'e.article_class_id=dbshop_article_class.article_class_id')
            ->where($where)
            ->order($order);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 文章分类信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoArticleClass (array $where)
    {
        $row = $this->select(function (Select $select) use ($where) {
           $select->join(array('e'=>'dbshop_article_class_extend'), 'e.article_class_id=dbshop_article_class.article_class_id')
           ->where($where);
        });
        return $row->current();
    }
    /**
     * 编辑更新文章分类信息
     * @param array $data
     * @param array $where
     * @return bool
     */
    public function editArticleClass (array $data, array $where)
    {
        $classInfo = $this->select($where)->current();
        $articleClassTopId = $classInfo->article_class_top_id;
        $articleClassPath  = $classInfo->article_class_path;
        
        if(!empty($data)) {
            foreach ($data as $key => $val) {
                if($key == 'article_class_path') {
                    if($data['article_class_top_id'] == $articleClassTopId) {
                        $val = $articleClassPath;
                    } else {
                        $info = $this->select(array('article_class_id'=>$data['article_class_top_id']))->current();
                        $val  = ((isset($info) and !empty($info)) ? $info->article_class_path . ',' : '') . $where['article_class_id'];
                        
                        $result = $this->select("article_class_path like '%" .$articleClassPath. "%'");
                        if($result) {
                            foreach ($result as $value) {
                                if($value->article_class_id != $where['article_class_id']) {
                                    $this->update(array('article_class_path'=>str_replace($articleClassPath, $val, $value->article_class_path)), array('article_class_id'=>$value->article_class_id));
                                }
                            }
                        }
                    }
                }
                $data[$key] = $val;
            }
        }

        //去除article_class_path重复的处理
        if(!empty($data['article_class_path'])) {
            $array = explode(',', $data['article_class_path']);
            $array = array_unique($array);
            $data['article_class_path'] = implode(',', $array);
        }

        $update = $this->update(dbshopCheckInData::updateArticleClassData($data), $where);
        return true;
    }
    /**
     * 编辑更新分类信息，用于批量修改
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateArticleCalss(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 删除文章分类
     * @param array $where
     * @return bool|null
     */
    public function delArticleClass (array $where)
    {
        $classNum = $this->select(array('article_class_top_id'=>$where['article_class_id']))->count();
        if($classNum > 0) return null;
        
        $del  = $this->delete($where);
        if($del) {
            $sql = new Sql($this->adapter);
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_article_class_extend')->where($where))->execute();
            return true;
        }
        return null;
    }
    /**
     * 过滤和排序所有分类，返回一个带有缩进级别的数组
     * @param $spec_class_id    上级id
     * @param $arr              分类数组
     * @return array    过滤排序好的数组
     */
    public function classOptions($spec_class_id,$arr)
    {
        static $class_options = array();
    
        if (isset($class_options[$spec_class_id])) {
            return $class_options[$spec_class_id];
        }
    
        if (!isset($class_options[0])) {
            $level = $last_class_id = 0;
            $options = $class_id_array = $level_array = array();
            while (!empty($arr)) {
                foreach ($arr AS $key => $value) {
                    $class_id = $value['article_class_id'];
                    if ($level == 0 && $last_class_id == 0) {
                        if ($value['article_class_top_id'] > 0) {
                            break;
                        }
    
                        $options[$class_id]					= $value;
                        $options[$class_id]['class_level']	= $level;
                        unset($arr[$key]);
    
                        if ($value['has_children'] == 0) {
                            continue;
                        }
                        $last_class_id  					= $class_id;
                        $class_id_array 					= array($class_id);
                        $level_array[$last_class_id] 		= ++$level;
                        $options[$class_id]['class_leaf']	= 1;
                        continue;
                    }
                    if ($value['article_class_top_id'] == $last_class_id)	{
                        $options[$class_id]					= $value;
                        $options[$class_id]['class_level']	= $level;
                        unset($arr[$key]);
    
                        if ($value['has_children'] > 0)	{
                            if (end($class_id_array) != $last_class_id)	{
                                $class_id_array[] = $last_class_id;
                            }
                            $last_class_id    					= $class_id;
                            $class_id_array[] 					= $class_id;
                            $level_array[$last_class_id]		= ++$level;
                            $options[$class_id]['class_leaf']	= 1;
                        }
                    } elseif ($value['article_class_top_id'] > $last_class_id) {
                        break;
                    }
                }
                $count = count($class_id_array);
                if ($count > 1) {
                    $last_class_id = array_pop($class_id_array);
                }
                elseif ($count == 1) {
                    if ($last_class_id != end($class_id_array)) {
                        $last_class_id = end($class_id_array);
                    } else {
                        $level = 0;
                        $last_class_id = 0;
                        $class_id_array = array();
                        continue;
                    }
                }
                if ($last_class_id && isset($level_array[$last_class_id])) {
                    $level = $level_array[$last_class_id];
                } else {
                    $level = 0;
                }
            }
            $class_options[0] = $options;
        } else {
            $options = $class_options[0];
        }
    
        if (!$spec_class_id) {
            return $options;
        } else {
            if (empty($options[$spec_class_id])) {
                return array();
            }
    
            $spec_class_id_level = $options[$spec_class_id]['class_level'];
    
            foreach ($options AS $key => $value) {
                if ($key != $spec_class_id) {
                    unset($options[$key]);
                } else {
                    break;
                }
            }
    
            $spec_class_id_array = array();
            foreach ($options AS $key => $value) {
                if (($spec_class_id_level == $value['class_level'] && $value['article_class_id'] != $spec_class_id) ||
                ($spec_class_id_level > $value['class_level'])) {
                    break;
                } else {
                    $spec_class_id_array[$key] = $value;
                }
            }
            $class_options[$spec_class_id] = $spec_class_id_array;
            return $spec_class_id_array;
        }
    }
    /**
     * 获得分类id下（包括当前id）的所有子分类
     * @param int $class_id     分类id
     * @param $language
     * @return array    子集分类数组
     */
    public function getSunClassId($class_id=0, $language)
    {
        $sub_class		= array();
        $class_array	= $this->listArticleClass($language);
        $class_array	= $this->classOptions($class_id,$class_array);
        if(is_array($class_array) and count($class_array)>0) {
            foreach ($class_array as $v) {
                $sub_class[] = $v['article_class_id'];
            }
        }
    
        return $sub_class;
    }
}

?>