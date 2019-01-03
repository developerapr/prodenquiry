<?php
namespace Aayanshtech\Prodenquiry\Block\Index;

class Producttab extends \Magento\Framework\View\Element\Template
{
    public $varregistry;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
            $this->varregistry = $registry;
            parent::__construct($context, $data);
    }
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    public function getFormAction()
    {
           
        return $this->getUrl('prodenquiry/index/post', ['_secure' => true]);
    }
    public function getCurrentProduct()
    {
        return $this->varregistry->registry('current_product');
    }
}
