<?php


namespace MageYug\LowStockNotification\Cron;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Reports\Model\ResourceModel\Product\Lowstock\CollectionFactory;
use Magento\Store\Model\ScopeInterface;

class Notification
{
    public function __construct(
        CollectionFactory $lowstocksFactory,
        \MageYug\LowStockNotification\Helper\Email $emailHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_lowstocksFactory = $lowstocksFactory;
        $this->emailHelper = $emailHelper;
        $this->productFacory = $productFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Cronjob Description
     *
     * @return void
     */
    public function execute(): void
    {
        $status = $this->scopeConfig->getValue('cataloginventory/stock_notification/status',ScopeInterface::SCOPE_STORE);
        if($status) {
            $collection = $this->_lowstocksFactory->create()->addAttributeToSelect(
                '*'
            )->filterByIsQtyProductTypes()->joinInventoryItem(
                'qty'
            )->setOrder(
                'qty',
                DataCollection::SORT_ORDER_ASC
            )->useManageStockFilter(
                0
            )->useNotifyStockQtyFilter(
                0
            )->setOrder(
                'qty',
                DataCollection::SORT_ORDER_ASC
            );
            $html = '';
            $html = "<table style='border: 1px solid #CACACA'>";
            $html .= "<tr>";
            $html .= "<th style='border: 1px solid #CACACA; padding: 10px'>";
            $html .= "Name";
            $html .= "</th>";
            $html .= "<th style='border: 1px solid #CACACA;padding: 10px'>";
            $html .= "Sku";
            $html .= "</th>";
            $html .= "<th style='border: 1px solid #CACACA;padding: 10px'>";
            $html .= "Qty";
            $html .= "</th>";
            $html .= "</tr>";
            foreach ($collection as $item) {
                $product = $this->productFacory->create()->load($item->getId());
                $html .= "<tr>";
                $html .= "<td style='border: 1px solid #CACACA;padding: 10px'>";
                $html .= $product->getName();
                $html .= "</td>";
                $html .= "<td style='border: 1px solid #CACACA;padding: 10px'>";
                $html .= $item->getSku();
                $html .= "</td>";
                $html .= "<td style='border: 1px solid #CACACA;padding: 10px'>";
                $html .= $item->getQty();
                $html .= "</td>";
                $html .= "</tr>";
            }
            $html .= "</table>";
            $this->emailHelper->sendEmail($html);
        }

    }
}
