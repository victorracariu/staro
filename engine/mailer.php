<?php

require_once RVXPATH.'controller.php';
require_once APXPATH . 'admin/mailing_list.php';

//=============================================================================
class RMailer extends RController
//=============================================================================
{
	var $From = 1;
	var $Mailer;
	var $Mail_List;
	var $Body;
	var $Subject;
	var $Attach;

//=============================================================================
	function RMailer()
//=============================================================================
	{
		require_once( RVXPATH.'/mailer/class.phpmailer.php' );
		$this->Mailer = new PHPMailer();
                $this->Mailer->Mailer = 'smtp';
                $this->Mailer->Host = 'mail-relay.fd.corp';

	}

//=============================================================================
	function SendMail()
//=============================================================================
	{
		$rvx =& get_engine();

		// get email from person who send mail
                $usr = array();
                if( $this->From )
                {
                        $user_id = $rvx->Context->UserId;
                        $sql = "SELECT Email, PersonName FROM User WHERE Id =".$user_id;
                        $usr = $rvx->Database->QueryRow($sql);
                }

		// set from person email, name
		if( !$usr['Email'] ) $usr['Email'] = 'ml_rvx@fashiondays.com';
		if( !$usr['PersonName'] ) $usr['PersonName'] = 'RVX';

		// from
		$this->Mailer->SetFrom( $usr['Email'], $usr['PersonName'] );

		// body
		$this->Mailer->MsgHTML( $this->Body );

		// subject
		$this->Mailer->Subject  = $this->Subject;

		// to
		if( RVX_STAGING === TRUE )
		{
			$this->Mail_List = array();
			$this->Mailer->clearAddresses();
			$mailingList = new Mailing_List();
	                $mailAddresses = $mailingList->GetAddressesByName("wms_warnings");
	                $this->Mail_List = $mailAddresses;
		}
		
		foreach( $this->Mail_List as $mail )
		{
			$this->Mailer->AddAddress( $mail );
		}

		$this->Mailer->clearReplyTos();
		$this->Mailer->AddReplyTo( "no-reply@fashiondays.com" );

		// attach
		foreach( $this->Attach as $attach )
		{
			if (filesize($attach) > "15")
			{
				$this->Mailer->AddAttachment( $attach );
			}
		}

		// send
		if(!$this->Mailer->Send())
		{
			rvx_error( "Mail error: " . $this->Mailer->ErrorInfo);
		}
	}

//=============================================================================
        function SendToListById($mail_list_id, $additionalEmails = array())
//=============================================================================
        {
                $mailingList = new Mailing_List();
                $mailAddresses = $mailingList->GetAddressesById($mail_list_id);
                $this->Mail_List = array_merge($mailAddresses, $additionalEmails);
                $this->SendMail();
        }

//=============================================================================
        function SendToListByName($mail_list_name, $additionalEmails = array())
//=============================================================================
        {
                $mailingList = new Mailing_List();
                $mailAddresses = $mailingList->GetAddressesByName($mail_list_name);
                $this->Mail_List = array_merge($mailAddresses, $additionalEmails);
                $this->SendMail();
        }
}

?>
