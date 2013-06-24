<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Mailer
*/

namespace Yau\Mailer;

/**
* Interface for generic mailer classes
*
* @author   John Yau
* @category Yau
* @package  Yau_Mailer
*/
interface MailerInterface
{
/*=======================================================*/

/**
* Send an email
*
* @param string $to
* @param string $subject
* @param string $message
* @param string $additional_headers,
* @param string $additional_parameters
* @see   mail()
*/
public function mail($to, $subject, $message, $additional_headers = '', $additional_parameters = '');

/*=======================================================*/
}
