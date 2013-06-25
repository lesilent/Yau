<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Log
*/
namespace Yau\Log\Writer;

/**
* Log writer interface
*
* @author   John Yau
* @category Yau
* @package  Yau_Log
*/
interface WriterInterface
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