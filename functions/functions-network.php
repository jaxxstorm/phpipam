<?php

/**
 * Network functions
 *
 */



/* @common functions ---------------- */


/**
 * Resolve reverse DNS name if blank
 * Return class and name
 */
function ResolveDnsName ( $ip ) 
{

    // format to dotted representation
    $ip = Transform2long ( $ip );
    
    // resolve dns name if it is empty and configured
    if ( empty($dns_name) ) {
        $return['class'] = "resolved";
        $return['name']  = gethostbyaddr( $ip );
    }
        
    // if nothing resolves revert to blank
    if ($return['name'] ==  $ip) {
        $return['name'] = "";
    }

    /* return result */
    return($return);
}



/**
 * Present numbers in pow 10, only for IPv6
 */
function reformatNumber ($number)
{

	$length = strlen($number);
	$pos	= $length - 3;
	
	if ($length > 8) {
		$number = "~". substr($number, 0, $length - $pos) . "&middot;10^<sup>". $pos ."</sup>";
	}
	
	return $number;
}


/**
 *	Reformat IP address state
 */
function reformatIPState ($state)
{
	/* 
	0 = not active
	1 = active
	2 = reserved
	*/
	switch ($state)
	{
		case "0": return "Offline"; break;
		case "1": return " "; 		break;
		case "2": return "Reserved";break;
		default: return $state;
	}	
}


/**
 * Verify that switch exists
 */
function verifySwitchByName ($hostname)
{
    /* get variables from config file */
    global $db;
    
    /* set check query and get result */
    $database = new database ($db['host'], $db['user'], $db['pass'], $db['name']);
    $query = 'select * from `switches` where `hostname` = "'. $hostname .'";';
    
    /* fetch role */
    $role = $database->getRow($query);

    /* close database connection */
    $database->close();
    
    /* return true if viewer, else false */
    if (!$role) {
        return false;
    }
    else {
        return true;
    }

}









/* @VLAN functions ---------------- */


/**
 *	Validate VLAN number
 */
function validateVlan ($vlan)
{
	/* must be number:
		not 1
		reserved 1002-1005
		not higher that 4094
	*/
	if(empty($vlan)) {
		return 'ok';
	}
	else if(!is_numeric($vlan)) {
		return 'VLAN must be numeric value!';
	}
	else if ($vlan > 4094) {
		return 'Vlan number can be max 4094';
	}
	else {
		return 'ok';
	}
}









/* @VRF functions ---------------- */


/**
 *	get all VRFs
 */
function getAllVRFs () 
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']);     

	/* execute query */
	$query = "select * from `vrf`;";
    
  	/* update database */
   	$vrfs = $database->getArray($query);
   	
   	/* return false if none, else list */
	if(sizeof($vrfs) == 0) {
		return false;
	}
	else {
		return $vrfs;
	}
}


/**
 *	get vrf details by id
 */
function getVRFDetailsById ($vrfId)
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']);     

	/* execute query */
	$query = 'select * from `vrf` where `vrfId` = "'. $vrfId .'";';
    
  	/* update database */
   	$vrf = $database->getArray($query);
   	
   	/* return false if none, else list */
	if(sizeof($vrf) == 0) {
		return false;
	}
	else {
		return $vrf[0];
	}
}









/* @section functions ---------------- */


/**
 * Get all sections
 */
function fetchSections ()
{
    /* get variables from config file */
    global $db;

    /* set query */
    $query 	  = 'select * from `sections` order by `id` asc;';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);

    /* fetch results */
    $sections  = $database->getArray($query); 

    /* close database connection */
    $database->close();

    /* return subnets array */
    return($sections);
}


/**
 * Get section details - provide section id
 */
function getSectionDetailsById ($id)
{
    /* get variables from config file */
    global $db;

    /* set query, open db connection and fetch results */
    $query 	  = 'select * from sections where id = "'. $id .'";';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $subnets  = $database->getArray($query);
    $database->close();

    /* return subnets array */
    return($subnets[0]);
}










/* @subnet functions ---------------- */


/**
 * Get all subnets
 */
function fetchAllSubnets ()
{
    /* get variables from config file */
    global $db;

    /* set query */
    $query 	  = 'select * from subnets;';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);

    /* fetch results */
    $sections  = $database->getArray($query); 

    /* close database connection */
    $database->close();

    /* return subnets array */
    return($sections);
}


/**
 * Get all subnets in provided sectionId
 */
function fetchSubnets ($sectionId)
{
    /* get variables from config file */
    global $db;

    /* set query, open db connection and fetch results */
    $query 	  = 'select * from subnets where sectionId = "'. $sectionId .'" ORDER BY masterSubnetId,subnet ASC;';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $subnets  = $database->getArray($query);
    $database->close();

    /* return subnets array */
    return($subnets);
}


/**
 * Get all master subnets in provided sectionId
 */
function fetchMasterSubnets ($sectionId)
{
    /* get variables from config file */
    global $db;

    /* set query, open db connection and fetch results */
    $query 	  = 'select * from subnets where sectionId = "'. $sectionId .'" and (`masterSubnetId` = "0" or `masterSubnetId` IS NULL) ORDER BY subnet ASC;';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $subnets  = $database->getArray($query);
    $database->close();

    /* return subnets array */
    return($subnets);
}


/**
 * Get all slave subnets in provided subnetId
 */
function getAllSlaveSubnetsBySubnetId ($subnetId)
{
    /* get variables from config file */
    global $db;

    /* set query, open db connection and fetch results */
    $query 	  = 'select * from subnets where `masterSubnetId` = "'. $subnetId .'" ORDER BY subnet ASC;';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $subnets  = $database->getArray($query);
    $database->close();

    /* return subnets array */
    return($subnets);
}


/**
 * Get all ip addresses in requested subnet bt provided Id
 */
function getIpAddressesBySubnetId ($subnetId) 
{
    /* get variables from config file */
    global $db;
    
    /* set query, open db connection and fetch results */
    $query       = 'select * from ipaddresses where subnetId = "'. $subnetId .'" order by ip_addr ASC;';
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    
    $ipaddresses = $database->getArray($query);
    $database->close();

    /* return ip address array */
    return($ipaddresses);       
}


/**
 * Count number of ip addresses in provided subnet
 */
function countIpAddressesBySubnetId ($subnetId) 
{
    /* get variables from config file */
    global $db;
    
    /* set query, open db connection and fetch results */
    $query       = 'select count(*) from ipaddresses where subnetId = "'. $subnetId .'" order by subnetId ASC;';
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $count 		 = $database->getArray($query);
    $database->close();
    
    /* we only need count field */
    $count	= $count[0]['count(*)'];
    
    /* return ip address array */
    return($count);       
}


/**
 * Get details for requested subnet by Id
 *
 * *** OLD ***  - not used anymore!!!
 */
function getSubnetDetails ($subnetId)
{
    /* get variables from config file */
    global $db;
    
    /* set query, open db connection and fetch results */
    $query         = 'select * from subnets where id = "'. $subnetId .'";';
    $database      = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $SubnetDetails = $database->getArray($query);
    $database->close();

    /* return subnet details - only 1st field! We cannot do getRow because we need associative array */
    return($SubnetDetails[0]); 
}


/**
 * Get details for requested subnet by ID
 */
function getSubnetDetailsById ($id)
{
    /* get variables from config file */
    global $db;
    
    /* set query, open db connection and fetch results */
    $query         = 'select * from subnets where id = "'. $id .'";';
    $database      = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $SubnetDetails = $database->getArray($query);
    $database->close();

    /* return subnet details - only 1st field! We cannot do getRow because we need associative array */
    return($SubnetDetails[0]); 
}


/**
 * Calculate subnet details
 *
 * Calculate subnet details based on input!
 *
 * We must provide used hosts and subnet mask to calculate free hosts, and subnet to identify type
 */
function calculateSubnetDetails ( $usedhosts, $bitmask, $subnet )
{

    // number of used hosts
    $SubnetCalculateDetails['used']              = $usedhosts;
    
    // calculate max hosts
    if ( IdentifyAddress( $subnet ) == "IPv4") {
        $type = 0;
    }
    else {
        $type = 1;
    }
    
    $SubnetCalculateDetails['maxhosts']          = MaxHosts( $bitmask, $type ); 
    
    // calculate free hosts
    $SubnetCalculateDetails['freehosts']         = gmp_strval( gmp_sub ($SubnetCalculateDetails['maxhosts'] , $SubnetCalculateDetails['used']) );

	//reset maxhosts for /31 and /32 subnets
	if (gmp_cmp($SubnetCalculateDetails['maxhosts'],1) == -1) {
		$SubnetCalculateDetails['maxhosts'] = "1";
	}

    // calculate use percentage
    $SubnetCalculateDetails['freehosts_percent'] = round( ( ($SubnetCalculateDetails['freehosts'] * 100) / $SubnetCalculateDetails['maxhosts']), 2 );
 
    return( $SubnetCalculateDetails );
}


/**
 * Check if subnet already exists in section!
 * 
 * Subnet policy:
 *      - inside section subnets cannot overlap!
 *      - same subnet can be configured in different sections
 */
function verifySubnetOverlapping ($sectionId, $subnetNew) 
{
    /* we need to get all subnets in section */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']);  
    
    /* first we must get all subnets in section (by sectionId) */
    $querySubnets     = 'select subnet,mask from subnets where sectionId = "'. $sectionId .'";';  
    $allSubnets       = $database->getArray($querySubnets);   

    /* set new Subnet array */
    $subnet['subnet'] = $subnetNew;
    
    /* IPv4 or ipv6? */
    $type = IdentifyAddress( $subnet['subnet'] );

    /* we need network and broadcast address and check for both if the exist in any network!*/
    if ($type == "IPv4")
    {
        /* verify new against each existing if they exist */
        if (!empty($allSubnets)) {
            foreach ($allSubnets as $existingSubnet) {
            	
            	/* we need cidr format! */
            	$existingSubnet['subnet'] = Transform2long($existingSubnet['subnet']) .'/'. $existingSubnet['mask'];
                                
                if ( verifyIPv4SubnetOverlapping ($subnetNew, $existingSubnet['subnet']) ) {
                    return 'Subnet overlapps with '. $existingSubnet['subnet'];
                }
            }
        }
    }
    else
    {      
        /* verify new against each existing */
        foreach ($allSubnets as $existingSubnet) {
            
            /* we need cidr format! */
            $existingSubnet['subnet'] = Transform2long($existingSubnet['subnet']) .'/'. $existingSubnet['mask'];
            
            if ( verifyIPv6SubnetOverlapping ($subnetNew, $existingSubnet['subnet']) ) {
                return 'Subnet overlapps with '. $existingSubnet['subnet'];
            }
        }
    }
    
    return false;
}


/**
 * Check if nested subnet already exists in section!
 * 
 * Subnet policy:
 *      - inside section subnets cannot overlap!
 *      - same subnet can be configured in different sections
 */
function verifyNestedSubnetOverlapping ($sectionId, $subnetNew) 
{
    /* we need to get all subnets in section */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']);  
    
    /* first we must get all subnets in section (by sectionId) */
    $querySubnets     = 'select subnet,mask from subnets where sectionId = "'. $sectionId .'" and `masterSubnetId` != "0" and `masterSubnetId` IS NOT NULL;';  
    $allSubnets       = $database->getArray($querySubnets);   

    /* set new Subnet array */
    $subnet['subnet'] = $subnetNew;
    
    /* IPv4 or ipv6? */
    $type = IdentifyAddress( $subnet['subnet'] );

    /* we need network and broadcast address and check for both if the exist in any network!*/
    if ($type == "IPv4")
    {
        /* verify new against each existing if they exist */
        if (!empty($allSubnets)) {
            foreach ($allSubnets as $existingSubnet) {
            	
            	/* we need cidr format! */
            	$existingSubnet['subnet'] = Transform2long($existingSubnet['subnet']) .'/'. $existingSubnet['mask'];
                                
                if ( verifyIPv4SubnetOverlapping ($subnetNew, $existingSubnet['subnet']) ) {
                    return 'Subnet overlapps with '. $existingSubnet['subnet'];
                }
            }
        }
    }
    else
    {      
        /* verify new against each existing */
        foreach ($allSubnets as $existingSubnet) {
            
            /* we need cidr format! */
            $existingSubnet['subnet'] = Transform2long($existingSubnet['subnet']) .'/'. $existingSubnet['mask'];
            
            if ( verifyIPv6SubnetOverlapping ($subnetNew, $existingSubnet['subnet']) ) {
                return 'Subnet overlapps with '. $existingSubnet['subnet'];
            }
        }
    }
    
    return false;
}


/**
 * Check if subnet contains slaves
 */
function subnetContainsSlaves($subnetId)
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']); 
    
    /* get all ip addresses in subnet */
    $query 		  = 'SELECT count(*) from subnets where `masterSubnetId` = "'. $subnetId .'";';    
    $slaveSubnets = $database->getArray($query);  
	
	if($slaveSubnets[0]['count(*)']) {
		return true;
	}
	else {
		return false;
	}
}


/**
 * Verify IPv4 subnet overlapping
 *
 * both must be in CIDR format (10.4.5.0/24)!
 * 
 */
function verifyIPv4SubnetOverlapping ($subnet1, $subnet2)
{
    /* IPv4 functions */
    require_once('PEAR/Net/IPv4.php'); 
       
    /* subnet 2 needs to be parsed to get subnet and broadcast */
    $net1 = Net_IPv4::parseAddress( $subnet1 );
    $net2 = Net_IPv4::parseAddress( $subnet2 );

    /* network and broadcast */
    $nw1  = $net1->network;
    $nw2  = $net2->network;
    $bc1  = $net1->broadcast; 
    $bc2  = $net2->broadcast; 
    
    /* network and broadcast in decimal format */
    $nw1_dec  = Transform2decimal( $net1->network);
    $nw2_dec  = Transform2decimal( $net2->network);
    $bc1_dec  = Transform2decimal( $net1->broadcast); 
    $bc2_dec  = Transform2decimal( $net2->broadcast); 
    
    /* calculate delta */
    $delta1 = $bc1_dec - $nw1_dec;
    $delta2 = $bc2_dec - $nw2_dec;
    
    /* calculate if smaller is inside bigger */
    if ($delta1 < $delta2) 
    {
        /* check smaller nw and bc against bigger network */
        if ( Net_IPv4::ipInNetwork($nw1, $subnet2) || Net_IPv4::ipInNetwork($bc1, $subnet2) ) {
            return true;
        }
    }
    else
    {
        /* check smaller nw and bc against bigger network */
        if ( Net_IPv4::ipInNetwork($nw2, $subnet1) || Net_IPv4::ipInNetwork($bc2, $subnet1) ) {
            return true;
        }    
    }  
    return false;
}


/**
 * Verify IPv6 subnet overlapping
 *
 * both must be in CIDR format (2001:fee1::/48)!
 *      subnet1 will be checked against subnet2
 * 
 */
function verifyIPv6SubnetOverlapping ($subnet1, $subnet2)
{
    /* IPv6 functions */
    require_once('PEAR/Net/IPv6.php');
    
    /* remove netmask from subnet1 */
    $subnet1 = Net_IPv6::removeNetmaskSpec ($subnet1);
    
    /* verify */
    if (Net_IPv6::isInNetmask ( $subnet1 , $subnet2 ) ) {
        return true;
    }

    return false;
}


/**
 * Verify that new nested subnet is inside master subnet!
 *
 * $root = root subnet
 * $new  = new subnet that we wish to add to root subnet
 */
function verifySubnetNesting ($rootId, $new)
{
	//first get details for root subnet
	$rootDetails = getSubnetDetailsById($rootId);
	$rootDetails = Transform2long($rootDetails['subnet']) . "/" . $rootDetails['mask'];
	
    /* IPv4 or ipv6? */
    $type1 = IdentifyAddress( $rootDetails );
    $type2 = IdentifyAddress( $new );
    
    /* both must be IPv4 or IPv6 */
	if($type1 != $type2) {
		return false;
		die();
	}

    /* we need network and broadcast address and check for both if the exist in any network!*/
    if(isSubnetInsideSubnet ($new, $rootDetails)) {
    	return true;
    }
    else {
    	return false;
    }
}


/**
 * Verify that subnet a is inside subnet b!
 *
 * both subnets must be in ip format (e.g. 10.10.10.0/24)
 */
function isSubnetInsideSubnet ($subnetA, $subnetB)
{
	$type = IdentifyAddress( $subnetA );
	
	/* IPv4 */
	if ($type == "IPv4") {

    	/* IPv4 functions */
    	require_once('PEAR/Net/IPv4.php'); 
       
    	/* subnet A needs to be parsed to get subnet and broadcast */
    	$net = Net_IPv4::parseAddress( $subnetA );

		//both network and broadcast must be inside root subnet!
		if( (Net_IPv4::ipInNetwork($net->network, $subnetB)) && (Net_IPv4::ipInNetwork($net->broadcast, $subnetB)) ) {
			return true;
		}
		else {
			return false;
		}
	}
	/* IPv6 */
	else {
    	/* IPv6 functions */
    	require_once('PEAR/Net/IPv6.php');
    	
    	/* remove netmask from subnet1 */
    	$subnetA = Net_IPv6::removeNetmaskSpec ($subnetA);
    
	    /* verify */
    	if (Net_IPv6::isInNetmask ( $subnetA, $subnetB ) ) {
        	return true;
    	}
    	else {
    		return false;
    	}
	}
}


/**
 * Check if subnet is admin-locked
 */
function isSubnetWriteProtected($subnetId)
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']); 
    
    /* first update request */
    $query    = 'select `adminLock` from subnets where id = '. $subnetId .';';
    $lock 	  = $database->getArray($query); 
  
	/* return true if locked */
	if($lock[0]['adminLock'] == 1) {
		return true;
	}
	else {
		return false;
	}
}


/**
 * Get VLAN number form Id
 */
function subnetGetVLANdetailsById($vlanId)
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']); 
    
    /* first update request */
    $query    = 'select * from `vlans` where `vlanId` = "'. $vlanId .'";';
    $vlan 	  = $database->getArray($query); 
  
	/* return vlan details */
	return $vlan[0];
}


/**
 * Get all VLANS
 */
function getAllVLANs()
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']); 
    
    /* first update request */
    $query    = 'select * from `vlans`;';
    $vlan 	  = $database->getArray($query); 
  
	/* return vlan details */
	return $vlan;
}


/**
 * Calculate maximum number of IPv4 / IPv6 hosts per subnet
 */
function MaxHosts( $mask, $type = 0 ) 
{
    /* IPv4 address */
    if($type == 0) {
		return pow(2, (32 - $mask)) -2;
    }
     /* IPv6 address */
	else {
	   return gmp_strval(gmp_sub(gmp_pow(2, 128 - $mask) ,1));
    }
}


/**
 *	get all subnets belonging to vrf
 */
function getAllSubnetsInVRF($vrfId)
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']);     

	/* execute query */
	$query = 'select * from `subnets` where `vrfId` = "'. $vrfId .'";';
    
  	/* update database */
   	$vrf = $database->getArray($query);
   	
   	/* return false if none, else list */
	if(sizeof($vrf) == 0) {
		return false;
	}
	else {
		return $vrf;
	}
}











/* @IP address functions ---------------- */


/**
 * Get all IP addresses
 */
function fetchAllIPAddresses ($hostnameSort = false)
{
    /* get variables from config file */
    global $db;
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);

    /* set query */
    if(!$hostnameSort) {
    	$query 	  = 'select * from ipaddresses;'; 
    }
    else {
/*     	$query 	   = 'select * from ipaddresses where `dns_name` != "" order by dns_name asc;';  */
    	$query 	   = 'select * from ipaddresses order by dns_name desc;'; 
    }

    /* fetch results */
    $ipaddresses  = $database->getArray($query); 

    /* close database connection */
    $database->close();

    /* return subnets array */
    return($ipaddresses);
}


/**
 * Get all IP addresses by hostname
 */
function fetchAllIPAddressesByName ($hostname)
{
    /* get variables from config file */
    global $db;

    /* set query */
    $query 	  = 'select * from ipaddresses where `dns_name` like "%'. $hostname .'%" order by `dns_name` desc;';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);

    /* fetch results */
    $ipaddresses  = $database->getArray($query); 

    /* close database connection */
    $database->close();

    /* return subnets array */
    return($ipaddresses);
}


/**
 * Get sectionId for requested name - needed for hash page loading
 */
function getSectionIdFromSectionName ($sectionName) 
{
    /* get variables from config file */
    global $db;
    
    /* set query, open db connection and fetch results */
    $query         = 'select id from sections where name = "'. $sectionName .'";';
    $database      = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $SubnetDetails = $database->getArray($query);
    $database->close();

    /* return subnet details - only 1st field! We cannot do getRow because we need associative array */
    return($SubnetDetails[0]['id']); 

}


/**
 * Check for duplicates on add
 */
function checkDuplicate ($ip, $subnetId)
{
    /* get variables from config file */
    global $db;
    
    /* we need to put IP in decimal format */
    $ip = Transform2decimal ($ip);
    
    /* set query, open db connection and fetch results */
    $query         = 'select * from ipaddresses where ip_addr = "'. $ip .'" and subnetId = "'. $subnetId .'" ;';
    $database      = new database($db['host'], $db['user'], $db['pass'], $db['name']);
    $unique        = $database->getArray($query);
    $database->close();

    /* return false if it exists */
    if (sizeof($unique) != 0 ) {
        return true;
    }
    else {
        return false;
    }
}


/**
 * Modify ( add / edit / delete ) IP address
 */
function modifyIpAddress ($ip) 
{
    /* get variables from config file */
    global $db;
    /* set query, open db connection and fetch results */
    $query    = SetInsertQuery($ip);
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']); 

    if ( !$database->executeQuery($query) ) {
        return false;
    }
    else {
        return true;
    }
}


/**
 * set insert / update / delete query for adding IP address
 * based on provided array
 */
function SetInsertQuery( $ip ) 
{
	/* insert */
	if( $ip['action'] == "Add" ) 
	{
		$query  = "insert into ipaddresses ";
		$query .= "(`subnetId`,`description`,`ip_addr`, `dns_name`,`mac`, `owner`, `state`, `switch`, `port`, `note`) ";
		$query .= "values ";
		$query .= "('". $ip['subnetId'] ."', '". $ip['description'] ."', '". Transform2decimal( $ip['ip_addr'] ) ."', ". "\n"; 
		$query .= " '". $ip['dns_name'] ."', '". $ip['mac'] ."', '". $ip['owner'] ."', '". $ip['state'] ."', ". "\n";
		$query .= " '". $ip['switch'] ."', '". $ip['port'] ."', '". $ip['note'] ."');";
	}
	/* edit multiple */
	else if( ($ip['action'] == "Edit") && ($ip['type'] == "series") ) 
	{
		$query  = "update ipaddresses ";
		$query .= "set `description` = '". $ip['description'] ."', ";
		$query .= "`dns_name` = '". $ip['dns_name'] ."' ,"; 
		$query .= "`mac` = '". $ip['mac'] ."' ,"; 
		$query .= "`owner` = '". $ip['owner'] ."' ,"; 
		$query .= "`state` = '". $ip['state'] ."',";
		$query .= "`switch` = '". $ip['switch'] ."',";
		$query .= "`port` = '". $ip['port'] ."',";
		$query .= "`note` = '". $ip['note'] ."' ";
		$query .= "where `subnetId` = '". $ip['subnetId'] ."' and `ip_addr` = '". Transform2decimal( $ip['ip_addr'] ) ."';";	
	}
	/* edit */
	else if( $ip['action'] == "Edit" ) 
	{
		$query  = "update ipaddresses ";
		$query .= "set `description` = '". $ip['description'] ."', `dns_name` = '". $ip['dns_name'] ."' , `mac` = '". $ip['mac'] ."', ". "\n"; 
		$query .= "`owner` = '". $ip['owner'] ."' , `state` = '". $ip['state'] ."', `switch` = '". $ip['switch'] ."', ". "\n"; 
		$query .= "`port` = '". $ip['port'] ."', `note` = '". $ip['note'] ."' ";
		$query .= "where `id` = '". $ip['id'] ."';";	
	}
	/* delete multiple */
	else if( ($ip['action'] == "Delete") && ($ip['type'] == "series") ) {
		$query = "delete from ipaddresses where `subnetId` = '". $ip['subnetId'] ."' and `ip_addr` = '". Transform2decimal( $ip['ip_addr'] ) ."';";	
	}
	/* delete */
	else if( $ip['action'] == "Delete" ) {
		$query = "delete from ipaddresses where `id` = '". $ip['id'] ."';";	
	}
	
	/* return query */		
	return $query;
}


/**
 * Get IP address details
 */
function getIpAddrDetailsById ($id) 
{
    /* get variables from config file */
    global $db;
    /* set query, open db connection and fetch results */
    $query    = 'select * from ipaddresses where id = "'. $id .'";';
    $database = new database($db['host'], $db['user'], $db['pass'], $db['name']);  
    $details  = $database->getArray($query); 
    
    //we only fetch 1 field
    $details  = $details[0];
	//change IP address formatting to dotted(long)
	$details['ip_addr'] = Transform2long( $details['ip_addr'] ); 
	   
    /* return result */
    return($details);
}



/**
 * verify ip address from edit / add
 */
function VerifyIpAddress( $ip , $subnet ) 
{
	/* First identify it */
	$type = IdentifyAddress( $ip );
	$type = IdentifyAddress( $subnet );
	
	/* IPv4 verification */
	if ( $type == 'IPv4' )
	{
        require_once 'PEAR/Net/IPv4.php';
        
		// is it valid?
		if (!Net_IPv4::validateIP($ip)) {
			$error = "IP address not valid!";
		}
		// it must be in provided subnet
		else if (!Net_IPv4::ipInNetwork($ip, $subnet)) {
			$error = "IP address not in selected subnet!";
		}
		// It cannot be subnet or broadcast
		else {
            $net = Net_IPv4::parseAddress($subnet);
            
            if ($net->network == $ip) {
                $error = "Cannot add subnet as IP address!";   
            }
            else if ($net->broadcast == $ip) {
                $error = "Cannot add broadcast as IP address!"; 
            }
		}
	}
	
	/* IPv6 verification */
	else 
	{
        require_once 'PEAR/Net/IPv6.php';
        
        //remove /xx from subnet
        $subnet_short = Net_IPv6::removeNetmaskSpec($subnet);
		
		// is it valid?
		if (!Net_IPv6::checkIPv6($ip)) {
			$error = "IP address not valid!";
		}
		// it must be in provided subnet
		else if (!Net_IPv6::isInNetmask($ip, $subnet)) {
			$error = "IP address not in selected subnet!";
		}
		//it cannot be subnet
		else if ($ip == $subnet_short) {
		      $error = "Cannot add subnet as IP address!";  
		}
	}
	
	/* return results */
	if( isset($error) )
        return $error;
	else {
		return false;
    }
}


/**
 * verify ip address /mask 10.10.10.10./24 - CIDR 
 *
 * if subnet == 0 we dont check if IP is subnet -> needed for ipCalc
 */
function verifyCidr( $cidr , $subnet = 1 ) 
{
    /* split it to network and subnet */
    $temp = explode("/", $cidr);
    
    $network = $temp[0];
    $netmask = $temp[1];
    
    //if one part is missing die
    if (empty($network) || empty($netmask)) {
        $errors[] = "Invalid CIDR format!";
    }

	/* Identify address type */
	$type = IdentifyAddress( $network );
	
	/* IPv4 verification */
	if ( $type == 'IPv4' )
	{
        require_once 'PEAR/Net/IPv4.php';

        if ($net = Net_IPv4::parseAddress ($cidr)) {
            //validate IP
            if (!Net_IPv4::validateIP ($net->ip)) {
                $errors[] = "Invalid IP address!";
            }
            //network must be same as provided IP address
            else if (($net->network != $net->ip) && ($subnet == 1)) {
                $errors[] = "IP address cannot be subnet! (Consider using ". $net->network .")";
            }
            //validate netmask
            else if (!Net_IPv4::validateNetmask ($net->netmask)) {
                $errors[] = 'Invalid netmask ' . $net->netmask;
            }            
        }
        else {
            $errors[] = 'Invalid CIDR format!';
        }
	}	
	/* IPv6 verification */
	else 
	{
        require_once 'PEAR/Net/IPv6.php';

        //validate IPv6
        if (!Net_IPv6::checkIPv6 ($cidr) ) {
            $errors[] = "Invalid IPv6 address!";
        }
        else {
            
            //validate subnet
            $subnet = Net_IPv6::getNetmask($cidr);
            $subnet = Net_IPv6::compress($subnet);

            $subnetParse = explode("/", $cidr);
            $subnetMask  = $subnetParse[1];
            $subnetNet   = $subnetParse[0];
        
            if ( ($subnetParse[0] != $subnet) && ($subnet == 1) ) {
                $errors[] = "IP address cannot be subnet! (Consider using ". $subnet ."/". $subnetMask .")";
            }
	   }
    }
    
	/* return array of errors */
	return($errors);
}


/**
 * parse IP address
 *
 * IP must be in  CIDR format - '192.168.0.50/16'
 */
function parseIpAddress( $ip, $mask )
{
    /* IPv4 address */
    if ( IdentifyAddress( $ip ) == "IPv4" )
    {
        
        require('PEAR/Net/IPv4.php');
        $net = Net_IPv4::parseAddress( $ip .'/'. $mask );
        
        $out['network']   = $net->network;   // 192.168.0.0
        $out['ip']        = $net->ip;        // 192.168.0.50
        $out['broadcast'] = $net->broadcast; // 192.168.255.255
        $out['bitmask']   = $net->bitmask;   // 16
        $out['netmask']   = $net->netmask;   // 255.255.0.0

    }
    /* IPv6 address */
    else
    {
        require('PEAR/Net/IPv6.php');  

        $out['network']   = $ip;         // 2a34:120:feel::
        $out['bitmask']   = $mask;         // 48
        $out['netmask']   = $mask;         // 48 - we just duplicate it
        
        //broadcast - we fake it with highest IP in subnet
        $net = Net_IPv6::parseaddress( $ip .'/'. $mask );
        
        $out['broadcast'] = $net['end'];    // 2a34:120:feel::ffff:ffff:ffff:ffff:ffff      
    }
    
    return( $out );
} 


/**
 * Find unused ip addresses between two provided
 *
 * subnet must be without subnet
 */
function FindUnusedIpAddresses ($ip1, $ip2, $type, $broadcast = 0 ) 
{              
    /* calculate difference */
    $diff = gmp_strval(gmp_sub($ip2, $ip1));
    
    /* if diff is less than 2 return false */
    if ( $diff < 2 ) {
        return false;
    }
    /* if diff is 2 return 1 IP address in the middle */
    else if ( $diff == 2 ) 
    {
        if ($type == "IPv4") 
        {   //ipv4
			$result['ip'] 	 = long2ip($ip1 +1);
			$result['hosts'] = "1";
        }
        else 
        {   //ipv6
            $ip1_return = gmp_strval(gmp_add($ip1,1));
            
			$result['ip'] 	 = long2ip6( $ip1_return );
			$result['hosts'] = "1";
        }
    }
    /* if diff is more than 2 return pool */
    else 
    {
        if ($type == "IPv4") 
        {   //ipv4
            $free = long2ip($ip1 +1) . ' - ' . long2ip($ip2 -1);
            
			$result['ip'] 	 = $free;
			$result['hosts'] = gmp_strval(gmp_sub($diff, 1));;
        }
        else 
        {   //ipv6
            $ip1_return = gmp_strval(gmp_add($ip1,1));
            
            //No broadcast in IPv6
            if ($broadcast == 0) 
            { 
                $ip2_return = gmp_strval(gmp_sub($ip2,1));
            }
            else
            {
                $ip2_return = gmp_strval($ip2);           
            }
            
            $free = long2ip6( $ip1_return ) . ' - ' . long2ip6( $ip2_return );
            
				$result['ip'] 	 = $free;
				$result['hosts'] = gmp_strval(gmp_sub($diff, 1));
        }
    }
    
    /* return result array with IP range and free hosts */
    return $result;
}


/**
 * Get first available IP address
 */
function getFirstAvailableIPAddress ($subnetId)
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']); 
    
    /* get all ip addresses in subnet */
    $query 		 = 'SELECT ip_addr from ipaddresses where subnetId = "'. $subnetId .'" order by ip_addr ASC;';    
    $ipAddresses = $database->getArray($query);  

    /* get subnet */
    $query 	= 'SELECT subnet from subnets where id = "'. $subnetId .'";';    
    $subnet = $database->getArray($query); 
    $subnet = $subnet[0]['subnet'];
    
    /* create array of IP addresses */
    $ipaddressArray[]	  = $subnet;
    foreach($ipAddresses as $ipaddress) {
    	$ipaddressArray[] = $ipaddress['ip_addr'];
    }
    //get array size
    $size = sizeof($ipaddressArray);
    $curr = 0;
    
    //if size = 0 return subnet +1
    if($size == 1) {
    	$firstAvailable[] = gmp_strval(gmp_add($ipaddressArray[0], 1));
    }
    else {
   	 
    	for($m=1; $m <= $size -1; $m++) {
    	
    		$delta = gmp_strval(gmp_sub($ipaddressArray[$m],$ipaddressArray[$m-1]));
    
    		//compare with previous
    		if ($delta != 1 ) {
    			$firstAvailable[] = gmp_strval(gmp_add($ipaddressArray[$m-1],1));
    		}
    	}
    	
    	//no delta found
    	if (empty($firstAvailable)) {
    		$firstAvailable[] = gmp_strval(gmp_add($ipaddressArray[$size-1],1));
    	} 
    }
    
    /* return first available IP address */
    return $firstAvailable[0];
}


/**
 * Functions to transform IPv6 to decimal and back
 *
 */
function ip2long6 ($ipv6) 
{ 
	if($ipv6 == ".255.255.255") {
		return false;
	}
    $ip_n = inet_pton($ipv6); 
    $bits = 15; // 16 x 8 bit = 128bit 
    $ipv6long = "";
    
    while ($bits >= 0) 
    { 
        $bin = sprintf("%08b",(ord($ip_n[$bits]))); 
        $ipv6long = $bin.$ipv6long; 
        $bits--; 
    } 
    return gmp_strval(gmp_init($ipv6long,2),10); 
} 

function long2ip6($ipv6long) 
{ 
    $bin = gmp_strval(gmp_init($ipv6long,10),2); 
    $ipv6 = "";
    
    if (strlen($bin) < 128) { 
        $pad = 128 - strlen($bin); 
        for ($i = 1; $i <= $pad; $i++) { 
            $bin = "0".$bin; 
        } 
    } 
  
    $bits = 0; 
    while ($bits <= 7) 
    { 
        $bin_part = substr($bin,($bits*16),16);         
        $ipv6 .= dechex(bindec($bin_part)).":"; 
        $bits++; 
    } 
    // compress result
    return inet_ntop(inet_pton(substr($ipv6,0,-1))); 
} 


/**
 * Get all avaialble devices
 */
function getIPaddressesBySwitchName ( $name ) 
{
    /* get variables from config file */
    global $db;
    $database    = new database($db['host'], $db['user'], $db['pass'], $db['name']); 
    
    /* get all vlans, descriptions and subnets */
    $query = 'SELECT * FROM ipaddresses where `switch` = "'. $name .'" order by port ASC;';
    $ip    = $database->getArray($query);  
    
    /* return vlans */
    return $ip;
}










/* @IPcalculations ---------- */

/**
 * Transform IP address from decimal to dotted (167903488 -> 10.2.1.0)
 */
function Transform2long ($ip) 
{
    if (IdentifyAddress($ip) == "IPv4" ) {
        return(long2ip($ip));
    }
    else {
        return(long2ip6($ip));
    }
} 


/**
 * Transform IP address from dotted to decimal (10.2.1.0 -> 167903488)
 */
function Transform2decimal ($ip) 
{
    if (IdentifyAddress($ip) == "IPv4" ) {
        return( sprintf("%u", ip2long($ip)) );
    }
    else {
        return(ip2long6($ip));
    }
} 


/**
 * identify ip address type - ipv4 or ipv6?
 *
 * first we need to find representation - decimal or dotted?
 */
function IdentifyAddress( $subnet ) 
{   
    /* dotted */
    if (strpos($subnet, ":")) {
        return 'IPv6';
    }
    else if (strpos($subnet, ".")) {
        return 'IPv4';
    } 
    /* decimal */
    else  {
        /* IPv4 address */
        if(strlen($subnet) < 12) {
    		return 'IPv4';
        }
        /* IPv6 address */
    	else {
    		return 'IPv6';
        }
    }
}




?>