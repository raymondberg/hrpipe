<?php
//This script generates a pipeline one at a time to help understand/identify errors. Output is a
// folder full of png files, one for the pipeline status after each Fellow. 
// @TODO: This is pretty rough. Need to do a lot of cleanup. 

//This script should be disabled by default on production servers as execution is a resource hog. 
if (true) { exit(1); }
require_once("Pipeline_classes.php");
error_reporting(E_ERROR);
//error_reporting(E_ALL);

$xml = simplexml_load_file("data/fellows.xml");
$fellows = Fellow::manyFromXML($xml); 
//var_dump($fellows);
for($i=0; $i < count($fellows); $i++)
{
	echo "printing $i <br/>";
	$pipe = new Pipeline(array_slice($fellows,$i));

	//var_dump($pipe);
	$png = $pipe->getImage();

	if($png) 
	{
		imagepng($png, "output/o" . (99 - $i ) . ".png");
	}
}

?>