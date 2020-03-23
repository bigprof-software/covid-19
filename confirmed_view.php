<?php
// This script and data application were generated by AppGini 5.82
// Download AppGini for free from https://bigprof.com/appgini/download/

	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");
	@include("$currDir/hooks/confirmed.php");
	include("$currDir/confirmed_dml.php");

	// mm: can the current member access this page?
	$perm=getTablePermissions('confirmed');
	if(!$perm[0]) {
		echo error_message($Translation['tableAccessDenied'], false);
		echo '<script>setTimeout("window.location=\'index.php?signOut=1\'", 2000);</script>';
		exit;
	}

	$x = new DataList;
	$x->TableName = "confirmed";

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = array(
		"`confirmed`.`id`" => "id",
		"`confirmed`.`country`" => "country",
		"`confirmed`.`state`" => "state",
		"`confirmed`.`lat`" => "lat",
		"`confirmed`.`long`" => "long",
		"if(`confirmed`.`date`,date_format(`confirmed`.`date`,'%d/%m/%Y'),'')" => "date",
		"`confirmed`.`value`" => "value",
		"`confirmed`.`calc_recovered`" => "calc_recovered",
		"`confirmed`.`calc_deaths`" => "calc_deaths",
	);
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = array(
		1 => '`confirmed`.`id`',
		2 => 2,
		3 => 3,
		4 => '`confirmed`.`lat`',
		5 => '`confirmed`.`long`',
		6 => '`confirmed`.`date`',
		7 => '`confirmed`.`value`',
		8 => '`confirmed`.`calc_recovered`',
		9 => '`confirmed`.`calc_deaths`',
	);

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = array(
		"`confirmed`.`id`" => "id",
		"`confirmed`.`country`" => "country",
		"`confirmed`.`state`" => "state",
		"`confirmed`.`lat`" => "lat",
		"`confirmed`.`long`" => "long",
		"if(`confirmed`.`date`,date_format(`confirmed`.`date`,'%d/%m/%Y'),'')" => "date",
		"`confirmed`.`value`" => "value",
		"`confirmed`.`calc_recovered`" => "calc_recovered",
		"`confirmed`.`calc_deaths`" => "calc_deaths",
	);
	// Fields that can be filtered
	$x->QueryFieldsFilters = array(
		"`confirmed`.`id`" => "ID",
		"`confirmed`.`country`" => "Country/Region",
		"`confirmed`.`state`" => "Province/State",
		"`confirmed`.`lat`" => "Lat",
		"`confirmed`.`long`" => "Long",
		"`confirmed`.`date`" => "Date",
		"`confirmed`.`value`" => "Infected",
		"`confirmed`.`calc_recovered`" => "Recovered",
		"`confirmed`.`calc_deaths`" => "Deaths",
	);

	// Fields that can be quick searched
	$x->QueryFieldsQS = array(
		"`confirmed`.`id`" => "id",
		"`confirmed`.`country`" => "country",
		"`confirmed`.`state`" => "state",
		"`confirmed`.`lat`" => "lat",
		"`confirmed`.`long`" => "long",
		"if(`confirmed`.`date`,date_format(`confirmed`.`date`,'%d/%m/%Y'),'')" => "date",
		"`confirmed`.`value`" => "value",
		"`confirmed`.`calc_recovered`" => "calc_recovered",
		"`confirmed`.`calc_deaths`" => "calc_deaths",
	);

	// Lookup fields that can be used as filterers
	$x->filterers = array();

	$x->QueryFrom = "`confirmed` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm[2]==0 ? 1 : 0);
	$x->AllowDelete = $perm[4];
	$x->AllowMassDelete = true;
	$x->AllowInsert = $perm[1];
	$x->AllowUpdate = $perm[3];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 1;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowPrintingDV = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 25;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation["quick search"];
	$x->ScriptFileName = "confirmed_view.php";
	$x->RedirectAfterInsert = "confirmed_view.php?SelectedID=#ID#";
	$x->TableTitle = "Confirmed COVID-19 cases";
	$x->TableIcon = "resources/table_icons/chart_bar_error.png";
	$x->PrimaryKey = "`confirmed`.`id`";
	$x->DefaultSortField = '`confirmed`.`date`';
	$x->DefaultSortDirection = 'desc';

	$x->ColWidth   = array(  150, 150, 150, 150, 150, 150);
	$x->ColCaption = array("Country/Region", "Province/State", "Date", "Infected", "Recovered", "Deaths");
	$x->ColFieldName = array('country', 'state', 'date', 'value', 'calc_recovered', 'calc_deaths');
	$x->ColNumber  = array(2, 3, 6, 7, 8, 9);

	// template paths below are based on the app main directory
	$x->Template = 'templates/confirmed_templateTV.html';
	$x->SelectedTemplate = 'templates/confirmed_templateTVS.html';
	$x->TemplateDV = 'templates/confirmed_templateDV.html';
	$x->TemplateDVP = 'templates/confirmed_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HighlightColor = '#FFF0C2';
	$x->HasCalculatedFields = true;

	// mm: build the query based on current member's permissions
	$DisplayRecords = $_REQUEST['DisplayRecords'];
	if(!in_array($DisplayRecords, array('user', 'group'))) { $DisplayRecords = 'all'; }
	if($perm[2]==1 || ($perm[2]>1 && $DisplayRecords=='user' && !$_REQUEST['NoFilter_x'])) { // view owner only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `confirmed`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='confirmed' and lcase(membership_userrecords.memberID)='".getLoggedMemberID()."'";
	}elseif($perm[2]==2 || ($perm[2]>2 && $DisplayRecords=='group' && !$_REQUEST['NoFilter_x'])) { // view group only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `confirmed`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='confirmed' and membership_userrecords.groupID='".getLoggedGroupID()."'";
	}elseif($perm[2]==3) { // view all
		// no further action
	}elseif($perm[2]==0) { // view none
		$x->QueryFields = array("Not enough permissions" => "NEP");
		$x->QueryFrom = '`confirmed`';
		$x->QueryWhere = '';
		$x->DefaultSortField = '';
	}
	// hook: confirmed_init
	$render=TRUE;
	if(function_exists('confirmed_init')) {
		$args=array();
		$render=confirmed_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: confirmed_header
	$headerCode='';
	if(function_exists('confirmed_header')) {
		$args=array();
		$headerCode=confirmed_header($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$headerCode) {
		include_once("$currDir/header.php"); 
	}else{
		ob_start(); include_once("$currDir/header.php"); $dHeader=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%HEADER%%>', $dHeader, $headerCode);
	}

	echo $x->HTML;
	// hook: confirmed_footer
	$footerCode='';
	if(function_exists('confirmed_footer')) {
		$args=array();
		$footerCode=confirmed_footer($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$footerCode) {
		include_once("$currDir/footer.php"); 
	}else{
		ob_start(); include_once("$currDir/footer.php"); $dFooter=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%FOOTER%%>', $dFooter, $footerCode);
	}
?>