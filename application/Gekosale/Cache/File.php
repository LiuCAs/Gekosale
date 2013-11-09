<?php
/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.com
 *
 * Copyright (c) 2009-2013 WellCommerce sp. z o.o.. Zabronione jest usuwanie informacji o licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * $Revision: 54 $
 * $Author: krzotr $
 * $Date: 2011-04-09 09:52:26 +0200 (So, 09 kwi 2011) $
 * $Id: cache.class.php 54 2011-04-09 07:52:26Z krzotr $
 */

namespace Gekosale\Cache;
use Exception;

class File
{
	protected $path;
	protected $cacheid;
	protected $suffix = '.reg';

	public function __construct ()
	{
		$this->path = ROOTPATH . 'serialization' . DS;
		$this->cacheid = \Gekosale\Helper::getViewId() . '_' . \Gekosale\Helper::getLanguageId();
	}

	public function save ($name, $value, $time)
	{
		if (@file_put_contents($this->getCacheFileName($name), $value, LOCK_EX) === FALSE){
			throw new Exception('Can not serialize content to file ' . $this->getCacheFileName($name) . '. Check directory\'s permissions');
		}

		$time = time() + ($time ?: 2592000);
		@touch($this->getCacheFileName($name), $time, $time);
	}

	public function load ($name)
	{
		if (($content = @file_get_contents($this->getCacheFileName($name))) === FALSE){
			return FALSE;
		}

		clearstatcache();
		if (filemtime($this->getCacheFileName($name)) < time()) {
			return FALSE;
		}

		return $content;
	}

	public function delete ($name)
	{
		$files_found = glob($this->path . strtolower($name) . '*');
		if (is_array($files_found)){
			foreach ($files_found as $fn){
				@unlink($fn);
			}
		}
	}

	public function deleteAll ()
	{
		$files_found = glob($this->path . '*' . $this->suffix);
		if (is_array($files_found)){
			foreach ($files_found as $fn){
				@unlink($fn);
			}
		}
	}

	public function getCacheFileName ($name)
	{
		$cacheid = \Gekosale\Helper::getViewId() . '_' . \Gekosale\Helper::getLanguageId();
		return $this->path . strtolower($name) . '_' . $cacheid . $this->suffix;
	}
}
