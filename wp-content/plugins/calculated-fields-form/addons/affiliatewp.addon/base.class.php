<?php

if(class_exists('Affiliate_WP_Base'))
{
	/**
	 * CFF integration class.
	 */
	class Affiliate_WP_CFF extends Affiliate_WP_Base {

		public $doc_url = 'https://cff.dwbooster.com/add-ons/affiliatewp';
		public $context = 'calculated-fields-form';
	} // End Class Affiliate_WP_CFF
}