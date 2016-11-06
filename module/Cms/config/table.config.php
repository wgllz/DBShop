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

return array(
'ArticleClassTable'          => function  () { return new \Cms\Model\ArticleClassTable();       },
'ArticleClassExtendTable'    => function  () { return new \Cms\Model\ArticleClassExtendTable(); },
'ArticleTable'               => function  () { return new \Cms\Model\ArticleTable();            },
'ArticleExtendTable'         => function  () { return new \Cms\Model\ArticleExtendTable();      },
'SingleArticleTable'         => function  () { return new \Cms\Model\SingleArticleTable();      },
'SingleArticleExtendTable'   => function  () { return new \Cms\Model\SingleArticleExtendTable();},
);