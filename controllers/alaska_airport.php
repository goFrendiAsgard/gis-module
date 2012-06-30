<?php
class Alaska_Airport extends CMS_Controller{
	
	public function index(){
		
	}
	
	public function geojson(){
		include(APPPATH.'../modules/'.$this->cms_module_path().'/classes/geoPHP/geoPHP.inc');
		$features = array();
		$SQL = "SELECT cat, name, astext(shape) as shape FROM gis_alaska_airport";
		$query = $this->db->query($SQL);
		foreach($query->result() as $row){
			$geom = geoPHP::load($row->shape,'wkt');
			$json = $geom->out('json');
			$features[] = array(
					"type" => "Feature",
					"properties" => array(
							"popupContent"=> $row->name,
					),
					"geometry" => json_decode($json),
			);			
		}
		$feature_collection = array(
				"type" => "FeatureCollection",
				"features" => $features,
		);
		echo json_encode($feature_collection);
		
	}
}
?>