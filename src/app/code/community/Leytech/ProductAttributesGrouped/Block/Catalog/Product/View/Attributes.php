<?php
/**
 * @package    Leytech_ProductAttributesGrouped
 * @author     Chris Nolan (chris@leytech.co.uk)
 * @copyright  Copyright (c) 2018 Leytech
 * @license    https://opensource.org/licenses/MIT  The MIT License  (MIT)
 */
class Leytech_ProductAttributesGrouped_Block_Catalog_Product_View_Attributes extends Mage_Catalog_Block_Product_View_Attributes
{
    /**
     * $excludeAttr is optional array of attribute codes to
     * exclude them from additional data array
     *
     * @param array $excludeAttr
     * @param bool $displayEmpty
     * @param string $removeText
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getAdditionalData(array $excludeAttr = array(), $displayEmpty = true, $removeText = '')
    {
        $data = array();
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if ($displayEmpty === false) {
                    if(!$product->hasData($attribute->getAttributeCode())
                        || $product->getData($attribute->getAttributeCode()) == NULL
                        || (string) $value == '') {
                        continue;
                    }
                }

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ($product->getData($attribute->getAttributeCode()) == NULL) {
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    $value = Mage::helper('catalog')->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = Mage::app()->getStore()->convertPrice($value, true);
                }

                $group = 0;
                if( $tmp = $attribute->getData('attribute_group_id') ) {
                    $group = $tmp;
                }

                if (is_string($value) && strlen($value)) {
                    $data[$group]['items'][$attribute->getAttributeCode()] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code'  => $attribute->getAttributeCode()
                    );
                }
            }
        }

        // Add group title
        foreach( $data AS $groupId => &$group ) {
            $groupModel = Mage::getModel('eav/entity_attribute_group')->load( $groupId );
            $group['title'] = $groupModel->getAttributeGroupName();
            if (!empty($removeText)) {
                $group['title'] = str_replace($removeText, "", $group['title']);
            }
        }

        return $data;
    }

}
