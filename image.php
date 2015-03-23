<?php
require_once("Pipeline_classes.php");
error_reporting(E_ERROR);
//error_reporting(E_ALL);
//echo "<html><body><pre>";

$DEBUG = False;

$xml = simplexml_load_file("data/fellows.xml");
$fellows = Fellow::manyFromXML($xml); 
//var_dump($fellows);

$pipe = new Pipeline($fellows,2014,2021);
/*
$yearsToProject = 6;

$projProfile = new ProjectionProfile(ProjectionProfile::$HEALTHY); 

$projProfile->setInternHiredLevel(20);
$projProfile->setInternExitLevel(8);
$projProfile->setInternGraduateLevel(6);

$projProfile->setJuniorHiredLevel(6);
$projProfile->setJuniorExitLevel(8);
$projProfile->setJuniorGraduateLevel(4);

$pipe->project($yearsToProject, $projProfile);

if($DEBUG) var_dump($projProfile);
if($DEBUG) var_dump($pipe);
*/
$png = $pipe->getImage();

if($png) 
{
	if(!$DEBUG) header("Content-Type: image/png");
	if(!$DEBUG) imagepng($png);
	exit();
}
echo "MAAAAH";
//echo "</pre></body></html>";
?>