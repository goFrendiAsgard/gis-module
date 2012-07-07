<head>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>modules/<?php echo $cms["module_path"]; ?>/assets/js/leaflet/dist/leaflet.css" />
	<!--[if lte IE 8]><link rel="stylesheet" href="<?php echo base_url(); ?>modules/<?php echo $cms["module_path"]; ?>/assets/js/leaflet/dist/leaflet.ie.css" /><![endif]-->		
	<style type="text/css">
	    .leaflet-container img{
	        z-index : -1;
	    }
	    #change_feature{
	        z-index:3;
	    }
	</style>
	<script type="text/javascript" src="<?php echo base_url(); ?>modules/<?php echo $cms["module_path"]; ?>/assets/js/leaflet/dist/leaflet.js"></script>
	<?php
	// only load google's stuff if needed
	if ($map["gmap_roadmap"] || $map["gmap_satellite"] || $map["gmap_hybrid"]){	
		echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>';
		echo '<script type="text/javascript" src="'.base_url().'modules/'.$cms["module_path"].'/assets/js/google/Google.js"></script>';
	}
	?>	
	<script type="text/javascript" src="<?php echo base_url(); ?>modules/<?php echo $cms["module_path"]; ?>/assets/js/jquery/jquery-1.7.2.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			var map_longitude = <?php echo $map["longitude"]; ?>;
			var map_latitude = <?php echo $map["latitude"]; ?>;
			var map_zoom = <?php echo $map["zoom"]; ?>;
			var map_cloudmade = <?php echo json_encode($map["cloudmade_basemap"]); ?>;
			var map_gmap_roadmap = <?php echo $map["gmap_roadmap"]; ?>;
			var map_gmap_satellite = <?php echo $map["gmap_satellite"]; ?>;
			var map_gmap_hybrid = <?php echo $map["gmap_hybrid"]; ?>;
			var map_layers = <?php echo json_encode($map["layers"]); ?>;
			var map_layer_groups = <?php echo json_encode($map["layer_groups"]); ?>

			var google_roadmap_caption = 'Google Roadmap';
			var google_satellite_caption = 'Google Satellite';
			var google_hybrid_caption = 'Google Hybrid';
			
			var shown_layers = new Array();
			
			// render the base maps and default_shown_base_map
			var baseMaps = new Object();
			for (var i =0; i<map_cloudmade.length; i++){
				cloudmade = map_cloudmade[i];
				cloudmade_attribution = cloudmade["attribution"];
				cloudmade_url = cloudmade["url"];
				cloudmade_name = cloudmade["basemap_name"];
				cloudmade_max_zoom = cloudmade["max_zoom"];
				cloudmade_options = {maxZoom: cloudmade_max_zoom, attribution: cloudmade_attribution};
				baseMaps[cloudmade_name] = new L.TileLayer(cloudmade_url, cloudmade_options);
				if(shown_layers.length == 0) shown_layers[0] = baseMaps[cloudmade_name];
			}
			try{
				if(map_gmap_roadmap){
					baseMaps[google_roadmap_caption] = new L.Google('ROADMAP');
					if(shown_layers.length == 0) shown_layers[0] = baseMaps[google_roadmap_caption];
					shown_layers[shown_layers.length] = baseMaps[google_roadmap_caption];
				}
				if(map_gmap_satellite){
					baseMaps[google_satellite_caption] = new L.Google('SATELLITE');
					if(shown_layers.length == 0) shown_layers[0] = baseMaps[google_satellite_caption];
				}
				if(map_gmap_hybrid){
					baseMaps[google_hybrid_caption] = new L.Google('HYBRID');
					if(shown_layers.length == 0) shown_layers[0] = baseMaps[google_hybrid_caption];
				}
			}catch(err){
				$("div#message").append('Cannot create google maps');	
			}
			
			

			var layer_groups = new Object();
			var overlayMaps = new Object();
			for(var i=0; i<map_layer_groups.length; i++){
				group = map_layer_groups[i];				
				label = group['name'];
				shown = group['shown'];
				layer_groups[label] = new L.GeoJSON();
				if(shown>0){
					shown_layers[shown_layers.length] = layer_groups[label];				
		    	}
				overlayMaps[label] = layer_groups[label];
			}
	
			

			// define map parameter
			var map = new L.Map('map', {
				center: new L.LatLng(map_latitude, map_longitude), zoom: map_zoom,
			});
			// add shown layers to the map
			for(var i=0; i<shown_layers.length; i++){
				map.addLayer(shown_layers[i]);
			}
			

			// add layer control, so that user can adjust the visibility of the layers
			layersControl = new L.Control.Layers(baseMaps, overlayMaps);
			map.addControl(layersControl);

			

			for(var i=0; i<map_layers.length; i++){
				layer = map_layers[i];
				label = layer["group_name"];
				json_url = layer["json_url"];
				
				// TODO : add image/color
				//$('.leaflet-control-layers-overlays label:nth-child('+i+')').append(' <b>'+label+'</b>');
				
				$.ajax({
					//async : false,
					parse_data: {
						layer: layer, 
						label: label},
					url : json_url,
					type : 'GET',
					dataType : 'json',
					success : function(response){
							layer = this.parse_data.layer;
							label = this.parse_data.label;
							geojson_feature = response;	
							var point_config = null;
							var style = null;
							var is_point = geojson_feature['features'][0]['geometry']['type']=='Point';
							style = {
									radius : layer['radius'],
									fillColor: layer['fill_color'],
									color: layer['color'],
									weight: layer['weight'],
									opacity: layer['opacity'],
									fillOpacity: layer['fill_opacity']
								};
							
							// if point
							if(is_point){
								if(layer['image_url']){
									var image_url = layer['image_url'];
									Marker_Icon = L.Icon.extend({
											iconUrl: image_url,
											shadowUrl: null,
											iconSize: new L.Point(20, 20),
											shadowSize: null,
											iconAnchor: new L.Point(14, 37),
											popupAnchor: new L.Point(2, -32)
										});
									point_config = {
											pointToLayer: function (latlng){
										        return new L.Marker(latlng, {
										            icon: new L.Icon({
											            	iconUrl: image_url,
															shadowUrl: null,
															iconSize: new L.Point(32, 37),
															shadowSize: null,
															iconAnchor: new L.Point(14, 37),
															popupAnchor: new L.Point(2, -32)
											            })
										        });
										    }																			
										};
								}else{									
									point_config = {
										    pointToLayer: function (latlng) {
										        return new L.CircleMarker(latlng, 
												        style
										        );
										    },
										}; 
								}
							}

							geojson_layer = new L.GeoJSON(geojson_feature, point_config	);

							geojson_layer.on("featureparse", function (e) {
								// the popups
								if (e.properties && e.properties.popupContent) {
							        popupContent = e.properties.popupContent;
							    }else{
								    popupContent = '';
							    }
							    e.layer.bindPopup(popupContent);

							    // the style (for point we need special treatment)
							    if(!is_point){
							    	e.layer.setStyle(style);
							    }
							    
							});
							
							geojson_layer.addGeoJSON(geojson_feature);	
							layer_groups[label].addLayer(geojson_layer);
											
						}
				});

				
			}
		
		});
	</script>
</head>
<body>
	<div id="map" style="height: <?php echo $map["height"]; ?>; width: <?php echo $map["width"]; ?>"></div>
	<div id="message"></div>
</body>