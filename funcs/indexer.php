<?php

require_once 'config.php';
require_once 'dbconnect.php';
require_once 'biz_funcs.php';

if (!(ConnectDB()))
	die;

SqlQuery('lock tables OPTIONS write;');
$qRes = SqlQuery('select `value` from OPTIONS where name="indexing_now";');
if (($qRes)&&($Row=mysql_fetch_row($qRes))&&($Row[0]==1))
	die;
@mysql_free_result($qRes);
SqlQuery('update OPTIONS set value=1 where name="indexing_now";');
SqlQuery('unlock tables;');
	
// �������� ���� ��������� ����������	
$qTxt = 'select `value` from OPTIONS where name="last_indexation";';
$qRes = SqlQuery($qTxt);
if (($qRes)&&($Rows=mysql_fetch_row($qRes))) {
	$last_indexation = $Rows[0];
} else
	$last_indexation = '1900-01-01 00:00:00';
@mysql_free_result($qRes);

$curdate = date("Y-m-d H:i:s");

// �������� ������� ���������� ��� ����� ����������
$qTxt =	'select '.
		'STR.id,'.				// 0
		'STR.name,'.			// 1
		'STR.active,'.			// 2
		'STR.status,'.			// 3
		'RUBRICS.full_name '.	// 4
		'from STR,RUBRICS '.
		'where STR.rubric=RUBRICS.id '.
		'and STR.udate>"'.$last_indexation.'"';
$qRes = SqlQuery($qTxt);
if ($qRes) {
	while($Rows=mysql_fetch_row($qRes)) {
		// ���� ������� ��������� � ������ ��� ��������, ������� � �� �������		
		if (($Rows[2]==0)||($Rows[3]>0)) {
			SqlQuery('delete from STR_INDEX where str='.$Rows[0]);
		} else {
			// � ��������� ������ ��������� ������� �� ��������� �����
			preg_match_all('/([�-��a-z�-ߨA-Z\d]+((?<=\d)[,.](?=\d)\d+)*)/i', $Rows[1], $result);
			for ($i = 0; $i < count($result[0]); $i++) {
				// ��� ������� ����� ���� ����
				$qRes2 = SqlQuery('select base from WORDFORMS where val="'.mysql_real_escape_string($result[0][$i]).'"');
				if (($qRes2)&&($Rows2=mysql_fetch_row($qRes2))) {
					// ���� ���� �������, ��������� ����� � ������
					SqlQuery('insert ignore into STR_INDEX values('.$Rows2[0].','.$Rows[0].','.$i.');');
				} else {
					@mysql_free_result($qRes2);
					// ���� ���� �� �������, ������� ����� ���� � ��������� ����� � ������
					$qRes2 = SqlQuery('select max(base)+1 from WORDFORMS');
					if (($qRes2)&&($Rows2=mysql_fetch_row($qRes2))) {
						SqlQuery('insert into WORDFORMS values('.$Rows2[0].',"'.mysql_real_escape_string(strtoupper($result[0][$i])).'");');
						SqlQuery('insert ignore into STR_INDEX values('.$Rows2[0].','.$Rows[0].','.$i.');');
					}
				}
				@mysql_free_result($qRes2);
			}
		}		
	}
	
	// ��������� ���� ����������
	SqlQuery('update OPTIONS set value="'.$curdate.'" where name="last_indexation"');
}
@mysql_free_result($qRes);

SqlQuery('lock tables OPTIONS write;');
SqlQuery('update OPTIONS set value=0 where name="indexing_now";');
SqlQuery('unlock tables;');

?>