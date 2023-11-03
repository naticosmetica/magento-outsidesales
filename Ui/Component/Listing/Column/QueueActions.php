<?php
namespace Nati\OutsideSales\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class QueueActions extends Column
{
    protected $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $item[$this->getData('name')] = [
                        'reprocess' => [
                            'href' => $this->urlBuilder->getUrl(
                                'outsidesales/queue/reprocesssingle',
                                ['id' => $item['id']]
                            ),
                            'label' => __('Reprocessar'),
                            'confirm' => [
                                'title' => __('Reprocessar %1', $item['id']),
                                'message' => __('Tem certeza de que deseja reprocessar o item %1?', $item['id'])
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
