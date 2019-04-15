<?php

function refreshClients(){
	SqlQuery("Update `CLIENTS` C Set C.`goods_qty` = (Select count(*) From `STR` S Where S.`client`=C.`id` and S.`active`=1 and S.`status`=0);");
}

function refreshClientRubrics(){
	SqlQuery("Delete From `CLIENT_RUBRICS`;");
	SqlQuery("
		Insert Into `CLIENT_RUBRICS` (`client`, `rubric`)
		Select S.`client`, S.`rubric` From `STR` S Where S.`active`=1 and S.`status`=0
		Union
		Select T.`client`, T.`rubric` From `TEXTMODULS` T Where T.`rubric`!=0
		Union
		Select B.`client`, B.`rubric` From `BANNERS` B Where B.`rubric`!=0
	");
	SqlQuery("
		Update `CLIENT_RUBRICS` CR Set CR.`cnt_str`=
		(Select count(*) From `STR` S Where S.`client`=CR.`client` and S.`rubric`=CR.`rubric` and S.`active`=1 and S.`status`=0);
	");
}

function refreshRubricsCnt(){
	// временно блокируем таблицы
	SqlQuery("
		Lock tables
		`RUBRICS_CNT` write,
		`RUBRICS` R write,
		`RUBRICS_CHILD` RCH write,
		`STR` S write,
		`CLIENTS` C write,
		`IDS` write,
		`TEXTMODULS` T write,
		`BANNERS` B write;
	");
	
	SqlQuery("Delete From `RUBRICS_CNT`;");
	
	// записываем в таблицу RUBRICS_CNT количество строчек для каждой рубрики включая родителей
	SqlQuery("
		Insert Into `RUBRICS_CNT`
		Select C.`region`, C.`city`, R.`id` as `id_rubric`, count(S.`id`), 0
		From `RUBRICS` R, `RUBRICS_CHILD` RCH, `STR` S, `CLIENTS` C
		Where R.`id`=RCH.`id` and RCH.`id_child`=S.`rubric` and S.`client`=C.`id` and S.`active`=1 and S.`status`=0
		Group by R.`id`, C.`region`, C.`city`;
	");
	
	SqlQuery("Delete From `IDS`;");
	
	// записываем во временную таблицу в каких рубриках размещается кажая компания
	SqlQuery("
		Insert Into `IDS`
		Select S.`client`, S.`rubric` From `STR` S Where S.`active`=1 and S.`status`=0
		union
		Select T.`client`, T.`rubric` From `TEXTMODULS` T Where T.`rubric`<>0
		union
		Select B.`client`, B.`rubric` From `BANNERS` B Where B.`rubric`<>0;
	");
	
	// записываем в таблицу RUBRICS_CNT количество компаний для каждой рубрики включая родителей
	SqlQuery("
		Insert Into `RUBRICS_CNT` (`region`, `city`, `rubric`, `qty`, `qty_c`)
		Select C.`region`, C.`city`, R.`id` as `id_rubric`, 0, count(distinct IDS.`id`) as `qty_c`
		From `RUBRICS` R, `RUBRICS_CHILD` RCH, `IDS`, `CLIENTS` C
		Where R.`id`=RCH.`id` and RCH.`id_child`=IDS.`id2` and IDS.`id`=C.`id`
		Group by R.`id`, C.`region`, C.`city`
		On duplicate key update `qty_c`=VALUES(`qty_c`);
	");
	
	SqlQuery("Unlock tables;");
}

function refreshCounts(){
	refreshClients();
	refreshClientRubrics();
	refreshRubricsCnt();
}

?>