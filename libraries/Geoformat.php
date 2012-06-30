<?php

class Geoformat{
	
	private function replace($str,$search,$replace){
		if(count($search)==count($replace)){
			for($i=0; $i<count($search); $i++){
				$str = str_replace($search, $replace, $str);
			}
		}
		return $str;
	}
	
	public function sql2json($SQL, $shape_column, $popup_content=NULL){
		$CI =& get_instance();
		require_once(APPPATH.'../modules/'.
				$CI->cms_module_path('gofrendi.gis.core').
				'/classes/geoPHP/geoPHP.inc');
		
		$features = array();
		$query = $CI->db->query($SQL);
		foreach($query->result_array() as $row){
			$geom = geoPHP::load($row[$shape_column],'wkt');
			$json = $geom->out('json');
			
			$real_popup_content = "";
			if(isset($popup_content)){
				$search = array();
				$replace = array();
				foreach($row as $label=>$value){
					$search[] = '@'.$label;
					$replace[] = $value;
				}
				$real_popup_content = $this->replace($popup_content, $search, $replace);
			}
			$features[] = array(
					"type" => "Feature",
					"properties" => array(
							"popupContent"=> $real_popup_content,
					),
					"geometry" => json_decode($json),
			);
		}
		$feature_collection = array(
				"type" => "FeatureCollection",
				"features" => $features,
		);
		return json_encode($feature_collection);
	}	
}

?>