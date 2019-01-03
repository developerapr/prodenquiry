<?php
/**
 * Author : Arunendra Pratap Rai
 * Mail : dev.aprai@gmail.com
 * URL : www.aayanshtech.in
 */
namespace Aayanshtech\Prodenquiry\Controller\Index;

class Post extends \Magento\Framework\App\Action\Action
{
	const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
	protected $_transportBuilder;
	protected $inlineTranslation;
	protected $scopeConfig;
	protected $storeManager;
	protected $_escaper;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Escaper $escaper

    ) {
        parent::__construct($context);
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->_escaper = $escaper;
    }  
	
    public function execute()
    {
		$data = $this->getRequest()->getPostValue();
		if (!$data) {
			$this->_redirect('*/*/');
			return;
		}		
		
		$this->inlineTranslation->suspend();
		try{
			$postObject = new \Magento\Framework\DataObject();
			$postObject->setData($data);
			$error = false;
			
			$sender = [
				'name' => $this->_escaper->escapeHtml($data['first_name']),
				'email' => $this->_escaper->escapeHtml($data['email']),
			];
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
			$transport = $this->_transportBuilder
			->setTemplateIdentifier('prodenquiry_template') // this code we have mentioned in the email_templates.xml
			->setTemplateOptions(
				[
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
					'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
				]
			)
			->setTemplateVars(['data' => $postObject])
			->setFrom($sender)
			->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
			->getTransport();

			$transport->sendMessage();
			$this->inlineTranslation->resume();
			/*
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();       
			$question = $objectManager->create('Aayanshtech\Warrantyform\Model\Warrantyform');
			$data['country'] = $data['country_id'];
			$data['state'] 	 = $data['region'];
			$question->setData($data);	
			$question->save();*/
			$this->messageManager->addSuccess( __('Your request has been submitted.'));		
			$this->_redirect('*/*/');
			return;
		} catch (\Exception $e) {
			/*echo $e->getMessage();
			echo $e->getLine();
			echo $e->getFile();
			die;*/
			$this->inlineTranslation->resume();
			$this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
			);
			$this->_redirect('*/*/');
			return;
		}
		
    }   

	
}
