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
 * $Id: productbuyalsobox.php 438 2011-08-27 09:29:36Z gekosale $
 */
namespace Gekosale;

class ProductBuyAlsoBoxModel extends Component\Model\Dataset
{

	public function initDataset ($dataset)
	{
		$clientGroupId = Session::getActiveClientGroupid();
		
		if (! empty($clientGroupId)){
			
			$dataset->setTableData(Array(
				'id' => Array(
					'source' => 'OP.productid'
				),
				'adddate' => Array(
					'source' => 'P.adddate'
				),
				'name' => Array(
					'source' => 'PT.name'
				),
				'ean' => Array(
					'source' => 'P.ean'
				),
				'delivelercode' => Array(
					'source' => 'P.delivelercode'
				),
				'shortdescription' => Array(
					'source' => 'PT.shortdescription'
				),
				'seo' => Array(
					'source' => 'PT.seo'
				),
				'onstock' => Array(
					'source' => 'IF(P.trackstock = 1, IF(P.stock > 0, 1, 0), 1)'
				),
				'pricenetto' => Array(
					'source' => 'IF(PGP.groupprice = 1, 
								 	PGP.sellprice, 
								 	P.sellprice
								 ) * CR.exchangerate'
				),
				'price' => Array(
					'source' => 'IF(PGP.groupprice = 1, 
									PGP.sellprice, 
									P.sellprice
								 ) * (1 + (V.value / 100)) * CR.exchangerate'
				),
				'buypricenetto' => Array(
					'source' => 'P.buyprice * CR.exchangerate'
				),
				'buyprice' => Array(
					'source' => 'P.buyprice * (1 + (V.value / 100)) * CR.exchangerate'
				),
				'discountpricenetto' => Array(
					'source' => 'IF(PGP.promotion = 1 AND IF(PGP.promotionstart IS NOT NULL, PGP.promotionstart <= CURDATE(), 1) AND IF(PGP.promotionend IS NOT NULL, PGP.promotionend >= CURDATE(), 1),
								 	PGP.discountprice,
								 	IF(PGP.groupprice IS NULL AND P.promotion = 1 AND IF(P.promotionstart IS NOT NULL, P.promotionstart <= CURDATE(), 1) AND IF(P.promotionend IS NOT NULL, P.promotionend >= CURDATE(), 1), P.discountprice, NULL)
								 ) * CR.exchangerate'
				),
				'discountprice' => Array(
					'source' => 'IF(PGP.promotion = 1 AND IF(PGP.promotionstart IS NOT NULL, PGP.promotionstart <= CURDATE(), 1) AND IF(PGP.promotionend IS NOT NULL, PGP.promotionend >= CURDATE(), 1),
								 	PGP.discountprice,
								 	IF(PGP.groupprice IS NULL AND P.promotion = 1 AND IF(P.promotionstart IS NOT NULL, P.promotionstart <= CURDATE(), 1) AND IF(P.promotionend IS NOT NULL, P.promotionend >= CURDATE(), 1), P.discountprice, NULL)
								 ) * (1 + (V.value / 100)) * CR.exchangerate'
				),
				'photo' => Array(
					'source' => 'IF(Photo.photoid IS NULL, 1, Photo.photoid)',
					'processFunction' => Array(
						App::getModel('product'),
						'getImagePath'
					)
				),
				'opinions' => Array(
					'source' => 'COUNT(DISTINCT PREV.idproductreview)'
				),
				'rating' => Array(
					'source' => 'IF(CEILING(AVG(PRANGE.value)) IS NULL, 0, CEILING(AVG(PRANGE.value)))'
				),
				'count' => Array(
					'source' => 'SUM(DISTINCT OP.qty)'
				),
				'new' => Array(
					'source' => 'IF(PN.active = 1 AND (PN.startdate IS NULL OR PN.startdate <= CURDATE()) AND (PN.enddate IS NULL OR PN.enddate >= CURDATE()), 1, 0)'
				),
				'statuses' => Array(
					'source' => 'P.idproduct',
					'processFunction' => Array(
						App::getModel('product'),
						'getProductStatuses'
					)
				)
			));
			
			$dataset->setFrom('
				orderproduct OP
				LEFT JOIN productcategory PC ON PC.productid = OP.productid
				LEFT JOIN category C ON PC.categoryid= C.idcategory
				LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
				LEFT JOIN product P ON PC.productid = P.idproduct
				LEFT JOIN productgroupprice PGP ON PGP.productid = P.idproduct AND PGP.clientgroupid = :clientgroupid
				LEFT JOIN producttranslation PT ON P.idproduct = PT.productid AND PT.languageid = :languageid
				LEFT JOIN productphoto Photo ON P.idproduct= Photo.productid AND Photo.mainphoto = 1
				LEFT JOIN productnew PN ON P.idproduct = PN.productid
				LEFT JOIN vat V ON P.vatid= V.idvat
				LEFT JOIN productreview PREV ON PREV.productid = P.idproduct
				LEFT JOIN productrange PRANGE ON PRANGE.productid = P.idproduct
				LEFT JOIN currencyrates CR ON CR.currencyfrom = P.sellcurrencyid AND CR.currencyto = :currencyto
			');
		}
		else{
			
			$dataset->setTableData(Array(
				'id' => Array(
					'source' => 'OP.productid'
				),
				'adddate' => Array(
					'source' => 'P.adddate'
				),
				'name' => Array(
					'source' => 'PT.name'
				),
				'ean' => Array(
					'source' => 'P.ean'
				),
				'delivelercode' => Array(
					'source' => 'P.delivelercode'
				),
				'shortdescription' => Array(
					'source' => 'PT.shortdescription'
				),
				'seo' => Array(
					'source' => 'PT.seo'
				),
				'onstock' => Array(
					'source' => 'IF(P.trackstock = 1, IF(P.stock > 0, 1, 0), 1)'
				),
				'pricenetto' => Array(
					'source' => 'P.sellprice * CR.exchangerate'
				),
				'price' => Array(
					'source' => 'P.sellprice * (1 + (V.value / 100)) * CR.exchangerate'
				),
				'buypricenetto' => Array(
					'source' => 'ROUND(P.buyprice * CR.exchangerate, 2)'
				),
				'buyprice' => Array(
					'source' => 'ROUND((P.buyprice + (P.buyprice * V.`value`)/100) * CR.exchangerate, 2)'
				),
				'discountpricenetto' => Array(
					'source' => 'IF(P.promotion = 1 AND IF(P.promotionstart IS NOT NULL, P.promotionstart <= CURDATE(), 1) AND IF(P.promotionend IS NOT NULL, P.promotionend >= CURDATE(), 1), P.discountprice * CR.exchangerate, NULL)'
				),
				'discountprice' => Array(
					'source' => 'IF(P.promotion = 1 AND IF(P.promotionstart IS NOT NULL, P.promotionstart <= CURDATE(), 1) AND IF(P.promotionend IS NOT NULL, P.promotionend >= CURDATE(), 1), P.discountprice * (1 + (V.value / 100)) * CR.exchangerate, NULL)'
				),
				'photo' => Array(
					'source' => 'IF(Photo.photoid IS NULL, 1, Photo.photoid)',
					'processFunction' => Array(
						App::getModel('product'),
						'getImagePath'
					)
				),
				'opinions' => Array(
					'source' => 'COUNT(DISTINCT PREV.idproductreview)'
				),
				'rating' => Array(
					'source' => 'IF(CEILING(AVG(PRANGE.value)) IS NULL, 0, CEILING(AVG(PRANGE.value)))'
				),
				'count' => Array(
					'source' => 'SUM(DISTINCT OP.qty)'
				),
				'new' => Array(
					'source' => 'IF(PN.active = 1 AND (PN.startdate IS NULL OR PN.startdate <= CURDATE()) AND (PN.enddate IS NULL OR PN.enddate >= CURDATE()), 1, 0)'
				),
				'statuses' => Array(
					'source' => 'P.idproduct',
					'processFunction' => Array(
						App::getModel('product'),
						'getProductStatuses'
					)
				)
			));
			
			$dataset->setFrom('
				orderproduct OP
				LEFT JOIN productcategory PC ON PC.productid = OP.productid
				LEFT JOIN viewcategory VC ON PC.categoryid = VC.categoryid
				LEFT JOIN product P ON PC.productid= P.idproduct
				LEFT JOIN producttranslation PT ON P.idproduct = PT.productid AND PT.languageid = :languageid
				LEFT JOIN productphoto Photo ON P.idproduct= Photo.productid AND Photo.mainphoto = 1
				LEFT JOIN productnew PN ON P.idproduct = PN.productid
				LEFT JOIN vat V ON P.vatid= V.idvat
				LEFT JOIN productreview PREV ON PREV.productid = P.idproduct
				LEFT JOIN productrange PRANGE ON PRANGE.productid = P.idproduct
				LEFT JOIN currencyrates CR ON CR.currencyfrom = P.sellcurrencyid AND CR.currencyto = :currencyto
			');
		}
		
		$dataset->setAdditionalWhere('
			OP.qty IS NOT NULL AND 
			OP.name IS NOT NULL AND 
			P.idproduct IS NOT NULL AND 
			VC.viewid = :viewid AND 
			OP.orderid IN (:ids) AND 
			OP.productid != :productid AND
			P.enable = 1
		');
		
		$dataset->setGroupBy('
				P.idproduct
			');
		
		$dataset->setSQLParams(Array(
			'ids' => App::getModel('product')->getAlsoProduct((int) $this->registry->core->getParam()),
			'productid' => (int) $this->registry->core->getParam()
		));
	}

	public function getProductDataset ()
	{
		return $this->getDataset()->getDatasetRecords();
	}
}
?>