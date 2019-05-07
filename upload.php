<?php

$connection = new mysqli("localhost", "root", "12345", "excel");

if(!$connection){
	echo "connection failed<br>";
}

// Include Spout library 
require_once 'spout-2.4.3/src/Spout/Autoloader/autoload.php';

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

$data = array();

// check if file name is not empty
if(!empty($_FILES['file']['name'])) {

	$pathinfo = pathinfo($_FILES['file']['name']);

	// check correct file extension
	if($pathinfo['extension'] == 'xlsx' || $pathinfo['extension'] == 'xls') {

		// temporary file name
        $inputFileName = $_FILES['file']['tmp_name'];

        // read excel file
        $reader = ReaderFactory::create(Type::XLSX);

        // open file
        $reader->open($inputFileName);
        $count = 1;

        // number of sheets in excel file
        foreach ($reader->getSheetIterator() as $sheet) {
        	
        	// number of rows in excel sheet
        	foreach ($sheet->getRowIterator() as $row) {
        		
        		// only read data from "summary" sheet
    			if($sheet->getName() === 'Sheet1') {
	        		
	        		// to read data after header
	        		if($count > 1) {

	        			// data in excel sheet
	        			$sheet_data = array(
		        			'name' => "'".$row[0]."'",
		        			'email' => "'".$row[1]."'",
		        			'phone' => "'".$row[2]."'",
		        			'city' => "'".$row[3]."'"
	        			);
	        			$data[] = $sheet_data;
	        		}
	        		$count++;

	        	}

        	}	

        }

	} else {
		echo "This file is not valid";
	}

} else {
	echo "Please select excel file";
}

//echo "<pre>";print_r($data);

// db insertion
$value = array();
$values = array();

foreach($data as $row) {
	$value[] = "(" . implode(", ", $row) . ")";
}
$values = implode(", ", $value);
//echo $values;

$sql = "INSERT INTO import(name, email, phone, city) VALUES $values";

if($connection->multi_query($sql)) {
    echo "Import success";
} else {
    echo "Import failed";
}