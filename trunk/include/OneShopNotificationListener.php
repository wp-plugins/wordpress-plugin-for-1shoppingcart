<?php
    include('../Library/OneShopAPI.php');

    # TODO: Add your Merchant ID as the First Parameter, and your Merchant Key as Second Parameter.
    $API = new OneShopAPI("","","https://www.mcssl.com");
    
    $requestBodyXML = new DOMDocument();

    # Load the request body into XML and check that the result has been parsed into XML    
    if ($requestBodyXML->loadXML($HTTP_RAW_POST_DATA) == true)
    {
        $notificationType = $requestBodyXML->documentElement->nodeName;  
        $tokenNode = $requestBodyXML->getElementsByTagName('Token')->item(0)->nodeValue;  
        switch ($notificationType)
        {            
            case "NewOrder":
                
                $apiResult = $API->GetOrderById($tokenNode);                
               
            break;
            
            default:
                # May have other types of notifications in the future
            break;
        }
        
        $apiResultXML = new DOMDocument(); 
        
        if ($apiResultXML->loadXML($apiResult)==true)
        {            
            # Check if the API returned an error
            $apiSuccess = $apiResultXML->getElementsByTagName('Response')->item(0)->getAttribute('success');
           
           if ($apiSuccess == 'true')
            {
                # TODO: Do something useful with the XML                         
            }
            else
            {
				# TODO: Do something with the error returned by the API
            }
        }
        else
        {
            # TODO: Do something with the xml error to either notify or log that the XML could not be parsed            
        }
    }
    else
    {
        # TODO: Do something with the xml error to either notify or log that the XML could not be parsed	
    }            
?>
