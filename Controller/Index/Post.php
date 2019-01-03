<?php
/**
 * Author : Arunendra Pratap Rai
 * Mail : dev.aprai@gmail.com
 * URL : www.aayanshtech.in
 */
namespace Aayanshtech\Prodenquiry\Controller\Index;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;
use Magento\Framework\App\Filesystem\DirectoryList;

class Post extends \Magento\Framework\App\Action\Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
    private $fileSystem;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->fileSystem = $fileSystem;
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
        try {
            $fileArray = $this->getRequest()->getFiles();
            /* append images */
            if (!empty($fileArray['file']['name'])) {
                $data['file_1'] =  $this->uploadFile('file');
            }
            if (!empty($fileArray['file2']['name'])) {
                    $data['file_2'] =  $this->uploadFile('file2');
            }
            if (!empty($fileArray['file3']['name'])) {
                    $data['file_3'] =  $this->uploadFile('file3');
            }
            $error = false;
            if (!\Zend_Validate::is(trim($data['name']), 'NotEmpty')) {
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
            ->setTemplateIdentifier('prodenquiry_template')
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars(['data' =>new DataObject($data)])
            ->setFrom($sender)
            ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
            ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            $this->messageManager->addSuccess(__('Your request has been submitted.'));
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $message = 'We can\'t process your request right now. Sorry, that\'s all we know.';
            $this->messageManager->addError(__($message.$e->getMessage()));
        }
        $this->_redirect($data['url']);
    }
    public function uploadFile($inputFileName)
    {
        $target = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)
        ->getAbsolutePath('product_enquiry/');
        $uploader = $this->_fileUploaderFactory->create(['fileId' =>$inputFileName])
        ->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png','doc'])
        ->setAllowCreateFolders(true)
        ->setAllowRenameFiles(true);
        $fileName = time().'.'.$uploader->getFileExtension();
        $result = $uploader->save($target, $fileName);
        $mediaUrl = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl.'product_enquiry/'.$fileName;
    }
}
