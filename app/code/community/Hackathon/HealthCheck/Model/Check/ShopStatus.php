<?php

class Hackathon_HealthCheck_Model_Check_ShopStatus extends Hackathon_HealthCheck_Model_Check_Abstract
{
    public function _run()
    {

        $status_error = Hackathon_HealthCheck_Model_Check_Abstract::WARN_TYPE_ERROR;
        $status_warning = Hackathon_HealthCheck_Model_Check_Abstract::WARN_TYPE_WARNING;
        $status_ok = Hackathon_HealthCheck_Model_Check_Abstract::WARN_TYPE_OK;

        $helper = Mage::helper('hackathon_healthcheck');
        $renderer = $this->getContentRenderer();

        $header = array(
            $helper->__('Service'),
            $helper->__('Status'),
        );

        $row = array();

        /**
         * Webserver interface, PHP.ini information
         */

        $max_execution_time = ini_get('max_execution_time');
        $memory_limit = ini_get('memory_limit');

        $row[$helper->__("Webserver")] = $_SERVER["SERVER_SOFTWARE"];
        $row[$helper->__("Maximum execution time (PHP)")] = array('value' => $max_execution_time,
            'status' => array(
                Hackathon_HealthCheck_Model_Check_Abstract::WARN_TYPE_CSSCLASS =>
                    $max_execution_time <= 30 ?
                        $status_error : ($max_execution_time >= 180 ? $status_ok : $status_warning)
            )
        );

        /**
         * Extract Memory Limit as Integer
         */
        preg_match("/([0-9]+[\.,]?)+/", $memory_limit, $matches);
        $memory_limit_value = $matches[0];

        $row[$helper->__("Memory Limit")] = array('value' => $memory_limit,
            'status' => array(
                Hackathon_HealthCheck_Model_Check_Abstract::WARN_TYPE_CSSCLASS =>
                    $memory_limit_value <= 64 ?
                        $status_error : ($memory_limit_value >= 256 ? $status_ok : $status_warning)
            )
        );

        /**
         * HTACCESS-Check
         */
        $row[$helper->__('.htaccess')] = file_exists(Mage::getBaseDir() . "/.htaccess") ?
            (array('value' => $helper->__('.htaccess exists'),
                'status' => array(
                    Hackathon_HealthCheck_Model_Check_Abstract::WARN_TYPE_CSSCLASS => $status_ok,
                ))) :
            (array('value' => $helper->__('.htaccess does not exist'),
                'status' => array(
                    Hackathon_HealthCheck_Model_Check_Abstract::WARN_TYPE_CSSCLASS => $status_error,
                )));


        /**
         * Magento-URL Information
         */
        $row[$helper->__('Admin-URL')] = Mage::helper('adminhtml')->getUrl('adminhtml');

        foreach (Mage::app()->getStores() as $store) {
            $row[$store->getName()] = Mage::app()->getStore($store->getId())->getUrl();
        }

        /**
         * Rendering
         */
        $renderer->setHeaderRow($header);

        //$renderer->addRow(array('RowData1', 'RowData2'), array('_cssClasses' => 'health-ok'));

        foreach ($row as $key => $line) {

            if (is_array($line)) {
                $renderer->addRow(array($key, $line['value']), $line['status']);
            } else {
                $renderer->addRow(array($key, $line));
            }
        }


        return $this;
    }
}