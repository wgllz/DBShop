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

class ExpressService
{
    /**
     * 对配送公式进行解析，并计算结果
     * @param string $express
     * @param unknown $weight
     * @param unknown $totalMoney
     * @param number $defaultCost
     * @return number
     */
    public function calculateCost ($express = null, $weight, $totalMoney, $defaultCost = 0)
    {
        if ($str = trim($express)) {
            $costVal = 0;
            $str = str_replace("（", "(", $str);
            $str = str_replace("）", ")", $str);
            $str = str_replace("[", "\$this->getCeil(", $str);
            $str = str_replace("]", ")", $str);
            $str = str_replace("{", "\$this->getValue(", $str);
            $str = str_replace("}", ")", $str);
            $str = str_replace("w", $weight, $str);
            $str = str_replace("W", $weight, $str);
            $str = str_replace("p", $totalMoney, $str);
            $str = str_replace("P", $totalMoney, $str);
            @eval("\$costVal = {$str};");
            if ($costVal === 'failed') {
                return $defaultCost;
            }
            if (!is_numeric($costVal))
                return 0;
            return $costVal;
        } else {
            return $defaultCost;
        }
    }
    /**
     * 值计算，对{}中的公式进行计算
     * @param string $expressStr
     * @return number
     */
    private function getValue ($expressStr)
    {
        if ($expressStr !== '') {
            $expressNum = 0;
            @eval("\$expressNum={$expressStr};");
            if ($expressNum > 0) {
                return 1;
            } else {
                if ($expressNum == 0) {
                    return 0.5;
                }
            }
        }
        return 0;
    }
    /**
     * 进一操作
     * @param string $expressStr
     * @return number
     */
    public function getCeil ($expressStr)
    {
        if ($expressStr = trim($expressStr)) {
            $expressNum = 0;
            @eval(" \$expressNum={$expressStr};");
            if ($expressNum > 0) {
                return ceil($expressNum);
            }
        }
        return 0;
    }
}

?>