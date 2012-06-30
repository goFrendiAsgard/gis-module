<?php

/**
 * Description of gis
 *
 * @author theModuleGenerator
 */
class gis extends CMS_Controller {
	
    public function index($map_id=NULL){
    	$this->load->Model('gis/Map_Model');
    	if(!isset($map_id)){
    		$data = array("map_list"=> $this->Map_Model->get_map());
    		$this->view('gis/gis_index_list', $data, 'gis_index');    		
    	}else{
    		$data = array("map"=> $this->Map_Model->get_map($map_id));
    		$this->view('gis/gis_index_map', $data, 'gis_index');    		
    	}
    }    

    public function gis_map(){
        $crud = new grocery_CRUD();
        $crud->set_table("gis_map");
        $crud->display_as('map_name','Map Name')
	        ->display_as('map_desc','Description')
	        ->display_as('longitude','Longitude')
	        ->display_as('latitude','Latitude')
        	->display_as('gmap_roadmap','Use Google Roadmap')
	        ->display_as('gmap_satelite','Use Google Satelite')
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
    	$crud->display_as('map_id','Map Name')
	    	->display_as('layer_name','Layer Name')
	    	->display_as('layer_desc','Description')
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
    	$output = $crud->render();
    	$this->view("grocery_CRUD", $output, "gis_layer");
    }
    
    public function gis_cloudmade_basemap(){
    	$crud = new grocery_CRUD();
    	$crud->set_table("gis_cloudmade_basemap");
    	$crud->display_as('map_id','Map Name')
	    	->display_as('base_map','Base Map')
	    	->display_as('url','URL')
	    	->display_as('max_zoom','Maximum Zoom')
	    	->display_as('attribution','Attribution');
    	$crud->set_relation('map_id', 'gis_map', 'map_name');
    	$output = $crud->render();
    	$this->view("grocery_CRUD", $output, "gis_cloudmade_basemap");
    }


    
}

?>
