<?php

require_once RVXPATH . 'controller.php';
require_once RVXPATH . 'list.php';

//=============================================================================
class Item_Value_History extends RController
//=============================================================================
{
//=============================================================================
        public function __construct()
//=============================================================================
        {
                parent::RController();
        }

//=============================================================================
        public function Delete()
//=============================================================================
        {
                $rvx =& get_engine();

                if (!$rvx->Context->UserSuper)
                {
                        return rvx_error(MSG_ACCESS_DENIED);
                }
        }

//=============================================================================
        public function Add()
//=============================================================================
        {
                return rvx_error(MSG_ACCESS_DENIED);
        }

//=============================================================================
        public function Import_Excel()
//=============================================================================
        {
                return rvx_error(MSG_ACCESS_DENIED);
        }
}