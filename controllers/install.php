<?php
/**
 * Description of install
 *
 * @author theModuleGenerator
 */
class Install extends CMS_Module_Installer {
    protected $DEPENDENCIES = array();
    protected $NAME = 'gofrendi.gis.core';

    //this should be what happen when user install this module
    protected function do_install(){
        $this->remove_all();
        $this->build_all();
    }
    //this should be what happen when user uninstall this module
    protected function do_uninstall(){
        $this->remove_all();
    }
    
    private function remove_all(){
    	$this->db->query('DROP TABLE IF EXISTS `gis_cloudmade_basemap`;');
    	$this->db->query('DROP TABLE IF EXISTS `gis_layer`;');
    	$this->db->query('DROP TABLE IF EXISTS `gis_map`;'); 

    	//example layer
    	$this->db->query('DROP TABLE IF EXISTS `gis_alaska_airport`;');

        $this->remove_navigation("gis_map");        
        $this->remove_navigation("gis_cloudmade_basemap");
        $this->remove_navigation("gis_layer");
        $this->remove_navigation("gis_index");
    }
    
    private function build_all(){
    	$this->db->query("
    		CREATE TABLE IF NOT EXISTS `gis_map` (
			  `map_id` int(11) NOT NULL AUTO_INCREMENT,
			  `map_name` varchar(45) NOT NULL,
			  `map_desc` varchar(45) DEFAULT NULL,
			  `latitude` double NOT NULL DEFAULT '0',
			  `longitude` double NOT NULL DEFAULT '0',
			  `gmap_roadmap` tinyint(4) NOT NULL DEFAULT '1',
			  `gmap_satellite` tinyint(4) NOT NULL DEFAULT '1',
			  `gmap_hybrid` tinyint(4) NOT NULL DEFAULT '1',
			  `zoom` int(11) NOT NULL DEFAULT '5',
			  `height` varchar(45) NOT NULL DEFAULT '100%',
			  `width` varchar(45) NOT NULL DEFAULT '100%',
			  PRIMARY KEY (`map_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
    	");
    	
    	$this->db->query("
    		INSERT INTO `gis_map` (`map_id`, `map_name`, `map_desc`, `latitude`, `longitude`, `gmap_roadmap`, `gmap_satellite`, `gmap_hybrid`, `zoom`, `height`, `width`) VALUES
    			(1, 'Alaska', 'A map of Alaska', 60.293165, -158.959803, 1, 1, 1, 5, '500px', '100%'),
				(2, 'Sorong', 'Map of Sorong, Papua, Indonesia', -0.87863315453434, 131.2604984393, 0, 0, 1, 18, '500px', '100%');
    	");
    	
    	$this->db->query("
    		CREATE TABLE IF NOT EXISTS `gis_layer` (
			  `layer_id` int(11) NOT NULL AUTO_INCREMENT,
    		  `map_id` int(11) NOT NULL,
			  `layer_name` varchar(45) NOT NULL,
    		  `group_name` varchar(45) NOT NULL,
			  `layer_desc` varchar(45) DEFAULT NULL,
    		  `z_index` tinyint(4) NOT NULL DEFAULT '0',
    		  `shown` tinyint(4) NOT NULL DEFAULT '1',
			  `radius` int(11) NOT NULL DEFAULT '8',
			  `fill_color` varchar(45) NOT NULL DEFAULT '#FF7800',
			  `color` varchar(45) NOT NULL DEFAULT '#000000',
			  `weight` double NOT NULL DEFAULT '1',
			  `opacity` double NOT NULL DEFAULT '1',
			  `fill_opacity` double NOT NULL DEFAULT '0.8',
    		  `image_url` varchar(100) NULL,
    		  `json_sql` text NULL,
    		  `json_shape_column` varchar(100) NULL,
    		  `json_popup_content` text NULL,
    		  `json_label` text NULL,	
    		  `use_json_url` tinyint(4) NOT NULL DEFAULT '0',
			  `json_url` varchar(100) NULL,
    		  `searchable` tinyint(4) NOT NULL DEFAULT '0',			  
    		  `search_sql` text NULL,
			  `search_result_content` text NULL,
			  `search_result_x_column` varchar(100) NULL,
    		  `search_result_y_column` varchar(100) NULL,
    		  `use_search_url` tinyint(4) NOT NULL DEFAULT '0',				  
    		  `search_url` varchar(100) NULL,
			  PRIMARY KEY (`layer_id`),
			  KEY `map_id` (`map_id`),
    		  CONSTRAINT `gis_layer_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `gis_map` (`map_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
    	");
    	
    	$this->db->query("
	    	INSERT INTO `gis_layer` (`layer_id`, `map_id`, `layer_name`, `group_name`, `layer_desc`, `shown`, `radius`, `fill_color`, `color`, `weight`, `opacity`, `fill_opacity`, `image_url`, `json_sql`, `json_shape_column`, `json_popup_content`, `json_label`, `use_json_url`, `json_url`, `searchable`, `search_sql`, `search_result_content`, `search_result_x_column`, `search_result_y_column`, `use_search_url`, `search_url`) VALUES
				(1, 1, 'All Airports', '', 'Airports in all alaska', 1, 4, '#ff7800', '#000000', 1, 1, 0.8, NULL, NULL, NULL, NULL, NULL, 1, '@site_urlgis/alaska_airport/geojson', 1, 'SELECT `name`, `use`, `elev`, x(`shape`) as `x`, y(`shape`) as `y` \nFROM gis_alaska_airport\nWHERE name LIKE ''%@keyword%'';', '<b>@name</b><br />\nUsage : @use<br />\nElevation : @elev<br />\nCoordinate: (@x,@y)', 'x', 'y', 0, NULL),
				(2, 1, 'Civilian/Public', 'Airports by usage', 'Civilian/Public Airport', 0, 4, '#ff7800', '#000000', 1, 1, 0.8, '@base_url/modules/gis/assets/images/black_plane.png', 'SELECT `cat`, `name`, `use`, `elev`, astext(`shape`) as `shape`, x(`shape`) as `lat`, y(`shape`) as `long` \nFROM gis_alaska_airport \nWHERE `use`=''Civilian/Public'' AND (MBRIntersects(`shape`,geomfromtext(''@map_region''))=1) AND (@map_zoom > 3) ;', 'shape', '<b>Civilian Airport</b><br />Name: @name<br />Latitude: @lat<br />Longitude:@long', '@name', 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL),
				(3, 1, 'Military', 'Airports by usage', 'Military Airport', 0, 4, '#ff0000', '#000000', 1, 1, 0.8, NULL, 'SELECT `cat`, `name`, `use`, `elev`, astext(`shape`) as `shape`, x(`shape`) as `lat`, y(`shape`) as `long` \nFROM gis_alaska_airport \nWHERE `use`=''Military'' AND (MBRIntersects(`shape`,geomfromtext(''@map_region''))=1) AND (@map_zoom > 3) ;', 'shape', '<b>Military Airport</b><br />@name<br />Latitude: @lat<br />Longitude:@long', '@name', 0, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL);
    	");
    	
        $this->db->query("          
	        CREATE TABLE `gis_cloudmade_basemap` (
	          `basemap_id` int(11) NOT NULL AUTO_INCREMENT,
	          `map_id` int(11) NOT NULL,
	          `basemap_name` varchar(45) NOT NULL,
	          `url` varchar(100) NOT NULL,
	          `max_zoom` int(11) NOT NULL DEFAULT '18',
	          `attribution` varchar(45) NOT NULL,
	          PRIMARY KEY (`basemap_id`),
	          KEY `map_id` (`map_id`),
	    	  CONSTRAINT `gis_cloudmade_basemap_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `gis_map` (`map_id`)
	        ) ENGINE=InnoDB DEFAULT CHARSET=latin1
        ");        
        
        $this->db->query("
        	INSERT INTO `gis_cloudmade_basemap` (`basemap_id`, `map_id`, `basemap_name`, `url`, `max_zoom`, `attribution`) VALUES
				(1, 1, 'Open Street Map', 'http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/997/256/{z}/{x}/{y}.png', 18, 'Map data &copy; 2011 OpenStreetMap contributo'),
				(2, 1, 'Night View', 'http://{s}.tile.cloudmade.com/BC9A493B41014CAABB98F0471D759707/999/256/{z}/{x}/{y}.png', 18, 'Map data &copy; 2011 OpenStreetMap contributo'),
				(3, 1, 'Map Box', 'http://a.tiles.mapbox.com/v3/mapbox.world-bright/{z}/{x}/{y}.png', 18, '');
        ");
        
        // example layer
        $this->db->query("
        	CREATE TABLE `gis_alaska_airport` (
			  `OGR_FID` int(11) NOT NULL AUTO_INCREMENT,
			  `SHAPE` geometry NOT NULL,
			  `cat` decimal(10,0) DEFAULT NULL,
			  `na3` varchar(80) DEFAULT NULL,
			  `elev` double(32,3) DEFAULT NULL,
			  `f_code` varchar(80) DEFAULT NULL,
			  `iko` varchar(80) DEFAULT NULL,
			  `name` varchar(80) DEFAULT NULL,
			  `use` varchar(80) DEFAULT NULL,
			  UNIQUE KEY `OGR_FID` (`OGR_FID`),
			  SPATIAL KEY `SHAPE` (`SHAPE`)
			) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=latin1;
        ");
        
        $this->db->query("
        	INSERT INTO `gis_alaska_airport` (`OGR_FID`, `SHAPE`, `cat`, `na3`, `elev`, `f_code`, `iko`, `name`, `use`) VALUES
	        	( '1',geomfromtext('POINT(-162.97528075057514 67.56208038542596)'),'1','US00157','78.000','Airport/Airfield','PA','NOATAK','Other'),
				( '2',geomfromtext('POINT(-157.85360716963905 67.10610962051074)'),'2','US00229','264.000','Airport/Airfield','PA','AMBLER','Other'),
				( '3',geomfromtext('POINT(-151.52806090867935 66.9152832037312)'),'3','US00186','585.000','Airport/Airfield','PABT','BETTLES','Other'),
				( '4',geomfromtext('POINT(-162.59855650743486 66.88468170387709)'),'4','US59150','9.000','Airport/Airfield','PAOT','RALPH WIEN MEM','Civilian/Public'),
				( '5',geomfromtext('POINT(-159.9861907863258 66.6000289935081)'),'5','US00173','21.000','Airport/Airfield','PA','SELAWIK','Other'),
				( '6',geomfromtext('POINT(-153.70428466202193 65.99279022313692)'),'6','US00193','1113.000','Airport/Airfield','PA','INDIAN MOUNTAIN LRRS','Other'),
				( '7',geomfromtext('POINT(-161.15197752856332 65.98228454805457)'),'7','US00177','21.000','Airport/Airfield','PA','BUCKLAND','Other'),
				( '8',geomfromtext('POINT(-167.9225005954405 65.56416321178065)'),'8','US00146','243.000','Airport/Airfield','PATC','TIN CITY LRRS','Other'),
				( '9',geomfromtext('POINT(-161.2799987685303 65.40499878161447)'),'9','US00150','1329.000','Airport/Airfield','PA','GRANITE MOUNTAIN AFS','Other'),
				( '10',geomfromtext('POINT(-166.85852049347008 65.25366974237791)'),'10','US03057','9.000','Airport/Airfield','PA','PORT CLARENCE CGS','Other'),
				( '11',geomfromtext('POINT(-152.10939025351573 65.1743927010712)'),'11','US00188','207.000','Airport/Airfield','PATA','RALPH M CALHOUN','Other'),
				( '12',geomfromtext('POINT(-161.1580505262659 64.93444061520005)'),'12','US00155','108.000','Airport/Airfield','PA','KOYUK','Other'),
				( '13',geomfromtext('POINT(-156.9374999917138 64.73611450361221)'),'13','US75867','138.000','Airport/Airfield','PAGA','EDWARD G PITKA SR','Joint Military/Civilian'),
				( '14',geomfromtext('POINT(-162.05722044749686 64.69805908468658)'),'14','US60244','12.000','Airport/Airfield','PA','MOSES POINT','Other'),
				( '15',geomfromtext('POINT(-165.44525145114966 64.51219940534808)'),'15','US42171','33.000','Airport/Airfield','PAOM','NOME','Civilian/Public'),
				( '16',geomfromtext('POINT(-156.84333800436613 64.42444610765973)'),'16','US00211','1461.000','Airport/Airfield','PA','KALAKAKET CREEKAS','Military'),
				( '17',geomfromtext('POINT(-160.7989501843509 63.88836288711196)'),'17','US00436','18.000','Airport/Airfield','PAUN','UNALAKLEET','Other'),
				( '18',geomfromtext('POINT(-152.30067443268598 63.88055420029708)'),'18','US00327','624.000','Airport/Airfield','PA','MINCHUMINA','Other'),
				( '19',geomfromtext('POINT(-171.73283384441595 63.766777044102895)'),'19','US91222','24.000','Airport/Airfield','PA','GAMBELL','Other'),
				( '20',geomfromtext('POINT(-170.4926452465237 63.68638992827347)'),'20','US00453','48.000','Airport/Airfield','PA','SAVOONGA','Other'),
				( '21',geomfromtext('POINT(-155.60577391772972 62.95288849053581)'),'21','US80563','306.000','Airport/Airfield','PAMC','MC GRATH','Civilian/Public'),
				( '22',geomfromtext('POINT(-155.97639464502458 62.894443513808355)'),'22','US00342','858.000','Airport/Airfield','PATL','TATALINA LRRS','Other'),
				( '23',geomfromtext('POINT(-164.49110411231055 62.78527832409856)'),'23','US00466','12.000','Airport/Airfield','PA','EMMONAK','Other'),
				( '24',geomfromtext('POINT(-160.1898956188945 62.64858246126401)'),'24','US00455','282.000','Airport/Airfield','PA','ANVIK','Other'),
				( '25',geomfromtext('POINT(-150.09368895982536 62.320499421150416)'),'25','US95665','327.000','Airport/Airfield','PATK','TALKEETNA','Civilian/Public'),
				( '26',geomfromtext('POINT(-163.3022155630188 62.06055450808827)'),'26','US00458','282.000','Airport/Airfield','PA','ST MARYS','Other'),
				( '27',geomfromtext('POINT(-166.03889463833326 61.78027725674379)'),'27','US00440','417.000','Airport/Airfield','PACZ','CAPE ROMANZOF LRRS','Other'),
				( '28',geomfromtext('POINT(-159.5430450330111 61.58159637739974)'),'28','US00317','78.000','Airport/Airfield','PA','ANIAK','Other'),
				( '29',geomfromtext('POINT(-155.57472228140594 61.097221376640746)'),'29','US00338','1449.000','Airport/Airfield','PASV','SPARREVOHN LRRS','Other'),
				( '30',geomfromtext('POINT(-161.83799742387436 60.779777530536094)'),'30','US38091','111.000','Airport/Airfield','PABE','BETHEL','Civilian/Public'),
				( '31',geomfromtext('POINT(-151.24752806994613 60.571998597610154)'),'31','US45021','87.000','Airport/Airfield','PAEN','KENAI MUNI','Civilian/Public'),
				( '32',geomfromtext('POINT(-151.03825377804444 60.474983216730834)'),'32','US34970','96.000','Airport/Airfield','PA','SOLDOTNA','Other'),
				( '33',geomfromtext('POINT(-166.27061460841657 60.37141800437253)'),'33','US00447','42.000','Airport/Airfield','PA','MEKORYUK','Other'),
				( '34',geomfromtext('POINT(-145.25054931525864 66.57138824475342)'),'34','US00201','393.000','Airport/Airfield','PFYU','FORT YUKON','Other'),
				( '35',geomfromtext('POINT(-147.61444091508713 64.83750152629912)'),'35','US99779','408.000','Airport/Airfield','PAFB','WAINWRIGHT AAF','Military'),
				( '36',geomfromtext('POINT(-147.8596649139725 64.81366729780883)'),'36','US90129','396.000','Airport/Airfield','PAFA','FAIRBANKS INTL','Civilian/Public'),
				( '37',geomfromtext('POINT(-147.10139465065822 64.66555786171527)'),'37','US49463','501.000','Airport/Airfield','PAEI','EIELSON AFB','Military'),
				( '38',geomfromtext('POINT(-149.07350158315575 64.54897308409042)'),'38','US18668','330.000','Airport/Airfield','PA','NENANA MUNI','Other'),
				( '39',geomfromtext('POINT(-149.12014770121792 64.30120086732457)'),'39','US00191','504.000','Airport/Airfield','PACL','CLEAR','Other'),
				( '40',geomfromtext('POINT(-145.7216491677743 63.99454879792356)'),'40','US11435','1167.000','Airport/Airfield','PABI','ALLEN AAF','Military'),
				( '41',geomfromtext('POINT(-143.3355865467078 63.37435913102234)'),'41','US34092','1416.000','Airport/Airfield','PA','TANACROSS','Other'),
				( '42',geomfromtext('POINT(-141.92913818299675 62.961334228599284)'),'42','US33180','1569.000','Airport/Airfield','PAOR','NORTHWAY','Civilian/Public'),
				( '43',geomfromtext('POINT(-145.4566345189184 62.15488815351677)'),'43','US91368','1443.000','Airport/Airfield','PAGK','GULKANA','Civilian/Public'),
				( '44',geomfromtext('POINT(-149.08882140645085 61.59474182222864)'),'44','US33235','225.000','Airport/Airfield','PAAQ','PALMER MUNI','Civilian/Public'),
				( '45',geomfromtext('POINT(-149.81390380348776 61.53556823835532)'),'45','US00343','135.000','Airport/Airfield','PA','BIG LAKE','Other'),
				( '46',geomfromtext('POINT(-149.65469359841418 61.26250076400407)'),'46','US00341','345.000','Airport/Airfield','PAFR','BRYANT AHP','Military'),
				( '47',geomfromtext('POINT(-149.80650329070806 61.25136184801247)'),'47','US58704','192.000','Airport/Airfield','PAED','ELMENDORF AFB','Military'),
				( '48',geomfromtext('POINT(-149.84616088344762 61.21438980212513)'),'48','US01693','123.000','Airport/Airfield','PAMR','MERRILL FLD','Civilian/Public'),
				( '49',geomfromtext('POINT(-149.99618529741286 61.17432022207521)'),'49','US77679','129.000','Airport/Airfield','PANC','ANCHORAGE INTL','Civilian/Public'),
				( '50',geomfromtext('POINT(-146.24836730628428 61.133945465714156)'),'50','US96982','108.000','Airport/Airfield','PAVD','VALDEZ','Other'),
				( '51',geomfromtext('POINT(-145.4776458709465 60.49183273375414)'),'51','US40776','36.000','Airport/Airfield','PACV','MERLE K MUDHOLE SMITH','Other'),
				( '52',geomfromtext('POINT(-149.41880797807755 60.12693786739825)'),'52','US80341','18.000','Airport/Airfield','PA','SEWARD','Other'),
				( '53',geomfromtext('POINT(-154.91722106067155 59.75277710187726)'),'53','US00483','189.000','Airport/Airfield','PAIL','ILIAMNA','Other'),
				( '54',geomfromtext('POINT(-151.47657775214063 59.64555740517424)'),'54','US53682','75.000','Airport/Airfield','PAHO','HOMER','Other'),
				( '55',geomfromtext('POINT(-155.25721739823047 59.361667635479414)'),'55','US00488','606.000','Airport/Airfield','PA','BIG MOUNTAIN AFS','Military'),
				( '56',geomfromtext('POINT(-158.50334166369532 59.045413974218015)'),'56','US07889','78.000','Airport/Airfield','PADL','DILLINGHAM','Civilian/Public'),
				( '57',geomfromtext('POINT(-156.64916991180434 58.6766662626614)'),'57','US81498','51.000','Airport/Airfield','PAKN','KING SALMON','Joint Military/Civilian'),
				( '58',geomfromtext('POINT(-162.06056212029765 58.64722061593822)'),'58','US00476','492.000','Airport/Airfield','PAEH','CAPE NEWENHAM LRRS','Other'),
				( '59',geomfromtext('POINT(-152.49386595896283 57.7499732992624)'),'59','US22587','66.000','Airport/Airfield','PADQ','KODIAK','Joint Military/Civilian'),
				( '60',geomfromtext('POINT(-170.22044370642809 57.167331703287715)'),'60','US00475','57.000','Airport/Airfield','PASN','ST PAUL ISLAND','Other'),
				( '61',geomfromtext('POINT(-158.63182066685732 56.95943451310793)'),'61','US00482','78.000','Airport/Airfield','PA','PORT HEIDEN','Other'),
				( '62',geomfromtext('POINT(-169.66139219291665 56.578609474290204)'),'62','US00477','114.000','Airport/Airfield','PA','ST GEORGE','Other'),
				( '63',geomfromtext('POINT(-162.7242584078442 55.205600744120815)'),'63','US95048','87.000','Airport/Airfield','PACD','COLD BAY','Civilian/Public'),
				( '64',geomfromtext('POINT(-166.54350278976236 53.900138862403665)'),'64','US95921','18.000','Airport/Airfield','PADU','UNALASKA','Other'),
				( '65',geomfromtext('POINT(-168.85000608390814 52.94166565811451)'),'65','US00578','66.000','Airport/Airfield','PA','NIKOLSKI AS','Military'),
				( '66',geomfromtext('POINT(-174.20634458136723 52.22034836961871)'),'66','US76610','51.000','Airport/Airfield','PA','ATKA','Other'),
				( '67',geomfromtext('POINT(-139.66021728460072 59.503360748387685)'),'67','US47439','30.000','Airport/Airfield','PAYA','YAKUTAT','Civilian/Public'),
				( '68',geomfromtext('POINT(-135.31567382934887 59.4600563047468)'),'68','US97565','39.000','Airport/Airfield','PAGY','SKAGWAY','Other'),
				( '69',geomfromtext('POINT(-135.5222167979631 59.2452774046164)'),'69','US62749','12.000','Airport/Airfield','PA','HAINES','Other'),
				( '70',geomfromtext('POINT(-135.70750427326004 58.42444229112622)'),'70','US19342','30.000','Airport/Airfield','PA','GUSTAVUS','Other'),
				( '71',geomfromtext('POINT(-135.4096984871562 58.096084594586046)'),'71','US47648','18.000','Airport/Airfield','PA','HOONAH','Other'),
				( '72',geomfromtext('POINT(-133.90826416125603 56.960479736135404)'),'72','US94530','156.000','Airport/Airfield','PA','KAKE','Other'),
				( '73',geomfromtext('POINT(-132.94528198382665 56.80166625952452)'),'73','US14079','96.000','Airport/Airfield','PA','PETERSBURG JAMES A JOHNSON','Other'),
				( '74',geomfromtext('POINT(-132.36982727203377 56.48432540867274)'),'74','US38648','39.000','Airport/Airfield','PA','WRANGELL','Other'),
				( '75',geomfromtext('POINT(-133.07611084090223 55.57916641215566)'),'75','US05477','72.000','Airport/Airfield','PA','KLAWOCK','Other'),
				( '76',geomfromtext('POINT(-131.57223510887206 55.04243469211528)'),'76','US11438','108.000','Airport/Airfield','PANT','ANNETTE ISLAND','Other');
        		
        ");
        
        $original_directory = 'gis';
        $module_url = $this->cms_module_path();
        $module_main_controller_url = '';
        if($module_url != $original_directory){
        	$module_main_controller_url = $module_url.'/'.$original_directory;
        }else{
        	$module_main_controller_url = $module_url;
        }

        $this->add_navigation("gis_index", "Geographic Information System", $module_main_controller_url."/index", 1);
        $this->add_navigation("gis_map", "Map", $module_main_controller_url."/gis_map", 4, "gis_index");        
        $this->add_navigation("gis_cloudmade_basemap", "Cloudmade Base", $module_main_controller_url."/gis_cloudmade_basemap", 4, "gis_index");
        $this->add_navigation("gis_layer", "Layer", $module_main_controller_url."/gis_layer", 4, "gis_index");

    }
}

?>
