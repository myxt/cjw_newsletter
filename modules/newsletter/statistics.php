<?php
/**
 *
 *  csv nieuwsbrief statistics export
 *  @copyright Copyright 2014 Leiden Tech 
 *  @version
 *
 */

set_time_limit( 0 );
$module = $Params['Module'];
$settingsINI = eZINI::instance( 'cjw_newsletter.ini' );

$http = eZHTTPTool::instance();
$rootNodeID=false;
#Check permsissions and/or if the passed variable is a newsletter object.
$errors=array();
$row=array();

$NodeID = $Params['nodeID'];
//if ( ctype_digit( $NodeID ) ) {
//$ignoreVisibility = false;
//eZFire::debug($NodeID,"MODULE NODE ID");
if ( !$user )
{
	$user = eZUser::instance();
}

$accessResult = $user->hasAccessTo( "CJW Newsletter" , 'statistics' );

if ( $accessResult['accessWord'] != "no" ) {
	if ( ctype_digit( $NodeID ) ) {
		//Coming from a specific list
		$rootNode = eZContentObjectTreeNode::fetch( $NodeID );
		if ($rootNode instanceof eZContentObjectTreeNode) { //Is this a list or an edition?
			if ( $rootNode->attribute('class_identifier') == "cjw_newsletter_list" ) {
				$rootNodeID=$NodeID;
			} else {
				$errors[] = "bad_node";
			}
		} else {
			$errors[] = "no_node";
		}
	}

	if ( !ctype_digit( $rootNodeID ) ) {
		$rootNodeID = $settingsINI->variable('NewsletterSettings','RootFolderNodeId');
		if ( ctype_digit( $rootNodeID ) ) {
			$rootNode = eZContentObjectTreeNode::fetch( $rootNodeID );
			if ($rootNode instanceof eZContentObjectTreeNode) { //Hmm.  This is a root - if a node is supplied you get the list.  Shouldn't matter I hope
				if ( $rootNode->attribute('class_identifier') != "cjw_newsletter_root" ) {
					$errors[] = "bad_node";
					$rootName=$rootNode->Name;
				}
			} else {
				$errors[] = "no_node";
			}
		} else {
			$errors[] = "bad_setting";
		}
	}

	if (count($errors) == 0) {
		$exfilter['id'] = "CjwNewsletterEditionFilter";
		$exfilter['params'] = array("status"=>"archive");

		$rootParams['LoadDataMap']  = true;
		$rootParams['ClassFilterType']      = "include";        // "include" or "exclude"
		$rootParams['ClassFilterArray']     = array("cjw_newsletter_edition"); // array of class_identifiers
		$rootParams['ExtendedAttributeFilter']      = ($exfilter) ? $exfilter : false;  // additional where clause from config
		$editions = eZContentObjectTreeNode::subTreeByNodeID( $rootParams, $rootNodeID );
	//Loop through editions
		foreach ($editions as $edition) {

			$parentNode = eZContentObjectTreeNode::fetch( $edition->ParentNodeID );
//eZFire::debug($parentNode,"Parent");
			$editionName=$edition->Name;
//eZFire::debug($editionName,"EDITION NAME");
			//Loop through kids
//eZFire::debug($edition->children(),"CHLDREN");
		//$children = eZContentObjectTreeNode::subTreeByNodeID( $rootParams, $rootNodeID );

//$exfilter['id'] = "ExtendedImageFilter";
//$exfilter['params'] = array("attribute"=>$picattid);

			foreach ($edition->children() as $child) {
				$childName=$child->attribute( "name" );
//eZFire::debug($child,"CHILD NAME");
				//if ( $rootNode->attribute('class_identifier') == "cjw_newsletter_list" ) {
				foreach($child->Object()->contentObjectAttributes() as $attribute) {
				//look for attributes of ezbinaryfile type  - want the name and path of file
				//look for download count
				//Add it to the array

					if ($attribute->attribute( "data_type_string" ) == "ezbinaryfile" OR  $attribute->attribute( "contentclass_attribute_identifier" ) == "pip_item_file" ) {
						if ($attribute->attribute( "data_type_string" ) == "ezbinaryfile" ) {
							//eZFire::debug($attribute->Content()->attribute("original_filename"),"FILENAME");
							//eZFire::debug($attribute->Content()->attribute("download_count"),"COUNT");
							$filename=$attribute->Content()->attribute("original_filename");
							$downloadCount=$attribute->Content()->attribute("download_count");
						}
						if ( $attribute->attribute( "contentclass_attribute_identifier" ) == "pip_item_file" ) {
							//eZFire::debug($attribute,"ATTRIBUTE");
							if($attribute->attribute( "data_int" )) {
								$docNode =  eZContentObjectTreeNode::fetchByContentObjectID($attribute->attribute( "data_int" ), true );
								$docNode =  $docNode[0];
								$docDataMap = $docNode->Object()->dataMap();

								$docAttribute=$docDataMap['file'];
								
								$filename=$docAttribute->Content()->attribute("original_filename");
								$downloadCount=$docAttribute->Content()->attribute("download_count");
//eZFire::debug($filename,"FILENAME");
//eZFire::debug($downloadCount,"COUNT");
							}
						}
						//newsletter name,link?,newsletter edition, link?, relevent object, filename, link?, count
						$rows[]='"'.$parentNode->Name .'","'.$editionName.'","'.$childName.'","'.$filename.'","'.$downloadCount.'"';
						
					}
				}
			}
		}

		if (count($rows) != 0 ) {
			$today = date("d_m_y_H_i");
			$filename="nieuwsbrief_".$rootName."_".$today.".csv";

			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
			header("Content-Transfer-Encoding: binary");
			$boundary = md5(time());
			header('Content-Type: multipart/form-data; boundary='.$boundary);
			ob_clean();
			echo '"nieuwsbrief","editie","artikel","document","aantal"'."\n";
			//This is the list of stuff
			foreach($rows as $row) {
				//These are the rows.
				//newsletter name,link?,newsletter edition, link?, relevent object, filename, link?, count
				echo $row."\n";
			}
			ob_flush();
			eZExecution::cleanExit();
		}else{
			$errors[] = "No content";
		}
	}
} else {
	$errors[] = "no_access";
}
//eZFire::debug($errors,"ERRORS");
//FAIL result? Is there anything I can actually do here?
$tpl = eZTemplate::factory();
$tpl->setVariable("errors", $errors);

$Result = array();
$Result['content'] = $tpl->fetch( 'design:newsletter/statistics.tpl' );

?>
