<?php

class CustomExtensions_AbandonedCart_IndexController extends Mage_Core_Controller_Front_Action
{

    // --------------------   <CONTROLLER ACTIONS>   ----------------------//

    public function indexAction ()
    {
        // Mage::log('Datix_AbandondexCart_IndexController->indexAction()');
    }

    public function getAbandonedCartsAction ()
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

        if ( !empty($results) )
        {
            $intAbandonedCartTotalCount = 0;

            header('Content-Type: application/json');

            $objAbandonedCart = array("cart"=>array(),"result"=>array("success"=>true,"count"=>0,"message"=>"this process ran correctly."));

            foreach ( $results as $resultID => $resultData )
            {
                if (strtotime($resultData['updated_at']) < strtotime('- 72 hours') || strtotime($resultData['created_at']) < strtotime('- 72 hours')  )
                {
                    $intAbandonedCartTotalCount++;
                    $objAbandonedCart["cart"][$resultData["entity_id"]] = $resultData;
                }
            }
            $objAbandonedCart["result"]["count"] = $intAbandonedCartTotalCount;

            echo json_encode($objAbandonedCart);
        }
    }

    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }
}