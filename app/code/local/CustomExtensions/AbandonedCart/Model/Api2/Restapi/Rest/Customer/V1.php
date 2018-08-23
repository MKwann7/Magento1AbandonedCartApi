<?php
/**
 * Created by PhpStorm.
 * User: mzak
 * Date: 8/2/2018
 * Time: 1:31 PM
 */
class CustomExtensions_AbandonedCart_Model_Api2_Restapi_Rest_Customer_V1 extends CustomExtensions_AbandonedCart_Model_Api2_Restapi
{
    public function _retrieve()
    {
        $data = array();
        return $data;
    }

    public function _retrieveCollection()
    {
        $data = array();
        return $data;
    }

    public function items()
    {
        $dtmStartDate = $this->getRequest()->getParam('start_date');

        $dtmEndDate = $this->getRequest()->getParam('end_date');

        if (!empty($dtmStartDate) && !$this->validateDate($dtmStartDate))
        {
            $objAbandonedCartResult = array("cart"=>array(),"result"=>array("success"=>false,"count"=>0,"message"=>"start_date is not a valid date."));
            die(json_encode($objAbandonedCartResult));
        }


        if (!empty($dtmEndDate) && !$this->validateDate($dtmEndDate))
        {
            $objAbandonedCartResult = array("cart"=>array(),"result"=>array("success"=>false,"count"=>0,"message"=>"end_date is not a valid date."));
            die(json_encode($objAbandonedCartResult));
        }

        $strStartDateWhereClause = "";
        $strEndDateWhereClause = "";

        if (!empty($dtmStartDate))
        {
            $strStartDateWhereClause = " AND updated_at > '".date("Y-m-d H:i:s",strtotime($dtmStartDate))."'";
        }

        if (!empty($dtmEndDate))
        {
            $strStartDateWhereClause = " AND updated_at < '".date("Y-m-d H:i:s",strtotime($dtmEndDate))."'";
        }

        $resource       = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName      = $resource->getTableName('sales_flat_quote');

        $query   = "SELECT * FROM `" . $tableName . "` WHERE is_active = 1 ".$strStartDateWhereClause.$strStartDateWhereClause." ORDER BY updated_at DESC";
        $results = $readConnection->fetchAll($query);

        if ( empty($results) )
        {
            return null;
        }

        $objAbandonedCart = array();

        foreach ( $results as $resultID => $resultData )
        {
            if (strtotime($resultData['updated_at']) < strtotime('- 72 hours') || strtotime($resultData['created_at']) < strtotime('- 72 hours')  )
            {
                $objAbandonedCart[] = $resultData;
            }
        }

        return $objAbandonedCart;
    }

    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}