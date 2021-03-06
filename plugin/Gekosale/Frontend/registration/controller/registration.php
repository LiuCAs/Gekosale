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
 * $Revision: 627 $
 * $Author: gekosale $
 * $Date: 2012-01-20 23:05:57 +0100 (Pt, 20 sty 2012) $
 * $Id: registrationcart.php 627 2012-01-20 22:05:57Z gekosale $
 */

namespace Gekosale;

class RegistrationController extends Component\Controller\Frontend
{

	public function index ()
	{
		if (Session::getActiveClientid() > 0){
			App::redirectUrl($this->registry->router->generate('frontend.clientsettings', true));
		}
		$this->Render('Registration');
	}
}