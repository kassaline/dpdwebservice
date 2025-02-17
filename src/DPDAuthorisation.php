<?php
/*
 * **************************************************************************************
 *
 * Dateiname:                 DPDShipment.php
 * Projekt:                   dpdwebservice
 *
 * erstellt von:              Daniel Siekiera <ds@optimondo.de>
 * erstellt am:	              02.09.22, 17:17 Uhr
 * zu letzt bearbeitet:       02.09.22, 16:36 Uhr
 *
 * Copyright © 2022 - Optimondo GmbH
 *
 * **************************************************************************************
 */

namespace DPDWebservice;

use Exception;
use \Soapclient;
use \SoapFault;
use \SOAPHeader;

class DPDAuthorisation{
 
    public $authorisation = [
        'staging' => false,
        'delisId' => null,
        'password' => null,
        'messageLanguage' => 'en_EN',
        'customerNumber' => null,
        'token' => null
    ];

    const TEST_LOGIN_WSDL = 'https://public-ws-stage.dpd.com/services/LoginService/V2_0/?wsdl';
    const LOGIN_WSDL = 'https://public-ws.dpd.com/services/LoginService/V2_0?wsdl';
	
	
	/**
	 * Get an authorisationtoken from the DPD webservice
	 * @param array $array
	 * @param boolean $wsdlCache , cache the wsdl
	 * @throws Exception
	 */
    public function __construct(array $array, $wsdlCache = true)
    {
        $this->authorisation = array_merge($this->authorisation, $array);
        $this->environment = [
            'wsdlCache' => $wsdlCache,
            'loginWsdl' => ($this->authorisation['staging'] ? self::TEST_LOGIN_WSDL : self::LOGIN_WSDL),
        ];

        if($this->environment['wsdlCache'])
		{
            $soapParams = [
                'cache_wsdl' => WSDL_CACHE_BOTH
            ];    
        }
        else
		{
            $soapParams = [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'exceptions' => true
            ];    
        }
        
        try
		{
            $client = new \Soapclient($this->environment['loginWsdl'], $soapParams);

            $auth = $client->getAuth([
                'delisId' => $this->authorisation['delisId'],
                'password' => $this->authorisation['password'],
                'messageLanguage' => $this->authorisation['messageLanguage'],
            ]);

            $auth->return->messageLanguage = $this->authorisation['messageLanguage'];
            $this->authorisation['token'] = $auth->return;
        }
        catch (\SoapFault $e)
		{
            throw new Exception($e->detail->authenticationFault->errorMessage);   
        }
    }    
}