<?php
class Alaska_Airport extends CMS_Controller{
	
	public function index(){
		
	}
	
	public function geojson(){
		$this->load->library($this->cms_module_path('gofrendi.gis.core').'/geoformat');
		
		$SQL = "
			SELECT `cat`, `name`, `use`, `elev`, astext(`shape`) as `shape` 
			FROM gis_alaska_airport";
		$shape_column = 'shape';
		
		$popup_content = '';
		$popup_content .= '<b>@name</b><br />';
		$popup_content .= '<p>';
		$popup_content .= ' Usage : @use<br />';
		$popup_content .= ' Elevation : @elev<br />';
		$popup_content .= '</p>';
		
		echo $this->geoformat->sql2json($SQL, $shape_column, $popup_content);
	}
	
}
?>