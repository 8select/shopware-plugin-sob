<?php

class Shopware_Controllers_Backend_CseEightselectBasicManualExport extends \Shopware_Controllers_Backend_ExtJs
{
    public function fullExportAction()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $connection->insert('8s_cron_run_once_queue', ['cron_name' => '8select Full Export']);
    }

    public function quickExportAction()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $connection->insert('8s_cron_run_once_queue', ['cron_name' => '8select Quick Export']);
    }

    public function getFullExportStatusAction()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $queryBuilder = $connection->createQueryBuilder();
        $statement = $queryBuilder->select('progress')->from('8s_cron_run_once_queue')->where('cron_name = \'8select Full Export\'')->execute();
        if ($statement->rowCount()) {
            $progress = $statement->fetchColumn() ?: 0;
        } else {
            $progress = false;
        }
        $this->View()->assign(['progress' => $progress]);
    }

    public function getQuickExportStatusAction()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Shopware()->Container()->get('dbal_connection');
        $queryBuilder = $connection->createQueryBuilder();
        $statement = $queryBuilder->select('progress')->from('8s_cron_run_once_queue')->where('cron_name = \'8select Quick Export\'')->execute();
        if ($statement->rowCount()) {
            $progress = $statement->fetchColumn() ?: 0;
        } else {
            $progress = false;
        }
        $this->View()->assign(['progress' => $progress]);
    }
}
