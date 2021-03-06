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
 * $Id: rulescart.php 438 2011-08-27 09:29:36Z gekosale $ 
 */
namespace Gekosale;

use FormEngine;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class TemplateEditorController extends Component\Controller\Admin
{

	public function index ()
	{
		$this->registry->xajax->registerFunction(array(
			'doDeletePagescheme',
			$this->model,
			'doAJAXDeletePagescheme'
		));
		
		$this->registry->xajax->registerFunction(array(
			'LoadAllPagescheme',
			$this->model,
			'getPageschemeForAjax'
		));
		
		$this->registry->xajax->registerFunction(array(
			'setDefaultPagescheme',
			$this->model,
			'doAJAXDefaultPagescheme'
		));
		
		$this->registry->xajax->registerFunction(array(
			'doUpdateScheme',
			$this->model,
			'doAJAXUpdateScheme'
		));
		
		$this->renderLayout(Array(
			'datagrid_filter' => $this->model->getDatagridFilterData()
		));
	}

	public function add ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'pagescheme',
			'action' => '',
			'method' => 'post'
		));
		
		$filesPane = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'files_pane',
			'label' => 'Dodaj nowy szablon'
		)));
		
		$filesPane->AddChild(new FormEngine\Elements\Tip(Array(
			'tip' => '<p align="center">Wybierz z dysku lub wgraj plik zip z szablonem sklepu. Po imporcie będziesz mógł zmienić jego ustawienia oraz ustawić jako domyślny dla sklepu.</p>',
			'direction' => FormEngine\Elements\Tip::DOWN
		)));
		
		$files = $filesPane->AddChild(new FormEngine\Elements\LocalFile(Array(
			'name' => 'files',
			'label' => 'Plik',
			'file_source' => 'themes/',
			'traversable' => false,
			'file_types' => Array(
				'zip'
			)
		)));
		
		if ($form->Validate(FormEngine\FE::SubmittedData())){
			$Data = $form->getSubmitValues(FormEngine\Elements\Form::FORMAT_FLAT);
			
			$check = App::getModel('pagescheme/import')->check($Data['files']['file']);
			$bValid = false;
			
			if (is_array($check)){
				foreach ($check as $file){
					if ($file['filename'] == 'settings/export.json'){
						$bValid = true;
						break;
					}
				}
			}
			
			if ($bValid){
				App::getModel('pagescheme/import')->importPagescheme($Data['files']['file']);
				App::redirect(__ADMINPANE__ . '/templateeditor');
			}
			else{
				Session::setVolatileMessage("Musisz wybrać prawidłowe archiwum ZIP.");
				App::redirect(__ADMINPANE__ . '/templateeditor/add');
			}
		}
		
		$this->registry->template->assign('form', $form->Render());
		$this->registry->xajax->processRequest();
		$this->registry->template->assign('xajax', $this->registry->xajax->getJavascript());
		$this->registry->template->display($this->loadTemplate('add.tpl'));
	}

	public function confirm ()
	{
		$extensions = Array(
			'less',
			'css',
			'tpl',
			'js',
			'xml',
			'json',
			'jpg',
			'jpeg',
			'png',
			'gif',
			'ico',
			'otf',
			'html',
			'pdf'
		);
		
		$parts = array_reverse(explode('.', $this->registry->core->getParam()));
		$theme = array_pop($parts);
		
		$root = ROOTPATH . 'themes' . DS . $theme;
		
		$_POST['dir'] = urldecode($_POST['dir']);

		if (strncmp($root, realpath($root . $_POST['dir']), strlen($root)) !== 0) {
			throw new CoreException('Directory "' . $_POST['dir'] . '" not found');
		}

		if (file_exists($root . $_POST['dir'])){
			$files = scandir($root . $_POST['dir']);
			natcasesort($files);
			if (count($files) > 2){
				echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
				foreach ($files as $file){
					if (file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file)){
						echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
					}
				}
				foreach ($files as $file){
					if (file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && ! is_dir($root . $_POST['dir'] . $file) && in_array(pathinfo($root . $_POST['dir'] . $file, PATHINFO_EXTENSION), $extensions)){
						$ext = preg_replace('/^.*\./', '', $file);
						echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
					}
				}
				echo "</ul>";
			}
		}
	}

	public function view ()
	{
		$filename = App::getModel('pagescheme/export')->exportPagescheme($this->id);
		
		if (! $filename){
			throw new CoreException('Template id: ' . $this->id . ' not exists');
		}
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=$filename");
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile(ROOTPATH . 'themes' . DS . $filename);
		exit();
	}
}