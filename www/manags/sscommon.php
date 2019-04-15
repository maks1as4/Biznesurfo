<?php
$perSel = 0;
if (count ($_GET) > 0)
	if ( (isset ($_GET['period'])) && ($_GET['period'] != '') && (CheckIntegerUnsign ($_GET['period']) ) )
		$perSel = $_GET['period'];
if (isset($addParam))	$s = $addParam;	else $s='';
$Periods = array (
/*	1 => array (
		'name'	=> '¹ 01 2010',
		'param'	=> '?period=1'.$s,
		'range'	=> "'2010-02-01' and '2010-02-22'"
	),
	2 => array (
		'name'	=> '¹ 02 2010',
		'param'	=> '?period=2'.$s,
		'range'	=> "'2010-02-22' and '2010-03-22'"
	),
	3 => array (
		'name'	=> '¹ 03 2010',
		'param'	=> '?period=3'.$s,
		'range'	=> "'2010-03-22' and '2010-04-26'"
	),
	4 => array (
		'name'	=> '¹ 04 2010',
		'param'	=> '?period=4'.$s,
		'range'	=> "'2010-04-26' and '2010-05-24'"
	),
	5 => array (
		'name'	=> '¹ 05 2010',
		'param'	=> '?period=5'.$s,
		'range'	=> "'2010-05-24' and '2010-06-28'"
	),
	6 => array (
		'name'	=> '¹ 06 2010',
		'param'	=> '?period=6'.$s,
		'range'	=> "'2010-06-28' and '2010-07-26'"
	),
	7 => array (
		'name'	=> '¹ 07 2010',
		'param'	=> '?period=7'.$s,
		'range'	=> "'2010-07-26' and '2010-08-23'"
	),
	8 => array (
		'name'	=> '¹ 08 2010',
		'param'	=> '?period=8'.$s,
		'range'	=> "'2010-08-23' and '2010-09-20'"
	),
	9 => array (
		'name'	=> '¹ 09 2010',
		'param'	=> '?period=9'.$s,
		'range'	=> "'2010-09-20' and '2010-10-18'"
	),
	10 => array (
		'name'	=> '¹ 10 2010',
		'param'	=> '?period=10'.$s,
		'range'	=> "'2010-10-18' and '2010-11-15'"
	),
	11 => array (
		'name'	=> '¹ 11 2010',
		'param'	=> '?period=11'.$s,
		'range'	=> "'2010-11-15' and '2010-12-13'"
	),
	12 => array (
		'name'	=> '¹ 12 2010',
		'param'	=> '?period=12'.$s,
		'range'	=> "'2010-12-13' and '2011-01-24'"
	),
	13 => array (
		'name'	=> '¹ 01 2011',
		'param'	=> '?period=13'.$s,
		'range'	=> "'2011-01-24' and '2011-02-21'"
	),
	14 => array (
		'name'	=> '¹ 02 2011',
		'param'	=> '?period=14'.$s,
		'range'	=> "'2011-02-21' and '2011-03-21'"	
	),
	15 => array (
		'name'	=> '¹ 03 2011',
		'param'	=> '?period=15'.$s,
		'range'	=> "'2011-03-21' and '2011-04-25'"	
	),
	16 => array (
		'name'	=> '¹ 04 2011',
		'param'	=> '?period=16'.$s,
		'range'	=> "'2011-04-25' and '2011-05-23'"	
	),
	17 => array (
		'name'	=> '¹ 05 2011',
		'param'	=> '?period=17'.$s,
		'range'	=> "'2011-05-23' and '2011-06-27'"	
	),
	18 => array (
		'name'	=> '¹ 06 2011',
		'param'	=> '?period=18'.$s,
		'range'	=> "'2011-06-27' and '2011-07-25'"	
	),
	19 => array (
		'name'	=> '¹ 07 2011',
		'param'	=> '?period=19'.$s,
		'range'	=> "'2011-07-25' and '2011-08-22'"	
	),
	20 => array (
		'name'	=> '¹ 08 2011',
		'param'	=> '?period=20'.$s,
		'range'	=> "'2011-08-22' and '2011-09-19'"
	),
	21 => array (
		'name'	=> '¹ 09 2011',
		'param'	=> '?period=21'.$s,
		'range'	=> "'2011-09-19' and '2011-10-17'"	
	),
	22 => array (
		'name'	=> '¹ 10 2011',
		'param'	=> '?period=22'.$s,
		'range'	=> "'2011-10-17' and '2011-11-14'"	
	),
	23 => array (
		'name'	=> '¹ 11 2011',
		'param'	=> '?period=23'.$s,
		'range'	=> "'2011-11-14	' and '2011-12-13'"	
	),
	24 => array (
		'name'	=> '¹ 12 2011',
		'param'	=> '?period=24'.$s,
		'range'	=> "'2011-12-13	' and '2012-01-30'"	
	),
	25 => array (
		'name'	=> '¹ 1 2012',
		'param'	=> '?period=25'.$s,
		'range'	=> "'2012-01-30	' and '2012-02-27'"	
	),
	26 => array (
		'name'	=> '¹ 2 2012',
		'param'	=> '?period=26'.$s,
		'range'	=> "'2012-02-27	' and '2012-03-19'"	
	),
	27 => array (
		'name'	=> '¹ 3 2012',
		'param'	=> '?period=27'.$s,
		'range'	=> "'2012-03-19	' and '2012-04-23'"	
	),
	28 => array (
		'name'	=> '¹ 4 2012',
		'param'	=> '?period=28'.$s,
		'range'	=> "'2012-04-23	' and '2012-05-21'"	
	),
	29 => array (
		'name'	=> '¹ 5 2012',
		'param'	=> '?period=29'.$s,
		'range'	=> "'2012-05-21	' and '2012-06-25'"	
	),
	30 => array (
		'name'	=> '¹ 6 2012',
		'param'	=> '?period=30'.$s,
		'range'	=> "'2012-06-25	' and '2012-07-23'"	
	),
	31 => array (
		'name'	=> '¹ 7 2012',
		'param'	=> '?period=31'.$s,
		'range'	=> "'2012-07-23	' and '2012-08-20'"	
	),
	32 => array (
		'name'	=> '¹ 8 2012',
		'param'	=> '?period=32'.$s,
		'range'	=> "'2012-08-20	' and '2012-09-17'"	
	),
	33 => array (
		'name'	=> '¹ 9 2012',
		'param'	=> '?period=33'.$s,
		'range'	=> "'2012-09-17	' and '2012-10-15'"	
	),
	34 => array (
		'name'	=> '¹ 10 2012',
		'param'	=> '?period=34'.$s,
		'range'	=> "'2012-10-15	' and '2012-11-12'"	
	),
	35 => array (
		'name'	=> '¹ 11 2012',
		'param'	=> '?period=35'.$s,
		'range'	=> "'2012-11-12	' and '2012-12-10'"	
	),
	36 => array (
		'name'	=> '¹ 12 2012',
		'param'	=> '?period=36'.$s,
		'range'	=> "'2012-12-10	' and '2013-01-01'"	
	),

	1 => array (
		'name'	=> '¹ 01 2013',
		'param'	=> '?period=1'.$s,
		'range'	=> "'2013-01-01	' and '2013-02-01'"	
	),
	2 => array (
		'name'	=> '¹ 02 2013',
		'param'	=> '?period=2'.$s,
		'range'	=> "'2013-02-01	' and '2013-03-01'"	
	),
	3 => array (
		'name'	=> '¹ 03 2013',
		'param'	=> '?period=3'.$s,
		'range'	=> "'2013-03-01	' and '2013-04-01'"	
	),
	4 => array (
		'name'	=> '¹ 04 2013',
		'param'	=> '?period=4'.$s,
		'range'	=> "'2013-04-01	' and '2013-05-01'"	
	),
	5 => array (
		'name'	=> '¹ 05 2013',
		'param'	=> '?period=5'.$s,
		'range'	=> "'2013-05-01	' and '2013-06-01'"	
	),
	6 => array (
		'name'	=> '¹ 06 2013',
		'param'	=> '?period=6'.$s,
		'range'	=> "'2013-06-01	' and '2013-07-01'"	
	),
	7 => array (
		'name'	=> '¹ 07 2013',
		'param'	=> '?period=7'.$s,
		'range'	=> "'2013-07-01	' and '2013-08-01'"	
	),
	8 => array (
		'name'	=> '¹ 08 2013',
		'param'	=> '?period=8'.$s,
		'range'	=> "'2013-08-01	' and '2013-09-01'"	
	),
	9 => array (
		'name'	=> '¹ 09 2013',
		'param'	=> '?period=9'.$s,
		'range'	=> "'2013-09-01	' and '2013-10-01'"	
	),
	10 => array (
		'name'	=> '¹ 10 2013',
		'param'	=> '?period=10'.$s,
		'range'	=> "'2013-10-01	' and '2013-11-01'"	
	),
	11 => array (
		'name'	=> '¹ 11 2013',
		'param'	=> '?period=11'.$s,
		'range'	=> "'2013-11-01	' and '2013-12-01'"	
	),
	12 => array (
		'name'	=> '¹ 12 2013',
		'param'	=> '?period=12'.$s,
		'range'	=> "'2013-12-01	' and '2014-01-01'"	
	)
	1 => array (
		'name'	=> '¹ 01 2014',
		'param'	=> '?period=1'.$s,
		'range'	=> "'2014-01-01	' and '2014-02-01'"	
	),
	2 => array (
		'name'	=> '¹ 02 2014',
		'param'	=> '?period=2'.$s,
		'range'	=> "'2014-02-01	' and '2014-03-01'"	
	),
	3 => array (
		'name'	=> '¹ 03 2014',
		'param'	=> '?period=3'.$s,
		'range'	=> "'2014-03-01	' and '2014-04-01'"	
	),
	4 => array (
		'name'	=> '¹ 04 2014',
		'param'	=> '?period=4'.$s,
		'range'	=> "'2014-04-01	' and '2014-05-01'"	
	),
	5 => array (
		'name'	=> '¹ 05 2014',
		'param'	=> '?period=5'.$s,
		'range'	=> "'2014-05-01	' and '2014-06-01'"	
	),
	6 => array (
		'name'	=> '¹ 06 2014',
		'param'	=> '?period=6'.$s,
		'range'	=> "'2014-06-01	' and '2014-07-01'"	
	),
	7 => array (
		'name'	=> '¹ 07 2014',
		'param'	=> '?period=7'.$s,
		'range'	=> "'2014-07-01	' and '2014-08-01'"	
	),
	8 => array (
		'name'	=> '¹ 08 2014',
		'param'	=> '?period=8'.$s,
		'range'	=> "'2014-08-01	' and '2014-09-01'"	
	),
	9 => array (
		'name'	=> '¹ 09 2014',
		'param'	=> '?period=9'.$s,
		'range'	=> "'2014-09-01	' and '2014-10-01'"	
	),
	10 => array (
		'name'	=> '¹ 10 2014',
		'param'	=> '?period=10'.$s,
		'range'	=> "'2014-10-01	' and '2014-11-01'"	
	),
	11 => array (
		'name'	=> '¹ 11 2014',
		'param'	=> '?period=11'.$s,
		'range'	=> "'2014-11-01	' and '2014-12-01'"	
	),
	12 => array (
		'name'	=> '¹ 12 2014',
		'param'	=> '?period=12'.$s,
		'range'	=> "'2014-12-01	' and '2015-01-01'"	
	)
*/
	1 => array (
		'name'	=> '¹ 01 2015',
		'param'	=> '?period=1'.$s,
		'range'	=> "'2015-01-01	' and '2015-02-01'"	
	),
	2 => array (
		'name'	=> '¹ 02 2015',
		'param'	=> '?period=2'.$s,
		'range'	=> "'2015-02-01	' and '2015-03-01'"	
	),
	3 => array (
		'name'	=> '¹ 03 2015',
		'param'	=> '?period=3'.$s,
		'range'	=> "'2015-03-01	' and '2015-04-01'"	
	),
	4 => array (
		'name'	=> '¹ 04 2015',
		'param'	=> '?period=4'.$s,
		'range'	=> "'2015-04-01	' and '2015-05-01'"	
	),
	5 => array (
		'name'	=> '¹ 05 2015',
		'param'	=> '?period=5'.$s,
		'range'	=> "'2015-05-01	' and '2015-06-01'"	
	),
	6 => array (
		'name'	=> '¹ 06 2015',
		'param'	=> '?period=6'.$s,
		'range'	=> "'2015-06-01	' and '2015-07-01'"	
	),
	7 => array (
		'name'	=> '¹ 07 2015',
		'param'	=> '?period=7'.$s,
		'range'	=> "'2015-07-01	' and '2015-08-01'"	
	),
	8 => array (
		'name'	=> '¹ 08 2015',
		'param'	=> '?period=8'.$s,
		'range'	=> "'2015-08-01	' and '2015-09-01'"	
	),
	9 => array (
		'name'	=> '¹ 09 2015',
		'param'	=> '?period=9'.$s,
		'range'	=> "'2015-09-01	' and '2015-10-01'"	
	),
	10 => array (
		'name'	=> '¹ 10 2015',
		'param'	=> '?period=10'.$s,
		'range'	=> "'2015-10-01	' and '2015-11-01'"	
	),
	11 => array (
		'name'	=> '¹ 11 2015',
		'param'	=> '?period=11'.$s,
		'range'	=> "'2015-11-01	' and '2015-12-01'"	
	),
	12 => array (
		'name'	=> '¹ 12 2015',
		'param'	=> '?period=12'.$s,
		'range'	=> "'2015-12-01	' and '2016-01-01'"	
	)
);
$perQty = count ($Periods);
if ($perSel == 0)	$perSel = $perQty;
$range = 'C.sdate between '.$Periods[$perSel]['range'];

?>