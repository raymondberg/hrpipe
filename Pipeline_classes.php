<?php
//require_once("common_functions.php");
class PipelineHelper {
	const FONT_SIZE_BIG = 40;
	const FONT_SIZE_NORMAL = 20;
	const FONT_SIZE_SMALL	= 10;
	const FONT_FILE = 'img/arial.ttf';
	public static function debug($str)
	{
		echo "\n########################################\n";
		echo "#       ". $str . "\n";
		echo "########################################\n";
	}

	public static function writeImgText($img, $x, $y, $text, $size=PipelineHelper::FONT_SIZE_BIG, $proj=False)
	{
		$grey  = imagecolorallocate($img, 50, 50, 50);
		$black  = imagecolorallocate($img, 10, 10, 10);
		$purple  = imagecolorallocate($img, 200, 10, 200);
		
		if ($size == PipelineHelper::FONT_SIZE_BIG || 
				$size == PipelineHelper::FONT_SIZE_NORMAL || 
				$size == PipelineHelper::FONT_SIZE_SMALL ){
			$fontSize = $size;
		} else {
			$fontSize = PipelineHelper::FONT_SIZE_BIG;
		}
		// Add some shadow to the text
		$text = $text . "";
		$sizeXOffset = 0;
		if(strlen($text)==2)
		{	
			$sizeXOffset = -1*ceil($fontSize / 2);
		}
		$finalColor = ($proj)?$purple:$grey;
		imagettftext($img, $fontSize, 0, $x+1+$sizeXOffset, $y+1, $grey, PipelineHelper::FONT_FILE, $text);
		imagettftext($img, $fontSize, 0, $x+$sizeXOffset, $y, $finalColor, PipelineHelper::FONT_FILE, $text);
		return $img;
	}
	public static function errorImage($text)
	{
			$png  = imagecreatetruecolor(1000,100);
			$bgc = imagecolorallocate($png, 255, 0,0);
			imagefilledrectangle($png, 0, 0, 1000, 650, $bgc);
			return PipelineHelper::writeImgText($png, 100, 75, $text);
	}
}

class FellowStatus{
	const INTERN = 10;
	const JUNIOR = 20;
	const COMPLETED = 30;
	const EXITED = 40;
	
	//in
	const INTERNINHERITED = 11;
	const INTERNHIRED = 12;	
	//spec
	const INTERNGRADUATED = 13;
	//out
	const INTERNEXITED=14;
	const INTERNCARRIED = 15;

	//in
	const JUNIORINHERITED = 21;
	const JUNIORHIRED = 22	;
	//out
	const JUNIORGRADUATED = 23;
	const JUNIOREXITED = 24;
	const JUNIORCARRIED = 25;
	
	public function __construct($fy, $input, $output, $isGraduatingIntern=false)
	{
		$this->fy = $fy; 
		$this->input = $input;
		$this->output = $output;
		$this->isGraduatingIntern = $isGraduatingIntern; 
	}
	
	public function toString()
	{
		$ret = "{" . $this->fy . "::" . $this->input . 
			"::" . $this->output ;
			
		if($this->isGraduatingIntern) $ret = $ret . "++++";
		return $ret . "}"; 
	}
	
	public static function strongStatus($str)
	{
		if( $str == "Intern Hired") return FellowStatus::INTERNHIRED;
		if( $str == "Intern Exited") return FellowStatus::INTERNEXITED;
		if( $str == "Intern Inherited") return FellowStatus::INTERNINHERITED;
		if( $str == "Intern Carried") return FellowStatus::INTERNCARRIED;
		if( $str == "Junior Hired") return FellowStatus::JUNIORHIRED;
		if( $str == "Junior Exited") return FellowStatus::JUNIOREXITED;
		if( $str == "Junior Inherited") return FellowStatus::JUNIORINHERITED;
		if( $str == "Junior Carried") return FellowStatus::JUNIORCARRIED;	
		if( $str == "Junior Graduating") return FellowStatus::JUNIORGRADUATED;
		return "ERROR"; 
	}
	
	public static function simpleStatus($code)
	{
		if( $code == FellowStatus::INTERNHIRED) return "Intern Hired";
		if( $code == FellowStatus::INTERNEXITED) return "Intern Exited";
		if( $code == FellowStatus::INTERNINHERITED) return "Intern Inherited";
		if( $code == FellowStatus::INTERNCARRIED) return "Intern Carried";
		if( $code == FellowStatus::JUNIORHIRED) return "Junior Hired";
		if( $code == FellowStatus::JUNIOREXITED) return "Junior Exited";
		if( $code == FellowStatus::JUNIORINHERITED) return "Junior Inherited";
		if( $code == FellowStatus::JUNIORCARRIED) return "Junior Carried";	
		if( $code == FellowStatus::JUNIORGRADUATED) return "Junior Graduating";
		return "ERROR"; 
	}
	public static function fromXML($xmlSegment)
	{
		/*
			Should be of structure
			<years>
				<year>
					<fy></fy>
					<inputStatus></inputStatus>
					<outputStatus></outputStatus>
					<internGraduated></internGraduated>
				</year>
		*/

		//debug("KICKING it out: " . $xmlSegment->fy . " == " .
		//$xmlSegment->outputStatus . " == " . $xmlSegment->inputStatus);
			 
		$isSGrad = ($xmlSegment->internGraduated == "Yes")? true: false; 
		
		return new FellowStatus((int)$xmlSegment->fy . "", 
			FellowStatus::strongStatus($xmlSegment->inputStatus),
			 FellowStatus::strongStatus($xmlSegment->outputStatus), $isSGrad);
	}
	
	public static function arrayFromXML($xmlSegment)
	{
		$ret = Array();
		try
		{
			foreach($xmlSegment->children() as $child) //Grab years
			{
				array_push($ret, FellowStatus::fromXML($child));
			}
		}
		catch(Exception $e)
		{
			//TODO:FIXME 
		}
		return $ret;
	}
}

class Fellow 
{

	public function __construct($name, $enterStatus, $currentStatus, $yearsStatuses)
	{
		$this->name = $name;
		$this->enterStatus = $enterStatus;
		$this->currentStatus = $currentStatus;
		$this->statuses = $yearsStatuses;
	}
	
	public static function simpleStatus($str)
	{
		if( $str == "Intern" ) return FellowStatus::INTERN;
		if( $str == "Junior") return FellowStatus::JUNIOR;
		if( $str == "Permanent") return FellowStatus::COMPLETED;
		return FellowStatus::EXITED; 
	
	}
	
	public function occupiesYear($year)
	{
		foreach($this->statuses as $status)
		{
			if($status->fy == $year) return true;
		}
		return false; 
	}
	
	public function getYearStatus($year)
	{
		foreach($this->statuses as $status)
		{
			if($status->fy == $year) return $status;
		}
		return null;
	}
	
	public function toString()
	{
		$ret =  $this->name . "::" . $this->enterStatus . "::" . 
			$this->currentStatus . "::St[";
		foreach($this->statuses as $status)
		{
			$ret = $ret . $status->toString(); 
		}
		return $ret . "]";
	}

	public static function fromXML($xmlSegment)
	{
		/*
			Should be of structure
			<student>
			<name></name><enterStatus></enterStatus>
			<currentStatus></currentStatus>
			<years>
				<year>
					<fy></fy>
					<inputStatus></inputStatus>
					<outputStatus></outputStatus>
					<internGraduated></internGraduated>
				</year>
		*/
		//debug("CREATING:: " . $xmlSegment->getName() . "  " . $xmlSegment->name);
		
		$sectionList = $xmlSegment->children();
		$name = $sectionList->name . "";
		$enterStatus = $sectionList->enterStatus;
		$currentStatus = $sectionList->currentStatus;
		
		$years = FellowStatus::arrayFromXML($sectionList->years); 
		
		return new Fellow($name, Fellow::simpleStatus($enterStatus), 
			Fellow::simpleStatus($currentStatus),$years); 
		
	}
	
	public static function manyFromXML($xmlSegment)
	{
		$ret = Array();
		$wrapper = $xmlSegment->fellows; 
		foreach($wrapper->children() as $child)
		{
  			//echo "Adding 1 " . $child->getName();  
  			array_push($ret, Fellow::fromXML($child)); 
		}
		return $ret; 	
	}
}

class FY
{
	private static $IMAGE_INITIAL = "img/pre_no_text.png";
	private static $IMAGE_NORMAL = "img/no_text.png";
	public static $NUMBERS_Y_OFFSET = 40;
	
	public static $IMG_WIDTH = 176;
	public static $PRE_IMG_WIDTH = 151;
	
	public static $IMG_HEIGHT = 607;
	public static $IMG_OFFSET = 40;
	public static $PRE_IMG_HEIGHT = 605;	
	public static $PRE_IMG_OFFSET = 33;	
	public static $ALL_HEIGHT = 650;

	public function __construct($fy=1987, $juniorsInherited = 0, $internsInherited=0,$juniorsHired=0, $internsHired=0, $juniorsExited=0, $internsExited=0, $juniorsGraduating=0, $internsGraduating=0)
	{
		$this->fy=$fy;
		$this->juniorsInherited = $juniorsInherited; $this->internsInherited = $internsInherited; 
		$this->juniorsHired=$juniorsHired; $this->internsHired = $internsHired; 
		$this->juniorsExited=$juniorsExited; $this->internsExited = $internsExited;
		$this->juniorsGraduating=$juniorsGraduating; $this->internsGraduating = $internsGraduating;
		
		$this->calculate();
	}
	
	public static function fromFellows($year, $fellows)
	{
		//debug("Starting investigation of : " . $year); 
		
		$iInherited = 0;
		$sInherited = 0;
		$iHired = 0;
		$sHired = 0;
		$iExited = 0; 
		$sExited = 0;
		$iCarried = 0;
		$sCarried = 0; 
		$iGraduating = 0;
		$sGraduating = 0; 
		
		foreach($fellows as $fellow)
		{
			$status = $fellow->getYearStatus($year);
			if(!empty($status))
			{
				if($status->input == FellowStatus::INTERNHIRED) $sHired++;
				if($status->input == FellowStatus::INTERNINHERITED) $sInherited++;
				if($status->output == FellowStatus::INTERNEXITED) $sExited++;
				if($status->output  == FellowStatus::INTERNCARRIED) $sCarried++;
				if($status->isGraduatingIntern == true) $sGraduating++;
				if($status->input == FellowStatus::JUNIORHIRED) $iHired++;
				if($status->input == FellowStatus::JUNIORINHERITED) $iInherited++;
				if($status->output == FellowStatus::JUNIORGRADUATED) $iGraduating++;
				if($status->output == FellowStatus::JUNIOREXITED) $iExited++;
				if($status->output == FellowStatus::JUNIORCARRIED) $iCarried++;
			}
		}
				
		return new FY($year, $iInherited, $sInherited, $iHired, $sHired, 
			$iExited, $sExited, $iGraduating, $sGraduating);		
	}
	
	public function calculate()
	{
		$this->juniorsCarried = $this->juniorsInherited + $this->internsGraduating + $this->juniorsHired  - 
				$this->juniorsGraduating - $this->juniorsExited; 
				
		$this->internsCarried = $this->internsInherited + $this->internsHired  - 
				$this->internsGraduating - $this->internsExited; 
	}
	
	public function inheritPreviousFy($previousFy)
	{
		$this->internsInherited = $previousFy->internsCarried;
		$this->juniorsInherited = $previousFy->juniorsCarried;
		$this->calculate();
	}
	
	public function generateNextFy()
	{
		return new FY($this->fy + 1, $this->juniorsCarried, $this->internsCarried);
	}

	public function getPreImage()
	{
		$img  = imagecreatetruecolor( FY::$IMG_WIDTH, FY::$ALL_HEIGHT);
		imagesavealpha( $img, true );
		imagealphablending($img, True);
		$bgc = imagecolorallocate($img, 255, 254, 255);
		imagefilledrectangle($img, 0, 0, FY::$IMG_WIDTH, FY::$ALL_HEIGHT, $bgc);
		$imgtemp = imagecreatefrompng(FY::$IMAGE_INITIAL);
		imagecopyresampled($img, $imgtemp, //dst / src images
								0,FY::$PRE_IMG_OFFSET, //dst x / y
								0,0, // src x /y
								FY::$PRE_IMG_WIDTH,FY::$PRE_IMG_HEIGHT, //dst h / w
								FY::$PRE_IMG_WIDTH,FY::$PRE_IMG_HEIGHT); //src h / w
								
		//Numbers 
		$img = PipelineHelper::writeImgText($img,5, FY::$NUMBERS_Y_OFFSET + 30, "Graduates",PipelineHelper::FONT_SIZE_NORMAL); 
		$img = PipelineHelper::writeImgText($img,5, FY::$NUMBERS_Y_OFFSET + 150, "Juniors", PipelineHelper::FONT_SIZE_NORMAL ); 
		$img = PipelineHelper::writeImgText($img,5, FY::$NUMBERS_Y_OFFSET + 400, "Interns", PipelineHelper::FONT_SIZE_NORMAL ); 
		$img = PipelineHelper::writeImgText($img,68, FY::$NUMBERS_Y_OFFSET + 310, "Inherited", PipelineHelper::FONT_SIZE_SMALL ); 
		$img = PipelineHelper::writeImgText($img,61, FY::$NUMBERS_Y_OFFSET + 548, "Inherited", PipelineHelper::FONT_SIZE_SMALL ); 
		$img = PipelineHelper::writeImgText($img,65, FY::$NUMBERS_Y_OFFSET + 355, "New Hires >>", PipelineHelper::FONT_SIZE_SMALL ); 
		$img = PipelineHelper::writeImgText($img,65, FY::$NUMBERS_Y_OFFSET + 590, "New Hires >>", PipelineHelper::FONT_SIZE_SMALL ); 
		$img = PipelineHelper::writeImgText($img,80, FY::$NUMBERS_Y_OFFSET + 280,$this->juniorsCarried ); 
		$img = PipelineHelper::writeImgText($img,80, FY::$NUMBERS_Y_OFFSET + 520,$this->internsCarried ); 
		return $img;
	}	

	public function getImage()
	{
		$img  = imagecreatetruecolor( FY::$IMG_WIDTH, FY::$ALL_HEIGHT);
		imagesavealpha( $img, true );
		imagealphablending($img, True);
	
		$bgc = imagecolorallocate($img, 255, 254, 255);
		imagefilledrectangle($img, 0, 0, FY::$IMG_WIDTH, FY::$ALL_HEIGHT, $bgc);
		
		$imgtemp = imagecreatefrompng(FY::$IMAGE_NORMAL);
		imagecopyresampled($img, $imgtemp, //dst / src images
								0,FY::$IMG_OFFSET, //dst x / y
								0,0, // src x /y
								FY::$IMG_WIDTH,FY::$IMG_HEIGHT, //dst h / w
								FY::$IMG_WIDTH,FY::$IMG_HEIGHT); //src h / w

		//Numbers 
		
		$img = PipelineHelper::writeImgText($img,52,FY::$NUMBERS_Y_OFFSET+ 230,$this->juniorsInherited, PipelineHelper::FONT_SIZE_NORMAL ); 
		$img = PipelineHelper::writeImgText($img,52,FY::$NUMBERS_Y_OFFSET+ 465,$this->internsInherited, PipelineHelper::FONT_SIZE_NORMAL); 
		
		$img = PipelineHelper::writeImgText($img,52,FY::$NUMBERS_Y_OFFSET+ 162,$this->juniorsGraduating, PipelineHelper::FONT_SIZE_NORMAL ); 
		$img = PipelineHelper::writeImgText($img,49,FY::$NUMBERS_Y_OFFSET+ 282,$this->internsGraduating, PipelineHelper::FONT_SIZE_NORMAL ); 
		$img = PipelineHelper::writeImgText($img,52,FY::$NUMBERS_Y_OFFSET+ 412,$this->internsGraduating, PipelineHelper::FONT_SIZE_NORMAL); 
		
		$img = PipelineHelper::writeImgText($img,120,FY::$NUMBERS_Y_OFFSET+  80,$this->juniorsGraduating ); 
		$img = PipelineHelper::writeImgText($img,120,FY::$NUMBERS_Y_OFFSET+ 230,$this->juniorsCarried ); 
		$img = PipelineHelper::writeImgText($img,120,FY::$NUMBERS_Y_OFFSET+ 465,$this->internsCarried ); 
		
		$img = PipelineHelper::writeImgText($img, 127,FY::$NUMBERS_Y_OFFSET+ 162,$this->juniorsExited, PipelineHelper::FONT_SIZE_NORMAL ); 
		$img = PipelineHelper::writeImgText($img,127,FY::$NUMBERS_Y_OFFSET+ 412,$this->internsExited, PipelineHelper::FONT_SIZE_NORMAL); 
		
		$img = PipelineHelper::writeImgText($img, 117,FY::$NUMBERS_Y_OFFSET+ 282,$this->juniorsHired, PipelineHelper::FONT_SIZE_NORMAL ); 
		$img = PipelineHelper::writeImgText($img,110,FY::$NUMBERS_Y_OFFSET+ 504,$this->internsHired, PipelineHelper::FONT_SIZE_NORMAL); 

		$img = PipelineHelper::writeImgText($img, 112,FY::$NUMBERS_Y_OFFSET+ 365,$this->juniorsHired ); 
		$img = PipelineHelper::writeImgText($img, 104,FY::$NUMBERS_Y_OFFSET+ 596,$this->internsHired); 
		
		$img = PipelineHelper::writeImgText($img, 55, 30, "FY" . $this->fy,PipelineHelper::FONT_SIZE_NORMAL);
		
		return $img;
	}	
}

class ProjectionProfile
{
	public static $ZEROED = 0;
	public static $HEALTHY = 1;
	
	public static $PTYPE_PERCENTAGE = 8;
	public static $PTYPE_CONSTANT = 9;
	
	public function __construct($profileLevel=ProjectionProfile::ZEROED)
	{
		$this->juniorExitLevelType = $PTYPE_CONSTANT; 
		$this->juniorExitLevel = 0;
		$this->juniorExitLevelMax = 0; 
		$this->juniorExitLevelMin = 0; 

		$this->internExitLevelType = $PTYPE_CONSTANT; 
		$this->internExitLevel = 0;
		$this->internExitLevelMax = 0; 
		$this->internExitLevelMin = 0; 
		
		$this->juniorGraduateLevelType = $PTYPE_CONSTANT; 
		$this->juniorGraduateLevel = 0;
		$this->juniorGraduateLevelMax = 0; 
		$this->juniorGraduateLevelMin = 0;
		
		$this->internGraduateLevelType = $PTYPE_CONSTANT; 
		$this->internGraduateLevel = 0;
		$this->internGraduateLevelMax = 0; 
		$this->internGraduateLevelMin = 0; 		
	}
	public function setInternHiredLevel($level){
		
	}
	public function projectThisYear($fy)
	{
		//Fixme
		$fy->markOutputProjection();	//change to a half projection 
		$fy->setGraduatingInterns();	//add intern and junior graduation numbers
		$fy->setGraduatingJuniors();
		$fy->setTerminatingInterns();
		$fy->setTerminatingJuniors(); 
		return $fy;
	}
	
	public function projectFromPrevious($pfy)
	{
		//Fixme
		$newFY = $pfy->generateNextYear();
		$newFY->markInputProjection();
		return $this->projectThisYear($newFY);
	}
}

class Pipeline
{
	public function __construct($fellows, $minYear=2001, $maxYear=2010)
	{
		$this->fellows = $fellows; 
		$this->fys = Array();
	 
		for ($i = $minYear; $i<$maxYear; $i++)
		{
			$item = FY::fromFellows($i, $this->getAnnualSubset($i));
			$this->addFY($item);
		}
	}
	public function addFY($item) // no return
	{	
		array_push($this->fys, $item);
	}
	public function getMaxYearNumber() // returns int 
	{	
		$ret = 0;
		foreach($this->fys as $fy)
		{
			if($fy->getYearNumber > $ret) $ret = $fy->getYearNumber;
		}
		return $ret; 
	}
	public function getMinYearNumber() // returns int 
	{ 
		$ret = 9999; //magicnumber
		foreach($this->fys as $fy)
		{
			if($fy->getYearNumber < $ret) $ret = $fy->getYearNumber;
		}
		return $ret; 	
	}
	
	public function getYear($year) // returns FY
	{
		foreach($this->fys as $fy)
		{
			if($fy->getYearNumber() == $year) return $fy;
		}
		return None; 
	}
	public function replaceYear($year, $obj)
	{ 
		for($i = 0 ; $i < count($this->fys); $i++)
		{
			$fy = $this->fys[$i];
			if($fy->getYearNumber() == $year) 
			{
				 $this->fys[$i] = $obj; //FIXME if another replace action is needed
				 return True;
			}
		}
		return False;
	}
	public function getAnnualSubset($year) // returns array
	{
		$ret = Array();
		
		foreach($this->fellows as $fellow)
		{
			if($fellow->occupiesYear($year)) array_push($ret, $fellow); 
		}
		return $ret; 
	}
	
	public function project($yearCount, $projProf=None)
	{
		if(is_empty($projProf)) $projProf = new ProjectionProfile();
		$lastRealYear = $this->getMaxYearNumber();
		$lastFY = $this->getYear($lastRealYear); //Get last year
		$lastFY = $projProf->addProjectionYear($lastFY); //Modify last years numbers, add (#endswithprojection) 
		$this->replaceYear($lastRealYear, $lastFY);//push it back
		for( $i=0; $i<yearCount; $i++) //iterate over years
		{
			$newFY = $projProfile->generateNextYear($lastFY);
			$this->addYear($newFY); //push it on
			$lastFY = $newFY;
		}
	}
	
	public function getImgWidth() // retuns int
	{
		return   FY::$PRE_IMG_WIDTH + (count($this->fys)-1)*FY::$IMG_WIDTH;
	}
	
	public function getImage() // returns image object
	{
		$imgWidth = $this->getImgWidth();
		$imgHeight = FY::$ALL_HEIGHT;
		if (empty($this->fys)) return PipelineHelper::werrorImage("No Such Luck");
		$png  = imagecreatetruecolor( $imgWidth, $imgHeight);
		imagealphablending( $png, false );
		imagesavealpha( $png, true );
		$bgc = imagecolorallocate($png, 255, 254, 255);
		imagefilledrectangle($png, 0, 0, $imgWidth, $imgHeight, $bgc);

		$offset=0;

		for($i=count($this->fys)-1; $i > 0 ;$i--)
		{
			imagecopyresampled($png, $this->fys[$i]->getImage(), //dst / src images
				FY::$PRE_IMG_WIDTH + ($i-1)*(FY::$IMG_WIDTH-15),0, //dst x / y
				0,0, // src x /y
				FY::$IMG_WIDTH,FY::$ALL_HEIGHT, //dst h / w
				FY::$IMG_WIDTH,FY::$ALL_HEIGHT); //src h / w
		}
		imagecopymerge($png, $this->fys[0]->getPreImage(), //dst / src images / something
			0,0, //dst x / y
			0,0, // src x /y
			FY::$PRE_IMG_WIDTH,FY::$ALL_HEIGHT, //dst h / w
			100); 
		
		return $png;
	}
}
?>