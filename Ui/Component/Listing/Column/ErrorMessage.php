<?php

namespace Nati\OutsideSales\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class ErrorMessage extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                // Você pode personalizar ainda mais como o erro é exibido aqui
                $item[$this->getData('name')] = $item['message'];
            }
        }
        return $dataSource;
    }
}
