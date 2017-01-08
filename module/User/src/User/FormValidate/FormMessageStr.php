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

namespace User\FormValidate;

class FormMessageStr
{
    private $messageLang;

    public function __construct($langTranslate)
    {
        $this->messageLang = $langTranslate;
    }
    /**
     * 提示模板
     * @param array $data
     */
    public function fromMessageTemplate(array $data)
    {
        if(is_array($data) and !empty($data)) {
            $html = '';
            foreach ($data as $value) {
                if(is_array($value) and !empty($value)) {
                    foreach ($value as $v) {
                        $html .= $v;
                    }
                } else {
                    $html .= $value;
                }
            }
            exit($html);
        }
    }
}