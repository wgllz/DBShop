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

namespace User\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use User\Model\User as dbshopCheckInData;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;

class UserTable extends AbstractTableGateway implements \Zend\Db\Adapter\AdapterAwareInterface
{
    protected $table = 'dbshop_user';
    
    public function setDbAdapter (Adapter $adapter)
    {
        $this->adapter     = $adapter;
        $this->initialize();
    }
    /**
     * 添加会员
     * @param array $data
     * @return int|null
     */
    public function addUser (array $data)
    {
        $row = $this->insert(dbshopCheckInData::addUserData($data));
        if($row) {
            return $this->getLastInsertValue();
        }
        return null;
    }
    /**
     * 多会员信息获取
     * @param $language
     * @param array $where
     * @return array|null
     */
    public function listUser($language, array $where=array())
    {
        $where  = dbshopCheckInData::whereUserData($where);
        $result = $this->select(function (Select $select) use ($language,$where) {
            $select->columns(array('*',new Expression('(SELECT e.group_name FROM dbshop_user_group_extend AS e WHERE e.group_id=dbshop_user.group_id and e.language=\'' . $language . '\') AS group_name')))->where($where);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 会员列表
     * @param array $pageArray
     * @param $language
     * @param array $where
     * @return Paginator
     */
    public function userPageList(array $pageArray, $language, array $where=array())
    {
    	$select = new Select(array('dbshop_user'=>$this->table));
    	$where  = dbshopCheckInData::whereUserData($where);
    	
    	$select->columns(array('*',new Expression('(SELECT e.group_name FROM dbshop_user_group_extend AS e WHERE e.group_id=dbshop_user.group_id and e.language=\'' . $language . '\') AS group_name')));
    	$select->where($where);
        $select->order('dbshop_user.user_id DESC');
    	
    	//实例化分页处理
    	$pageAdapter = new DbSelect($select, $this->adapter);
    	$paginator   = new Paginator($pageAdapter);
    	$paginator->setCurrentPageNumber($pageArray['page']);
    	$paginator->setItemCountPerPage($pageArray['page_num']);
    	
    	return $paginator;
    }
    /**
     * 获取会员信息
     * @param array $where
     * @return array|\ArrayObject|null
     */
    public function infoUser (array $where=array())
    {
        $row = $this->select($where);
        if($row) {
            return $row->current();
        }
        return null;
    }
    /**
     * 会员信息更新
     * @param array $data
     * @param array $where
     * @return bool|null
     */
    public function updateUser (array $data, array $where)
    {
        $update = $this->update(dbshopCheckInData::updateUserData($data),$where);
        if($update) {
            return true;
        }
        return null;
    }
    /**
     * 会员删除
     * @param array $where
     * @return boolean|NULL
     */
    public function delUser (array $where)
    {
        $del = $this->delete($where);
        if($del) {
            $sql = new Sql($this->adapter);
            //删除邮件发送历史、收货地址、收藏商品、第三方认证登录记录，不删除购买记录及评价与咨询信息
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_user_mail_log')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_user_address')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_user_favorites')->where($where))->execute();
            $sql->prepareStatementForSqlObject($sql->delete('dbshop_other_login')->where($where))->execute();
            return true;
        }
        return null;
    }
    /**
     * 会员积分更新
     * @param array $data
     * @param array $where
     * @param int $integralType
     */
    public function updateUserIntegralNum(array $data, array $where, $integralType=1)
    {
        $integralNum = intval($data['integral_num_log']);
        if($integralNum == 0) return;

        if($integralType == 1) $array['user_integral_num'] = new Expression('user_integral_num+'.$integralNum);     //消费积分
        if($integralType == 2) $array['integral_type_2_num'] = new Expression('integral_type_2_num+'.$integralNum); //等级积分

        $this->update($array, $where);
    }
    /** 
     * 会员总数
     * @param array $where
     * @return Ambigous <number, NULL>
     */
    public function countUser(array $where=array())
    {
        return $this->select($where)->count();
    }
    /**
     * 统计分析用户
     * @param $where
     * @param string $order
     * @param string $group
     * @return array|null
     */
    public function StatsUser($where, $order='', $group='')
    {
        $result = $this->select(function (Select $select) use ($where,$order,$group) {
            $select->columns(array(
                new Expression("FROM_UNIXTIME(user_time,'%Y-%m-%d') AS utime"),
                new Expression('count(user_id) AS user_count')
            ))->where($where);
            if(!empty($order)) $select->order($order);
            if(!empty($group)) $select->group($group);
        });
        if($result) {
            return $result->toArray();
        }
        return null;
    }
    /**
     * 会员排行榜（用户统计分析）
     * @param array $pageArray
     * @param string $where
     * @param string $timeWhere
     * @param string $order
     * @return Paginator
     */
    public function userRanking(array $pageArray, $where='',$timeWhere='', $order='order_total DESC')
    {
        $select = new Select(array('dbshop_user'=>$this->table));

        $select->columns(array('*',
            new Expression('(select SUM(o.order_amount) FROM dbshop_order AS o WHERE o.buyer_id=dbshop_user.user_id and o.order_state>=20 and o.refund_state<>1 '.$timeWhere.') AS order_total'),
            new Expression('(select count(ot.order_id) FROM dbshop_order AS ot WHERE ot.buyer_id=dbshop_user.user_id and ot.order_state>=20 and ot.refund_state<>1 '.str_replace('o.', 'ot.',$timeWhere).') AS order_count')
        ));
        if(!empty($where)) $select->where($where);
        if(!empty($order)) $select->order($order);

        //实例化分页处理
        $pageAdapter = new DbSelect($select, $this->adapter);
        $paginator   = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($pageArray['page']);
        $paginator->setItemCountPerPage($pageArray['page_num']);

        return $paginator;
    }
}

?>