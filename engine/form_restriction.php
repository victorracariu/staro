<?php

class RFormRestriction
{

    public $Restrictions = array( 'Number' => "restrict_number", 'Date' => "restrict_date", 'LocationId' => "restrict_location", 'AgentId' => "restrict_agent", 'CenterId' => "restrict_center", 'CurrencyType' => "restrict_currency", 'CurrencyRate' => "restrict_exchrate", 'ItemName' => "restrict_itemname", 'PriceNetV' => "restrict_price", 'PriceTotV' => "restrict_price", 'ValueVatV' => "restrict_price", 'ValueNetV' => "restrict_price", 'PriceBase' => "restrict_discount", 'DiscountRate' => "restrict_discount", 'DiscountNet' => "restrict_discount", 'DiscountTot' => "restrict_discount", 'PaymentDueDate' => "restrict_duedate" );

    public function CheckReadOnly( $ctrl )
    {
        $rvx =& get_engine( );
        if ( $rvx->Context->IsAdmin( ) )
        {
            return false;
        }
        foreach ( $this->Restrictions as $cname => $cfg )
        {
            if ( $ctrl->Name == $cname && $rvx->Context->GetConfig( $cfg ) )
            {
                if ( $ctrl->Type != CTRL_NUMBER && $ctrl->Type != CTRL_DATE )
                {
                    $ctrl->Type = CTRL_EDIT;
                }
                $ctrl->ReadOnly = true;
            }
        }
    }

}

?>
