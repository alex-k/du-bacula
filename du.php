#!/usr/bin/php
<?php

$host='localhost';
$user='bacula';
$password='bacula';
$database='bacula';


$jobid=intval($argv[1]);


if (!$jobid) {
echo <<<XML
	Bacula job filesizes. Finds files and directories use most space in the Bacula backup job.
        Usage: php du.php JobID [path] [depth]
                JobID - ID of the completed bacula job
                path  - start point, default /
                depth - depth from the startpoint, default 3

XML;
   
        die(1);
}



$startpath=isset($argv[2]) ? $argv[2] : '/';
$depth = isset($argv[3])  ? intval($argv[3]) : 3;
$depth+=count(explode('/',rtrim($startpath,'/')));



$mysqli = new mysqli($host,$user,$password,$database);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error.PHP_EOL);
}


$que=sprintf("SELECT Path.Path, Filename.Name , File.LStat 
                FROM File left join Path using (PathId) left join Filename using (FilenameId)
                        WHERE File.JobId =  %d
                        and Path.Path LIKE '%s%%' "
                        ,$jobid,$startpath);

$result = $mysqli->query($que);

if ($mysqli->error) {
    die('Query Error (' . $mysqli->errno . ') ' . $mysqli->error.PHP_EOL);
}

if (!$result->num_rows) die ('No files found for this JobID or path'.PHP_EOL);


$du=array();
$total_size=0;
$b64=new BaculaFInfo;

while ($row = $result->fetch_object()){
	$stat=explode(' ',$row->LStat);
	$fsize=$b64->decode($stat[7]);
	$total_size+=$fsize;

	$expp=explode('/',rtrim($row->Path,'/'));
	$path="";
	$i=0;
	foreach ($expp as $p) {
	    $path.=$p."/";
	    $du[$path]= isset($du[$path]) ? $du[$path]+$fsize : $fsize;
	    $i++;
	    if ($i>=$depth) break;
	}
	if ( rtrim($row->Path,'/') == rtrim($startpath,'/') ) {
	    $du[$row->Path.$row->Name]=$fsize;
	}
}

asort($du);

foreach ($du as $path=>$size) {
    printf("%s \t%s\n",formatBytes($size),$path);
}

printf("\n%d files of %s found in %s for job %d\n\n",$result->num_rows, formatBytes($total_size),$startpath,$jobid);


class BaculaFInfo
{
    public static $base64_map = array();
    public static $base64_digits = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

    public function decode($str)
    {
        $val = $i = 0;
        $len = strlen($str);
        while (($i < $len) && ($str[$i] !== ' ')) {
            $val *= 64;
            $val += self::$base64_map[ord($str[$i])];
            $i ++;
        }
        return $val;
    }

    // create base 64 maps
    public function __construct ()
    {
        for ($i = 0; $i < 64; $i ++) {
            self::$base64_map[ord(self::$base64_digits[$i])] = $i;
        }
    }
}

function formatBytes($bytes, $precision = 2) { 
	$fmt='%3d';
	if($precision>0) $fmt='%'.($precision+4).'.'.$precision.'f';
	$units=array('G'=>30,'M'=>20,'K'=>10,'b'=>1);
	foreach ($units as $n=>$u) {
		//if ($bytes>pow(2,$u)) return  sprintf("[%-".(4+$precision)."s]%s",round($bytes/pow(2,$u), $precision),$n);

		if ($bytes>pow(2,$u)) return  sprintf("$fmt %s",$bytes/pow(2,$u),$n);
	}
}

