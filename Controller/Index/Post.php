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
	/* file upload */
	protected $_mediaDirectory;
	protected $_fileUploaderFactory;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Escaper $escaper

    ) {
        parent::__construct($context);
		$this->_transportBuilder = $transportBuilder;
		$this->inlineTranslation = $inlineTranslation;
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
		$this->_fileUploaderFactory = $fileUploaderFactory;
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
		$redirectUrl = $data['url']; 
		try{
			$postObject = new \Magento\Framework\DataObject();			
			/* append images */
			if(!empty($_FILES) && !empty($_FILES['file']['name'])){
				$data['file_1'] =  $this->uploadFile($data,'file');
			}
			if(!empty($_FILES) && !empty($_FILES['file2']['name'])){
					$data['file_2'] =  $this->uploadFile($data,'file2');
			}
			if(!empty($_FILES) && !empty($_FILES['file3']['name'])){
					$data['file_3'] =  $this->uploadFile($data,'file3');
			}
			$postObject->setData($data);			
			$error = false;
		
            if (!\Zend_Validate::is(trim($data['name']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($data['subject']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($data['comment']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($data['email']), 'EmailAddress')) {
                $error = true;
            }
         
            if ($error) {
                throw new \Exception();
            }
			$sender = [
				'name' => $this->_escaper->escapeHtml($data['name']),
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
			$this->messageManager->addSuccess( __('Your request has been submitted.'));		
			$this->_redirect($redirectUrl);  // change here 
			return;
		} catch (\Exception $e) {
			/*echo $e->getMessage();
			echo $e->getLine();
			echo $e->getFile();
			die;*/
			$this->inlineTranslation->resume();
			$this->messageManager->addError(__('We can\'t process your request right now. Sorry, that\'s all we know.'.$e->getMessage())
			);
			$this->_redirect($redirectUrl);  // change here 
			return;
		}
		
    }   
	 public function uploadFile($post,$inputFileName){		
		$target = $this->_mediaDirectory->getAbsolutePath('product_enquiry/');
		$uploader = $this->_fileUploaderFactory->create(['fileId' =>$inputFileName]);
		$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png','doc']);
		$uploader->setAllowRenameFiles(true);		
		$fileName = time().'.'.$uploader->getFileExtension();
		$result = $uploader->save($target,$fileName);
		$mediaUrl = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ); 
		return $mediaUrl.'product_enquiry/'.$fileName;
	}
	
}
