<?php

namespace CseEightselectBasic\Services\Export;

interface ExportInterface
{
    /**
     * @param int $limit
     * @param int $offset
     * @param bool $isDeltaOffset
     */
    public function getProducts($limit, $offset, $isDeltaExport = true);

    /**
     * @param bool $isDeltaExport
     */
    public function getTotal($isDeltaExport = true);
}
