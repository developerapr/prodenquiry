<?php
namespace Aayanshtech\Prodenquiry\Block\Index;

class Producttab extends \Magento\Framework\View\Element\Template {
	 protected $_registry;

    public function __construct(
				\Magento\Catalog\Block\Product\Context $context,
				\Magento\Framework\Registry $registry,
				array $data = []
		) {
			$this->_registry = $registry;
	        parent::__construct($context, $data);
    }
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
     public function getFormAction()
    {
           
       return $this->getUrl('prodenquiry/index/post', ['_secure' => true]);
    }
    public function getCurrentProduct()
    {       
        return $this->_registry->registry('current_product');
    }   
  

}
