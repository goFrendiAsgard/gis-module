<?php

/**
 * Description of gis
 *
 * @author theModuleGenerator
 */
class gis extends CMS_Controller {
	
    public function index($map_id=NULL, $longitude=NULL, $latitude=NULL, $zoom=NULL){
    	$this->load->Model($this->cms_module_path().'/Map_Model');
    	if(!isset($map_id)){ //show list
    		$map = $this->Map_Model->get_map();
    		$data = array("map_list"=> $map);
    		$this->view($this->cms_module_path().'/gis_index_list', $data, 'gis_index');    		
    	}else{ //show the map
    		$map = $this->Map_Model->get_map($map_id);
    		if(isset($longitude)) $map["longitude"] = $longitude;
    		if(isset($latitude)) $map["latitude"] = $latitude;
    		if(isset($zoom)) $map["zoom"] = $zoom;
    		$data = array("map"=> $map);
    		$this->view($this->cms_module_path().'/gis_index_map', $data, 'gis_index');    		
    	}
    }    
    
    public function geojson($layer_id){
    	$this->load->Model($this->cms_module_path().'/Map_Model');
    	$this->load->library($this->cms_module_path().'/geoformat');
    	
    	// get parameter from model
    	$config = $this->Map_Model->get_layer_json_parameter($layer_id);
    	$SQL = $config["json_sql"];
    	$popup_content = $config["json_popup_content"];
    	$label = $config["json_label"];
    	$shape_column = $config["json_shape_column"];
    	
    	echo $this->geoformat->sql2json($SQL, $shape_column, $popup_content, $label);
    }
    
    public function search($layer_id, $keyword=NULL){
    	// get keyword
    	if(!isset($keyword)){
    		$keyword = $this->input->post('keyword');
    	}
    	$keyword = addslashes($keyword);
    	
    	// load model and library
    	$this->load->Model($this->cms_module_path().'/Map_Model');
    	$this->load->library($this->cms_module_path().'/geoformat');
    	
    	// get parameter from model
    	$config = $this->Map_Model->get_layer_search_parameter($layer_id);
    	$SQL = $config["search_sql"];
    	$result_content = $config["search_result_content"];
    	$long_column = $config["search_result_x_column"];
    	$lat_column = $config["search_result_y_column"];
    	
    	// merge keyword into SQL
    	$search = array('@keyword');
    	$replace = array($keyword);
    	$SQL = $this->geoformat->replace($SQL, $search, $replace);
    	
    	$data = array();
    	$query = $this->db->query($SQL);
    	foreach($query->result_array() as $row){
    		// real result content
    		$search = array();
    		$replace = array();
    		foreach($row as $label=>$value){
    			$search[] = '@'.$label;
    			$replace[] = $value;
    		}
    		$real_result_content = $this->geoformat->replace($result_content, $search, $replace);
    		
    		$real_lat_column = $row[$lat_column];
    		$real_long_column = $row[$long_column];
    		
    		$data[] = array(
    				"result_content" => $real_result_content,
    				"latitude" => $real_lat_column,
    				"longitude" => $real_long_column
    			);
    	}
    	echo json_encode($data);
    	
    }

    public function gis_map(){
        $crud = new grocery_CRUD();
        $crud->set_table("gis_map");
        $crud->columns('map_name','map_desc', 'zoom', 'height', 'width', 'gmap_roadmap', 'gmap_satellite', 'gmap_hybrid');
        $crud->display_as('map_name','Map Name')
	        ->display_as('map_desc','Description')
	        ->display_as('longitude','Longitude')
	        ->display_as('latitude','Latitude')
        	->display_as('gmap_roadmap','Use Google Roadmap')
	        ->display_as('gmap_satellite','Use Google Satelite')
	        ->display_as('gmap_hybrid','Use Google Hybrid')
        	->display_as('zoom','Zoom')
	        ->display_as('height','Height')
	        ->display_as('width','Width');
        $crud->set_relation('map_id', 'gis_map', 'map_name');
        $crud->change_field_type('gmap_roadmap', 'true_false');
        $crud->change_field_type('gmap_satellite', 'true_false');
        $crud->change_field_type('gmap_hybrid', 'true_false');
        $output = $crud->render();
        $this->view("grocery_CRUD", $output, "gis_map");
    }
    
    public function gis_layer(){
    	$crud = new grocery_CRUD();
    	$crud->set_table("gis_layer");
    	$crud->columns('map_id','layer_name', 'layer_desc', 'shown');
    	$crud->display_as('map_id','Map Name')
	    	->display_as('layer_name','Layer Name')
	    	->display_as('layer_desc','Description')
	    	->display_as('z_index', 'Z Index')
	    	->display_as('shown','Shown On Startup')
	    	->display_as('radius','Style\'s Radius')
	    	->display_as('fill_color','Style\'s Fill Color')
	    	->display_as('color','Style\'s Color')
	    	->display_as('weight','Style\'s Weight (Boldness)')
	    	->display_as('opacity','Style\'s Opacity')
	    	->display_as('fill_opacity','Style\'s Fill Opacity')
	    	->display_as('image_url','Style\'s Icon')
	    	->display_as('json_sql','Spatial SQL')
	    	->display_as('json_shape_column','Spatial Column')
	    	->display_as('json_popup_content','Popup Content')
	    	->display_as('json_label','Label')
	    	->display_as('use_json_url','Use GeoJSON url')
    		->display_as('json_url','GeoJSON url')
	    	->display_as('search_url','Search url')
	    	->display_as('search_result_content','Search Result Content')
    		->display_as('search_result_x_column','Search Longitude/x Column')
    		->display_as('search_result_y_column','Search Latitude/y Column')
    		->display_as('use_search_url','Use Search url')
    		->display_as('searchable','Searchable')
    		->display_as('search_sql','Search SQL');
    	$crud->set_relation('map_id', 'gis_map', 'map_name');
    	$crud->change_field_type('use_json_url', 'true_false');
    	$crud->change_field_type('use_search_url', 'true_false');
    	$crud->change_field_type('shown', 'true_false');
    	$crud->change_field_type('searchable', 'true_false');
    	$crud->unset_texteditor('json_sql');
    	$crud->unset_texteditor('json_popup_content');
    	$crud->unset_texteditor('json_label');
    	$crud->unset_texteditor('search_sql');
    	$crud->unset_texteditor('search_result_content');
    	$output = $crud->render();
    	$this->view("grocery_CRUD", $output, "gis_layer");
    }
    
    public function gis_cloudmade_basemap(){
    	$crud = new grocery_CRUD();
    	$crud->set_table("gis_cloudmade_basemap");
    	$crud->columns('map_id','basemap_name', 'url');
    	$crud->display_as('map_id','Map Name')
	    	->display_as('basemap_name','Base Map Name')
	    	->display_as('url','URL')
	    	->display_as('max_zoom','Maximum Zoom')
	    	->display_as('attribution','Attribution');
    	$crud->set_relation('map_id', 'gis_map', 'map_name');
    	$output = $crud->render();
    	$this->view("grocery_CRUD", $output, "gis_cloudmade_basemap");
    }


    
}

?>
