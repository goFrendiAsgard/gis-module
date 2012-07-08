<?php
class Alaska_Airport extends CMS_Controller{
	
	public function index(){
		
	}
	
	public function geojson(){
		$this->load->library($this->cms_module_path('gofrendi.gis.core').'/geoformat');
		
		$SQL = "
			SELECT `cat`, `name`, `use`, `elev`, astext(`shape`) as `shape` 
			FROM gis_alaska_airport 
			WHERE 
				(`use`<>'Military') AND (`use`<>'Civilian/Public') AND
				MBRIntersects(`shape`,geomfromtext('@map_region'))=1";
		$shape_column = 'shape';		
		
		$popup_content = '';
		$popup_content .= '<b>@name</b><br />';
		$popup_content .= '<p>';
		$popup_content .= ' Usage : @use<br />';
		$popup_content .= ' Elevation : @elev<br />';
		$popup_content .= '</p>';
		
		$label = '@name';
		
		echo $this->geoformat->sql2json($SQL, $shape_column, $popup_content, $label);
	}
	
}
?>