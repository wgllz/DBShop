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

namespace Express\Common\Service;


class ExpressStateApi
{
    
    public function __construct()
    {

    }
    /** 
     * 快递状态公共输出端口
     * @return Ambigous <\Express\Common\Service\unknown, multitype:, boolean, \Zend\Config\Reader\mixed, string>
     */
    public function getExpressStateContent($expressApi, $expressNameCode, $expressNumber)
    {
        $contentArray = array();
        switch ($expressApi['name_code']) {
            case 'kuaidi100':
                $contentArray = $this->kuaidi100StateContent($expressApi, $expressNameCode, $expressNumber);
                break;
        }
        return $contentArray;
    }
    /** 
     * 快递100 获取订单配送状态
     * @return unknown
     */
    private function kuaidi100StateContent($expressApi, $expressNameCode, $expressNumber)
    {
        $expressUrl = 'http://www.kuaidi100.com/applyurl?key='.$expressApi['api_code'].'&com='.$expressNameCode.'&nu='.$expressNumber;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $expressUrl);
        curl_setopt($curl, CURLOPT_HEADER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_TIMEOUT,5);
        $getContent = curl_exec($curl);
        curl_close($curl);
        $array['api_type'] = 'kuaidi100';
        $array['content']  = '<iframe src="'.$getContent.'" width="580" height="380">';
        
        /*api调用xml, api调用不稳定，所以使用上面的调用
         * $expressUrl = 'http://api.kuaidi100.com/api?id=344cd5058965d5e2&com=shunfeng&nu=302695229107&show=1&order=desc';
        $expressUrl = 'http://api.kuaidi100.com/api?id='.$expressApi['api_code'].'&com='.$expressNameCode.'&nu='.$expressNumber.'&show=1&order=asc';
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $expressUrl);
        curl_setopt($curl, CURLOPT_HEADER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_TIMEOUT,5);
        $getContent = curl_exec($curl);
        curl_close($curl);
        
        $array = $this->xmlReader->fromString($getContent);*/
        
        return $array;        
    }
}