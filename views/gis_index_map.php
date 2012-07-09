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
	    .layer_legend{
	    	list-style-type: none;
	    }
	    .layer_legend div{
	    	margin: 2px;
	    	width: 10px;
	    	height: 10px;
	    	display: inline-block;
	    }
	    .layer_legend div img{
	    	height: 10px;
	    	width: 10px;
	    }
	    /*
	    g>path{
	    	z-index: 5;
	    }
	    g>path[d*="L"]{
	    	z-index: -1;	    	
	    }*/
	    
	</style>
	<script type="text/javascript" src="<?php echo base_url(); ?>modules/<?php echo $cms["module_path"]; ?>/assets/js/leaflet/dist/leaflet.js"></script>
	<?php
	// only load google's stuff if needed
	if ($map["gmap_roadmap"] || $map["gmap_satellite"] || $map["gmap_hybrid"]){	
		echo '<script type="text/javascript" src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>';
		echo '<script type="text/javascript" src="'.base_url().'modules/'.$cms["module_path"].'/assets/js/leaflet-google/Google.js"></script>';
	}
	?>	
	<script type="text/javascript" src="<?php echo base_url(); ?>modules/<?php echo $cms["module_path"]; ?>/assets/js/jquery/jquery-1.7.2.min.js"></script>
	<script type="text/javascript">
		L.Icon.Text = L.Icon.extend({
			initialize: function (text, options) {
				this._text = text;
				L.Icon.prototype.initialize.apply(this, [options]);
			},
	
			createIcon: function() {
				var el = document.createElement('div');
				el.appendChild(document.createTextNode(this._text));
				this._setIconStyles(el, 'icon');
				el.style.textShadow = "1px 1px 5px #fff";
				el.style.color = "#000000";
				el.style.fontSize = "9px";
				return el;
			},
	
			createShadow: function() { return null; }
	
		});
	
		L.Marker.Text = L.Marker.extend({
			initialize: function (latlng, text, options) {
		        	L.Marker.prototype.initialize.apply(this, [latlng, options]);
				this._fakeicon = new L.Icon.Text(text);
			},
	
			_initIcon: function() {
		        	L.Marker.prototype._initIcon.apply(this);
	
				var i = this._icon, s = this._shadow, obj = this.options.icon
				this._icon = this._shadow = null;
	
				this.options.icon = this._fakeicon;
		        	L.Marker.prototype._initIcon.apply(this);
				this.options.icon = obj;
	
				if (s) {
					s.parentNode.removeChild(s);
					this._icon.appendChild(s);
				}
	
				i.parentNode.removeChild(i);
				this._icon.appendChild(i);
	
				var w = this._icon.clientWidth, h = this._icon.clientHeight;
				this._icon.style.marginLeft = -w / 2 + "px";
				//this._icon.style.backgroundColor = "red";
				var off = new L.Point(w/2, 0);
				if (L.Browser.webkit) off.y = -h;
				L.DomUtil.setPosition(i, off);
				if (s) L.DomUtil.setPosition(s, off);
			}
		});
		
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
			var layer_group_indexes = new Object();
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
				layer_group_indexes[label] = i+1;
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
			
			// jquery css hack to show the legends
			for(var i=0; i<map_layers.length; i++){
				var layer = map_layers[i];
				var layer_name = layer["layer_name"];
				var group_name = layer["group_name"];
				
				// css hack to modify to legends
				var label_index = layer_group_indexes[group_name];
				var label_identifier = '.leaflet-control-layers-overlays label:nth-child('+label_index+')';
				var ul_identifier = label_identifier+' ul';
				if ($(ul_identifier).length == 0){
					$(label_identifier).append('<ul></ul>');
				}
				$(ul_identifier).append('<li id="layer_'+i+'" class="layer_legend"><div></div>'+layer_name+'</li>');
				var div_identifier = ul_identifier+' li#layer_'+i+' div';
				if(layer['image_url']!=''){
					$(div_identifier).html('<img src="'+layer['image_url']+'" />');
				}else{
					$(div_identifier).css({'background-color': layer['fill_color']});
				}
			}

			fetchLayer();

			// search button
			$("#btn_gis_search").click(function(){
				$('#gis_search_result').html('');
				for(var i=0; i<map_layers.length; i++){
					var layer = map_layers[i];
					var searchable = layer["searchable"]>0;
					if(searchable){
						layer_name = layer["layer_name"];
						if($('#gis_search_layer_'+i).attr('checked')){
							var search_url = layer["search_url"];
							$.ajax({
								url: search_url,
								data: {keyword: $('#gis_search_keyword').val()},
								type: 'POST',
								dataType: 'json',
								success: function(response){
										for(var j=0; j<response.length; j++){
											var data = response[j];
											var html = '';
											html += '<div>';
											html += '<div class="result_content">'+data.result_content+'</div>';
											html += '<input class="result_longitude" type="hidden" value="'+data.longitude+'" />';
											html += '<input class="result_latitude" type="hidden" value="'+data.latitude+'" />';
											html += '<a class="result_link" href="<?php echo site_url($cms["module_path"].'/index/'.$map["map_id"]);?>/'+											
												data.longitude+'/'+data.latitude+'">Go To Location</a>';
											html += '<div>';
											$('#gis_search_result').append(html);
										}
										
									}
							});
						}
					}	
				}
			}); // end of search button click

			// result_link
			$('a.result_link').live('click',function(){
				var longitude = $(this).parent().children('.result_longitude').val();
				var latitude = $(this).parent().children('.result_latitude').val();
				var newLocation = new L.LatLng(latitude, longitude);
		        // teleport
		        map.setView(newLocation, 20);
				return false;
			});

			// refresh the features
			map.on('dragend', fetchLayer);
			map.on('zoomend', fetchLayer);


			function fetchLayer(){
				var map_zoom = map.getZoom();
				var bounds = map.getBounds();				
				var southWest = bounds.getSouthWest();
				var northEast = bounds.getNorthEast();
				var map_region = 'POLYGON(('+
					northEast.lng+' '+northEast.lat+', '+
					northEast.lng+' '+southWest.lat+', '+
					southWest.lng+' '+southWest.lat+', '+
					southWest.lng+' '+northEast.lat+', '+
					northEast.lng+' '+northEast.lat+'))';

				// delete layers from groups				
				for(var i=0; i<map_layer_groups.length; i++){
					group = map_layer_groups[i];
					group_name = group['name'];				
					layer_groups[group_name].clearLayers();
				}
				// add layers to the groups
				for(var i=0; i<map_layers.length; i++){
					var layer = map_layers[i];
					var group_name = layer["group_name"];
					var json_url = layer["json_url"];	
					

					// get geoJSON from the server
					$.ajax({
						//async: false,
						parse_data: {
							layer: layer, 
							group_name: group_name},
						url : json_url,
						type : 'POST',
						data : {
							map_region: map_region,
							map_zoom: map_zoom},
						dataType : 'json',
						error : function(response, textStatus, errorThrown){
								$("#message").append("Failed to load <b>"+
										this.parse_data.layer["layer_name"]+"</b> layer with status <b>"+
										textStatus+'</b>, '+errorThrown+'<br />');
							},
						success : function(response){
								layer = this.parse_data.layer;
								group_name = this.parse_data.group_name;
								geojson_feature = response;	
								// make geojson layer
								var point_config = null;							
								var style = null;
								if(geojson_feature['features'].length>0){
									var feature_type = geojson_feature['features'][0]['geometry']['type'];
									var is_point = (feature_type=='Point');
									// style
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
											point_config = {
													pointToLayer: function (latlng){
												        return new L.Marker(latlng, {
												            icon: new L.Icon({
													            	iconUrl: image_url,
																	shadowUrl: null,
																	iconSize: new L.Point(20,20),//(32, 37),
																	shadowSize: null,
																	iconAnchor: new L.Point(14, 20),
																	popupAnchor: new L.Point(2, -20)
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

									var geojson_layer = null;
									if(is_point){
										geojson_layer = new L.GeoJSON(geojson_feature, point_config	);
									}else{
										geojson_layer = new L.GeoJSON(geojson_feature);
									}
									
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
	
									// add geojson layer to layer_groups	
									layer_groups[group_name].addLayer(geojson_layer);
	
									// label for point feature
									if(is_point){
										// for each point, we should make a more elegant way
										for(var i=0; i<geojson_feature['features'].length; i++){
											var geojson_single_feature = {
													"type":"FeatureCollection",
													"features":[geojson_feature['features'][i]]
											}
											var label = geojson_feature['features'][i]['properties']['label']
											var point_config = {
													pointToLayer: function (latlng){
												        return new L.Marker(latlng,{
												            icon: new L.Icon.Text(label,{})
												        });
												    }																			
												};
											var geojson_label = new L.GeoJSON(geojson_single_feature, point_config	);
											geojson_label.on("featureparse", function (e) {
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
											
											geojson_label.addGeoJSON(geojson_single_feature);
			
											// add geojson_label to layer_group
											layer_groups[group_name].addLayer(geojson_label);
										}
									}

									// TODO: make a better approach
									var child = $('svg>g>path[d*="L"]');
									child.remove();
									$('svg').prepend(child);

									
								}
												
							}
					});

					
				}// end of AJAX call
			}// end of function fetchLayer
			
		
		});
	</script>
</head>
<body>
	<div id="map" style="height: <?php echo $map["height"]; ?>; width: <?php echo $map["width"]; ?>"></div>
	<div id="message"></div>
	<?php 
		$html = "";
		// make the checkboxes
		$need_search_form = FALSE;
		$layers = $map["layers"];
		for($i=0; $i<count($layers); $i++){	
			$layer = $layers[$i];			
			$layer_searchable = $layer["searchable"]>0;
			if($layer_searchable){
				$need_search_form = TRUE;
				$layer_name = $layer['layer_name'];
				$html.= '<label class=".checkbox"> 
					<input id="gis_search_layer_'.$i.'" name="options" type="checkbox" value="'.$layer_name.'" checked />'.
					$layer_name.'</label>&nbsp;&nbsp;';
			}
		}
		// make the search form if needed
		if($need_search_form){
			$html.= '<div class="well form-inline">
				<input id="gis_search_keyword" type="text" />
				<input id="btn_gis_search" type="button" value="Search On The Map" />
			</div>';	
		}
		if($html != ""){
			echo '<div class="well form-inline">'.$html.'</div>';
			echo '<div id="gis_search_result"></div>';
		}
	?>
</body>