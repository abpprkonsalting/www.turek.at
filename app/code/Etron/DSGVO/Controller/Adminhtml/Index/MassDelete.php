<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Etron\DSGVO\Controller\Adminhtml\Index;

use Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersDeleted = 0;
        foreach ($collection->getAllIds() as $customerId) {
            $backup = $this->SendDeleteBackup($customerId);
            $this->customerRepository->deleteById($customerId);
            $customersDeleted++;
        }

        if ($customersDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $customersDeleted));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    protected function SendDeleteBackup($customerId){
        $server = $this->getUrl();
        if(strpos($server, 'https:')){
		$server = substr($server, 8);
	} else {
                $server = substr($server, 7);
        }
	$tmp_arr = explode('/', $server);
        $server = $tmp_arr[0];
        $link = 'http://dsgvo.etron-gateway.at/?mode=push&id=' . $customerId . '&shop_uid=' . $server;
        $curl = curl_init();
        $request = curl_setopt_array($curl, array(
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_HEADER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_URL => $link,
            CURLOPT_USERAGENT => 'Magento2'
        ));

        $result = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return ($httpcode>=200 && $httpcode<300) ? true : false; #$result : false;
    }
}
