<?php
namespace Magecomp\Instagramshoppable\Model;
class ProductfeedTSV extends \Magecomp\Instagramshoppable\Model\Productfeed
{
    const TSV_FEED_FILENAME = 'facebook_adstoolbox_product_feed.tsv';
    const TSV_HEADER = "id\ttitle\tdescription\tlink\timage_link\tbrand\tcondition\tavailability\tprice\tshort_description\tproduct_type\tgoogle_product_category\tgender";

    public function tsvescape($t) {
        return str_replace(array("\r", "\n", "&nbsp;", "\t"), ' ', $t);
    }
    public function buildProductAttr($attr_name, $attr_value) {
        return $this->buildProductAttrText($attr_name, $attr_value, 'tsvescape');
    }
    public function defaultCondition() {
        return 'new';
    }
    public function getFileName() {
        return self::TSV_FEED_FILENAME;
    }
    public function buildHeader() {
        return self::TSV_HEADER;
    }
    public function buildFooter() {
        return null;
    }
    public function buildProductEntry($product, $product_name) {
        $items = parent::buildProductEntry($product, $product_name);
        return implode("\t", array_values($items));
    }
}