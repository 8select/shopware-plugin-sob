<?php

namespace CseEightselectBasic\Services\Export;

interface ExportInterface
{
    /**
     * @param int $limit
     * @param int $offset
     * @param bool $isDeltaOffset
     * @return array
     */
    public function getProducts($limit, $offset, $isDeltaExport = true);

    /**
     * @param bool $isDeltaExport
     * @return int
     */
    public function getTotal($isDeltaExport = true);
}
