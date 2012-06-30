<?php
	class Map_Model extends CMS_Model{
		
		public function get_map($map_id=NULL){
			if(isset($map_id)){
				$SQL = "SELECT * FROM gis_map WHERE map_id = ".addslashes($map_id);
				$query = $this->db->query($SQL);
				if($query->num_rows()>0){
					$row = $query->row_array();
					$row['layers'] = $this->get_layer($map_id);
					$row['cloudmade_basemap'] = $this->get_cloudmade_basemap($map_id);
					return $row;
				}else{
					return NULL;
				}
			}else{
				$SQL = "SELECT * FROM gis_map";	
				$query = $this->db->query($SQL);
				$data = array();
				foreach($query->result_array() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		
		public function get_layer($map_id){
			$SQL = "SELECT * FROM gis_layer WHERE map_id = '".addslashes($map_id)."'";
			$query = $this->db->query($SQL);
			$data = array();
			foreach($query->result_array() as $row){
				$row["image_url"] = $this->cms_parse_keyword($row["image_url"]);
				$row["json_url"] = $this->cms_parse_keyword($row["json_url"]);
				$row["display_feature_url"] = $this->cms_parse_keyword($row["display_feature_url"]);
				$row["edit_feature_url"] = $this->cms_parse_keyword($row["edit_feature_url"]);
				$row["delete_feature_url"] = $this->cms_parse_keyword($row["delete_feature_url"]);
				$data[] = $row;
			}
			return $data;
		}
		
		public function get_cloudmade_basemap($map_id){
			$SQL = "SELECT * FROM gis_cloudmade_basemap WHERE map_id = '".addslashes($map_id)."'";
			$query = $this->db->query($SQL);
			$data = array();
			foreach($query->result_array() as $row){
				$data[] = $row;
			}
			return $data;
		}
		
	}
?>