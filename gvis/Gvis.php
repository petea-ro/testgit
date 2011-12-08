<?php

/*----------------------------------------------------------------------
gVis - Google Visualization Library
===============
Quickly creates a Google Visualizations

Version 0.6 - 1/29/2010
Copyright Steve Barbera 2010 - whobutsb@gmail.com
This script is licensed under the Creative Commons License.
----------------------------------------------------------------------*/

// --------------------------------------------------------------------------
/*

1/29/2010
-Added set_colors() method.
Arguments can be an array or a list of strings.
example: set_colors('pink', 'green') or set_colors(array('blue', 'beige')); 


*/
// --------------------------------------------------------------------------


 
 

class Gvis{
	
	var $rows = array(); 
	var $heading = array(); 
	var $gVis; 
	var $package;
	
	//Optional Parameters
	var $height = 200; 
	var $width = 450;  
	var $title = ''; 
	var $is3D = 'false'; 
	var $legend = 'right';
	var $colors = array();  
	
	function Gvis(){
		
	}
	
	function set_is3D($is3D){
		if($is3D){
			$this->is3D = 'true'; 
			return;
		}
	}
	
	function set_legend($legend){
		$this->legend = $legend; 
	}
	
	function set_title($title){
		$this->title = $title; 
	}
	
	function set_width($width){
		$this->width = $width; 
	}
	
	function set_height($height){
		$this->height = $height;
	}
	
	
	function set_heading(){
		$args = func_get_args();
		$this->heading[] = (is_array($args[0])) ? $args[0] : $args;
	}
	
	
	function add_row(){
		$args = func_get_args();
		$this->rows[] = (is_array($args[0])) ? $args[0] : $args; 
	}
	
	function set_visualization($vis){
		$this->gVis = $vis; 
		$this->package = strtolower($vis); 
	}
	
	function set_colors(){
		$args = func_get_args(); 
		$this->colors[] = (is_array($args[0])) ? $args[0] : $args;
	}
	
	
	function clear(){
		$this->rows = array(); 
		$this->heading = array(); 
		$this->gVis; 
		$this->package;
		$this->height = 200; 
		$this->width = 450; 
		$this->is3D = 'false'; 
		$this->lengend = 'right'; 
		$this->colors = array(); 
	}
	
	
	function generate(){
		
		//Check to see if we have heading values and row values
		if(count($this->heading) == 0 && count($this->rows) == 0){
			return 'Undefined table data';
		}
		
		//Add the Google JS
		$data = "<script type='text/javascript'>\n";
		$data .= "google.load('visualization', '1', {packages:['".$this->package."']});\n";
		$data .= "google.setOnLoadCallback(draw".$this->gVis.");\n";
		$data .= "function draw".$this->gVis."(){\n"; 
		$data .= "var gData = new google.visualization.DataTable();\n";

		//Build the Google Table Data
		
		
		//Create the Column Headers		
		foreach($this->heading as $heading){
			$data .= "gData.addColumn('$heading[0]', '$heading[1]');\n"; 
		} 
		
		//Add the Row Count
		$data .= "gData.addRows(".count($this->rows).");\n";
			
		//Add the Rows
		$rowCount = 0; 
		foreach($this->rows as $row){
			$columnCount = 0; 
			foreach($row as $rowData){
				switch($this->heading[$columnCount][0]){
					case 'string': 
						$data .= "gData.setValue($rowCount, $columnCount, '$rowData');\n";
					break;
					case 'number':
						$data .= "gData.setValue($rowCount, $columnCount, $rowData);\n";
					break;
					case 'date':
						//Get the Date Info
						$date = date('Y, n-1, j', strtotime($rowData));	//Jan = 0, Dec = 11, hence the n-1
						$data .= "gData.setValue($rowCount, $columnCount, new Date($date));\n";
				}
				$columnCount++;  
			}
			$rowCount++; 
		}
		
		$data .= "var chart = new google.visualization.".$this->gVis."(document.getElementById('".$this->gVis."_div'));\n"; 
		
		$data .= "chart.draw(gData, {
			height:		".$this->height.", 
			width:  	".$this->width.",
			title: 		'".$this->title."', 
			is3D:  		".$this->is3D.", 
			legend: 	'".$this->legend."', 
			colors: 	[".$this->_compile_colors()."]
		});\n";
		
		//End the Javascript
		$data .= "} </script>";
		
		//Create the visualization <div>
		$data .= "<div style='width: ".$this->width."px; height: ".$this->height."px;' id='".$this->gVis."_div'></div>";  
		
		return $data; 
	}
	
	
	function _compile_colors(){
		
		if(!empty($this->colors)){
			
			if(count($this->colors[0]) == 1){
				$color = $this->colors[0][0]; 
				return "'$color'"; 
			}
			
			foreach($this->colors[0] as $color){
				$colors[] = "'$color'"; 
			}
			
			return implode(', ', $colors); 
		}

		return ''; 
	}

}