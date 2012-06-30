<?php

/**
 * Description of gis
 *
 * @author theModuleGenerator
 */
class gis extends CMS_Controller {
	
    public function index($map_id=NULL, $longitude=NULL, $latitude=NULL){
    	$this->load->Model('gis/Map_Model');
    	if(!isset($map_id)){
    		$map = $this->Map_Model->get_map();
    		$data = array("map_list"=> $map);
    		$this->view('gis/gis_index_list', $data, 'gis_index');    		
    	}else{
    		$map = $this->Map_Model->get_map($map_id);
    		if(isset($longitude)) $map["longitude"] = $longitude;
    		if(isset($latitude)) $map["latitude"] = $latitude;
    		$data = array("map"=> $map);
    		$this->view('gis/gis_index_map', $data, 'gis_index');    		
    	}
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
	    	->display_as('shown','Shown On Startup')
	    	->display_as('radius','Style\'s Radius')
	    	->display_as('fill_color','Style\'s Fill Color')
	    	->display_as('color','Style\'s Color')
	    	->display_as('weight','Style\'s Weight (Boldness)')
	    	->display_as('opacity','Style\'s Opacity')
	    	->display_as('fill_opacity','Style\'s Fill Opacity')
	    	->display_as('image_url','Style\'s Icon')
    		->display_as('json_url','GeoJSON url')
	    	->display_as('display_feature_url','Display url')
	    	->display_as('edit_feature_url','Editting url')
    		->display_as('delete_feature_url','Deleting url');
    	$crud->set_relation('map_id', 'gis_map', 'map_name');
    	$crud->change_field_type('shown', 'true_false');
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
