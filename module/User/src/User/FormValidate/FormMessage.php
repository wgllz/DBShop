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

namespace User\FormValidate;

class FormMessage
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
						$html .= '<li>'. $v . '</li>';
					}
				} else {
					$html .= '<li>' . $value . '</li>';
				}
			}
			$html = $this->templateHtml($html);
			exit($html);
		}
	}
	/**
	 * 提示模板解析
	 * @param unknown $data
	 * @return string
	 */
	private function templateHtml($data)
	{
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>'.$this->messageLang->translate('系统提示').'</title>
					<style type="text/css">
						body,td,th {
							color: #FFF;
						}
					</style>
					</head>
					<body>
						<table width="40%" border="0" align="center">
  							<tr bgcolor="#999999">
    							<td style="padding-left:8px; padding-right:5px;"><h2><strong>'.$this->messageLang->translate('系统提示').'：</strong></h2>
    								<ul>'.$data.'</ul>
    								<p style="text-align:center"><a href="">'.$this->messageLang->translate('返回上一步').'</a></p>
    							</td>
  							</tr>
						</table>
					</body>
				</html>';
		
		return $html;		
	}
	
}

?>