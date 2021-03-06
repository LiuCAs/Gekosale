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
 * $Revision: 619 $
 * $Author: gekosale $
 * $Date: 2011-12-19 22:09:00 +0100 (Pn, 19 gru 2011) $
 * $Id: news.php 619 2011-12-19 21:09:00Z gekosale $ 
 */
namespace Gekosale;

use FormEngine;

class NewsletterForm extends Component\Form
{
	protected $populateData;

	public function setPopulateData ($Data)
	{
		$this->populateData = $Data;
	}

	public function initForm ()
	{
		$form = new FormEngine\Elements\Form(Array(
			'name' => 'newsletter',
			'action' => '',
			'method' => 'post'
		));
		
		$requiredData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'required_data',
			'label' => _('TXT_MAIN_DATA')
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'name',
			'label' => _('TXT_NAME'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_NAME')),
				new FormEngine\Rules\Unique(_('ERR_NAME_ALREADY_EXISTS'), 'newsletter', 'name', null, Array(
					'column' => 'idnewsletter',
					'values' => (int) $this->registry->core->getParam()
				))
			)
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'email',
			'label' => _('TXT_SENDER'),
			'comment' => _('TXT_EMAIL_FORM'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_EMAIL')),
				new FormEngine\Rules\Email(_('ERR_WRONG_EMAIL'))
			)
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\TextField(Array(
			'name' => 'subject',
			'label' => _('TXT_TOPIC'),
			'rules' => Array(
				new FormEngine\Rules\Required(_('ERR_EMPTY_TOPIC'))
			)
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\RichTextEditor(Array(
			'name' => 'htmlform',
			'label' => _('TXT_HTML'),
			'rows' => 50,
		)));
		
		$requiredData->AddChild(new FormEngine\Elements\Textarea(Array(
			'name' => 'textform',
			'label' => _('TXT_TEXT'),
			'rows' => 50
		)));
		
		$recipientData = $form->AddChild(new FormEngine\Elements\Fieldset(Array(
			'name' => 'recipient_data',
			'label' => _('TXT_RECIPIENT')
		)));
		
		$recipientData->AddChild(new FormEngine\Elements\DatagridSelect(Array(
			'name' => 'recipient',
			'label' => _('TXT_RECIPIENT_LIST'),
			'key' => 'idrecipientlist',
			'datagrid_init_function' => Array(
				App::getModel('recipientlist'),
				'initDatagrid'
			),
			'repeat_max' => FormEngine\FE::INFINITE,
			'columns' => $this->getRecipientListDatagridColumns()
		)));
		
		$Data = Event::dispatch($this, 'admin.newsletter.initForm', Array(
			'form' => $form,
			'id' => (int) $this->registry->core->getParam(),
			'data' => $this->populateData
		));
		
		if (! empty($Data)){
			$form->Populate($Data);
		}
		
		$form->AddFilter(new FormEngine\Filters\Trim());
		$form->AddFilter(new FormEngine\Filters\Secure());
		
		return $form;
	}

	protected function getRecipientListDatagridColumns ()
	{
		return Array(
			Array(
				'id' => 'idrecipientlist',
				'caption' => _('TXT_ID'),
				'appearance' => Array(
					'width' => 50
				),
				'filter' => Array(
					'type' => FormEngine\Elements\DatagridSelect::FILTER_BETWEEN
				)
			),
			Array(
				'id' => 'name',
				'caption' => _('TXT_NAME'),
				'appearance' => Array(
					'width' => FormEngine\Elements\DatagridSelect::WIDTH_AUTO
				),
				'filter' => Array(
					'type' => FormEngine\Elements\DatagridSelect::FILTER_INPUT
				)
			),
			Array(
				'id' => 'adddate',
				'caption' => _('TXT_DATE'),
				'appearance' => Array(
					'width' => 150
				)
			)
		);
	}
}