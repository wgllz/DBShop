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

namespace Goods\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Goods\Model\GoodsClass as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class GoodsClassTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_goods_class';
    
    public function setDbAdapter(Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加商品分类
     * @param array $array
     * @return int|null
     */
    public function addGoodsClass (array $array)
    {
        $row = $this->insert(dbshopCheckInData::addGoodsClassData($array));
        if($row) {
            $classId   = $this->getLastInsertValue();
            if(isset($array['class_path']) and $array['class_path'] == 0) {
                $classPath = $classId;
            } else {
                $info      = $this->select(array('class_id'=>$array['class_top_id']))->current();
                $classPath = $info->class_path . ',' . $classId;
                //去除重复的处理
                $array = explode(',', $classPath);
                $array = array_unique($array);
                $classPath = implode(',', $array);
            }
            $this->update(array('class_path'=>$classPath),array('class_id'=>$classId));
            
            return $classId;
        }
        return null;
    }
    /**
     * 编辑商品分类
     * @param array $array
     * @param array $where
     * @return null
     */
    public function editGoodsClass(array $array,array $where)
    {
        $classInfo  = $this->select($where)->current();
        $classTopId = $classInfo->class_top_id;
        $classPath  = $classInfo->class_path;
        //print_r($array);exit;
        if(!empty($array)) {
            foreach ($array as $key => $val) {
                if($key == 'class_path') {
                    if ($array['class_top_id'] == $classTopId) {
                        $val    = $classPath;
                    } else {
                        $info   = $this->select(array('class_id'=>$array['class_top_id']))->current();
                        $val    = ((isset($info) and !empty($info)) ? $info->class_path . ',' : '') . $where['class_id'];
                        $result = $this->select("class_path like '%" .$classPath. "%'");
                        if($result) {
                            foreach ($result as $value) {
                                if($value->class_id != $where['class_id']) {
                                    $this->update(array('class_path'=>str_replace($classPath,$val,$value->class_path)),array('class_id'=>$value->class_id));
                                }
                            }
                        }
                    }
                }
                $array[$key] = $val;
            }
        }
        //去除重复的处理
        if(!empty($array['class_path'])) {
            $data = explode(',', $array['class_path']);
            $data = array_unique($data);
            $array['class_path'] = implode(',', $data);
        }

        $update = $this->update(dbshopCheckInData::updateGoodsClassData($array),array('class_id'=>$where['class_id']));
        if($update) {
            return $where['class_id'];
        }
        return null;
    }
    /**
     * 编辑更新分类信息，用于批量修改
     * @param array $data
     * @param array $where
     * @return int
     */
    public function updateGoodsCalss(array $data, array $where)
    {
        return $this->update($data, $where);
    }
    /**
     * 删除商品分类
     * @param array $where
     * @return int|null
     */
    public function delGoodsClass (array $where)
    {
        $subNum = $this->select(array('class_top_id'=>$where['class_id']))->count();
        if($subNum>0) return null;
        $row      = $this->select($where)->current();
        if($row) {
            @unlink(DBSHOP_PATH . $row->class_icon);
            @unlink(DBSHOP_PATH . $row->class_image);
            return $this->delete($where);
        }
        return null;
    }
    /**
     * 商品分类
     * @param array $where
     * @return array
     */
    public function listGoodsClass (array $where=array())
    {
        $result = $this->select(function  (Select $select) use ($where)
        {
            $select->join(array('a' => 'dbshop_goods_class'), 'a.class_top_id=dbshop_goods_class.class_id', array(), 'left')
                   ->columns(array('*',new Expression('COUNT(a.class_id) as has_children,(SELECT COUNT(goods_in.goods_id) FROM dbshop_goods_in_class AS goods_in WHERE goods_in.class_id=dbshop_goods_class.class_id) as goods_num')))
                   ->where($where)
                   ->group('dbshop_goods_class.class_id')
                   ->order(array('dbshop_goods_class.class_top_id ASC','dbshop_goods_class.class_sort ASC'
            ));
        });
        
        return $result->toArray();
    }
    /**
     * 商品分类列表
     * @param $where
     * @param array $order
     * @return array|null
     */
    public function selectGoodsClass ($where, array $order=array())
    {
        $result = $this->select(function (Select $select) use ($where,$order) {
           $select->where($where);
           if(!empty($order)) $select->order($order);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 根据条件获取商品分类数量
     * @param array $where
     * @return int
     */
    public function countGoodsClass (array $where)
    {
        return $this->select($where)->count();
    }
    /**
     * 获取商品分类信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoGoodsClass (array $where)
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 过滤和排序所有分类，返回一个带有缩进级别的数组
     * @param $spec_class_id    上级id
     * @param $arr              分组数组
     * @return array    过滤排序号的数组
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
                    $class_id = $value['class_id'];
                    if ($level == 0 && $last_class_id == 0) {
                        if ($value['class_top_id'] > 0) {
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
                    if ($value['class_top_id'] == $last_class_id)	{
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
                    } elseif ($value['class_top_id'] > $last_class_id) {
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
                if (($spec_class_id_level == $value['class_level'] && $value['class_id'] != $spec_class_id) ||
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
     * @param int $class_id 分类id
     * @return array    子集分类数组
     */
    public function getSunClassId($class_id=0)
    {
        $sub_class		= array();
        $class_array	= $this->listGoodsClass();
        $class_array	= $this->classOptions($class_id,$class_array);
        if(is_array($class_array) and count($class_array)>0) {
            foreach ($class_array as $v) {
                $sub_class[] = $v['class_id'];
            }
        }
    
        return $sub_class;
    }
}

?>