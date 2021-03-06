<?php
/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2013 WellCommerce sp. z o.o.. Zabronione jest usuwanie informacji o licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version. 
 * 
 * 
 * $Revision: 438 $
 * $Author: gekosale $
 * $Date: 2011-08-27 11:29:36 +0200 (So, 27 sie 2011) $
 * $Id: fileuploader.class.php 438 2011-08-27 09:29:36Z gekosale $ 
 */

namespace Gekosale;
use Exception;

abstract class FileUploader extends Component\Model
{
	
	protected $registry;
	protected $fileType = NULL;
	protected $allowedExtensions = Array();
	protected $uploadFile = '/_upload/';
	protected $tmpExtension;
	protected $insertedFileFullName;
	protected $files;
	const MIME = 'application/octet-stream';

	public function __construct ($registry)
	{
		$this->registry = $registry;
		$this->setFiles();
	}

	final protected function loadAllowedType ($type)
	{
		$types = Array();
		foreach($type as $val){
			$types[] = "'".$val."'";
		}
		$sql = 'SELECT idfiletype AS id, name FROM filetype WHERE name IN ('.implode(',', $types).') AND active = 1';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$this->fileType[$rs['name']] = $rs['id'];
		}
	}

	final protected function loadAllowedExtensions ($extension)
	{
		$extensions = Array();
		foreach($extension as $val){
			$extensions[] = "'".$val."'";
		}
		$sql = 'SELECT idfileextension AS id, name FROM fileextension WHERE name IN ('.implode(',', $extensions).') AND active = 1';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->execute();
		while ($rs = $stmt->fetch()){
			$this->allowedExtensions[$rs['name']] = $rs['id'];
		}
	}

	final protected function insertFile ($name)
	{
		$sql = 'INSERT INTO file(name, filetypeid, fileextensionid, viewid)
				VALUES (:name, :filetypeid, :fileextensionid, :viewid)';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('name', Core::clearUTF($name));
		$stmt->bindValue('filetypeid', current($this->fileType));
		$stmt->bindValue('viewid', Helper::getViewId());
		$stmt->bindValue('fileextensionid', $this->allowedExtensions[strtolower($this->tmpExtension)]);
		try{
			$stmt->execute();
		}
		catch (Exception $e){
			throw new Exception($e->getMessage());
		}
		$idFile = Db::getInstance()->lastInsertId();
		$this->insertedFileFullName = $idFile . '.' . $this->tmpExtension;
		$this->registry->cache->delete('files');
		$this->setFiles();
		return $idFile;
	}

	public function process ($file)
	{
		if (is_object($file)){
			$Data = $file->getValue();
		}
		else{
			$Data = $file;
		}
		if ($Data['error'] == 0 && isset($Data['type'])){
			try{
				$this->check($Data['type'], $Data['name']);
			}
			catch (Exception $e){
				throw $e;
			}
		}
		else{
			return false;
		}
	}

	final protected function check ($type, $fileName)
	{
		if ($type == self::MIME){
			$_fileType['type'] = self::MIME;
			$_fileType['extension'] = $this->getFileExtension($fileName);
		}
		else{
			preg_match('/^(?<type>[a-z]*)\/(?<extension>[a-z\-]*)$/', $type, $_fileType);
		}
		if (! isset($_fileType['type']) || ! isset($_fileType['extension'])){
			throw new Exception('File type or exception error');
		}
		else{
			if (! array_key_exists($_fileType['type'], $this->fileType)){
				throw new Exception('File type not match');
			}
			if (! array_key_exists($_fileType['extension'], $this->allowedExtensions)){
				throw new Exception('File extension not match');
			}
			if ($_fileType['extension'] == 'jpeg')
				$_fileType['extension'] = 'jpg';
			$this->tmpExtension = $_fileType['extension'];
		}
		return true;
	}

	final public function getType ()
	{
		return $this->fileType;
	}

	final public function getAllowedExtensions ()
	{
		return $this->allowedExtensions;
	}

	public function setFiles ()
	{
		if (($this->files = $this->registry->cache->load('files')) === FALSE){
			$sql = 'SELECT 
						idfile, 
						F.name AS filename, 
						F.fileextensionid, 
						F.filetypeid,
						FE.name AS filextensioname, 
						FT.name AS filetypename, 
						concat(idfile,\'.\', FE.name) AS filediskname 
					FROM file F 
					LEFT JOIN fileextension FE ON FE.idfileextension = F.fileextensionid
					LEFT JOIN filetype FT ON FT.idfiletype = F.filetypeid
					GROUP BY F.idfile';
			$stmt = Db::getInstance()->prepare($sql);
			$stmt->execute();
			while ($rs = $stmt->fetch()){
				$this->files[$rs['idfile']] = Array(
					'idfile' => $rs['idfile'],
					'filename' => $rs['filename'],
					'fileextensionid' => $rs['fileextensionid'],
					'filetypeid' => $rs['filetypeid'],
					'filextensioname' => $rs['filextensioname'],
					'filetypename' => $rs['filetypename'],
					'filediskname' => $rs['filediskname']
				);
			}
			$this->registry->cache->save('files', $this->files);
		}
	}

	final public function getFileById ($id)
	{
		if ($id > 0 && (isset($this->files[$id]))){
		
		}
		else{
			$id = 1;
		}
		return $this->files[$id];
	}

	final protected function getFileByName ($name)
	{
		$sql = 'SELECT idfile, F.name AS filename, F.fileextensionid, F.filetypeid,
				FE.name AS filextensioname, FT.name AS filetypename 
				FROM file F WHERE F.name = :name
				LEFT JOIN fileextension FE ON FE.idfileextension = F.fileextensionid
				LEFT JOIN filetypeid FT ON FT.idfiletype = F.filetypeid';
		$stmt = Db::getInstance()->prepare($sql);
		$stmt->bindValue('name', $name);
		$stmt->execute();
		$Data = $stmt->fetchAll();
		if (isset($Data[0])){
			return $Data[0];
		}
		throw new CoreException(_('ERR_FILE_NOT_EXIST'), 8, $e->getMessage());
	}

	public function getFileExtension ($fileName)
	{
		preg_match('/.(?<ext>[a-z]{1,4})$/', $fileName, $matches);
		if (isset($matches['ext'])){
			return $matches['ext'];
		}
		throw new Exception('Extension error');
	}

	abstract function load ();
}