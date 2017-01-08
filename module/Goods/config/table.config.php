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
'GoodsImageTable'            => function  () { return new \Goods\Model\GoodsImageTable();            },
'GoodsTable'                 => function  () { return new \Goods\Model\GoodsTable();                 },
'GoodsExtendTable'           => function  () { return new \Goods\Model\GoodsExtendTable();           },
'GoodsClassTable'            => function  () { return new \Goods\Model\GoodsClassTable();            },
'GoodsInClassTable'          => function  () { return new \Goods\Model\GoodsInClassTable();          },
'GoodsPriceExtendTable'      => function  () { return new \Goods\Model\GoodsPriceExtendTable();      },
'GoodsPriceExtendColorTable' => function  () { return new \Goods\Model\GoodsPriceExtendColorTable(); },
'GoodsPriceExtendSizeTable'  => function  () { return new \Goods\Model\GoodsPriceExtendSizeTable();  },
'GoodsPriceExtendGoodsTable' => function  () { return new \Goods\Model\GoodsPriceExtendGoodsTable(); },
'GoodsBrandTable'            => function  () { return new \Goods\Model\GoodsBrandTable();            },
'GoodsBrandExtendTable'      => function  () { return new \Goods\Model\GoodsBrandExtendTable();      },
'GoodsCommentTable'          => function  () { return new \Goods\Model\GoodsCommentTable();          },
'GoodsCommentBaseTable'      => function  () { return new \Goods\Model\GoodsCommentBaseTable();      },
'GoodsAskTable'              => function  () { return new \Goods\Model\GoodsAskTable();              },
'GoodsAskBaseTable'          => function  () { return new \Goods\Model\GoodsAskBaseTable();          },
'GoodsTagTable'              => function  () { return new \Goods\Model\GoodsTagTable();              },
'GoodsTagExtendTable'        => function  () { return new \Goods\Model\GoodsTagExtendTable();        },
'GoodsTagInGoodsTable'       => function  () { return new \Goods\Model\GoodsTagInGoodsTable();       },
'GoodsCustomTable'           => function  () { return new \Goods\Model\GoodsCustomTable();           },
'GoodsTagGroupTable'         => function  () { return new \Goods\Model\GoodsTagGroupTable();         },
'GoodsTagGroupExtendTable'   => function  () { return new \Goods\Model\GoodsTagGroupExtendTable();   },
'GoodsClassShowTable'        => function  () { return new \Goods\Model\GoodsClassShowTable();        },
'GoodsAttributeGroupExtendTable'=> function  () { return new \Goods\Model\GoodsAttributeGroupExtendTable();},
'GoodsAttributeGroupTable'   => function  () { return new \Goods\Model\GoodsAttributeGroupTable();   },
'GoodsAttributeTable'        => function  () { return new \Goods\Model\GoodsAttributeTable();        },
'GoodsAttributeExtendTable'  => function  () { return new \Goods\Model\GoodsAttributeExtendTable();  },
'GoodsAttributeValueTable'   => function  () { return new \Goods\Model\GoodsAttributeValueTable();   },
'GoodsAttributeValueExtendTable'=> function  () { return new \Goods\Model\GoodsAttributeValueExtendTable();  },
'GoodsInAttributeTable'      => function  () { return new \Goods\Model\GoodsInAttributeTable();      },
'PromotionsRuleTable'        => function  () { return new \Goods\Model\PromotionsRuleTable();        },
'GoodsRelatedTable'          => function  () { return new \Goods\Model\GoodsRelatedTable();          },
'GoodsCombinationTable'      => function  () { return new \Goods\Model\GoodsCombinationTable();      },
'VirtualGoodsTable'          => function  () { return new \Goods\Model\VirtualGoodsTable();          },
'GoodsIndexTable'            => function  () { return new \Goods\Model\GoodsIndexTable();          },

'PromotionsRuleService'      => function () { return new \Goods\Service\PromotionsRuleService(); },//此并非数据表相关
);