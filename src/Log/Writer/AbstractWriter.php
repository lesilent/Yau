<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Log
*/
namespace Yau\Log\Writer;

use Yau\Log\Writer\WriterTterface;


/**
* Abstract log writer class
*
* @author   John Yau
* @category Yau
* @package  Yau_Log
*/
abstract class AbstractWriter
{
/*=================================================================*/

/**
* Write an event out
*
* @param array $event
*/
public function write(array $event);

/*=================================================================*/
}