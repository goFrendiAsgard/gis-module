FEATURE:
- Compatible with the newest No-CMS so far (06/30/2012)
- Can use several tile server as basemap:
    - Cloudmade
    - Map Box
    - Google Map
- Can handle multiple map
- Full control of each map
    - zoom
    - longitude & latitude
    - width & height
    - use/not use google satellite, google roadmap & google hybrid map
- Can have several layers in a map
- Full control of each layer
    - Use already well known geoJSON to parse vector
    - Programmable geoJSON layer, and also means custom popup content
    - Simple library to change MySQL spatial into geojson has been provided
    - shown/not shown in startup
- Change the center position of the map by using URL (e.g: http://localhost/No-CMS/gis/index/1/15/14)

HOWTO:
- Use this module?
    - Download No-CMS (https://github.com/goFrendiAsgard/No-CMS)
    - Install No-CMS on your server
    - Download this module
    - extract it on your No-CMS module directory
    - go to Module management, click install
    - go to gis link
- Change map center position by using URL:
    - http://your_domain.com/No-CMS_path/gis/index/map_id/longitude/latitude
- Using geoformat library to change mysql into geojson:
    - In any controller of any module, write a function like this:

            public function geojson(){
                // load geoformat library
                $this->load->library($this->cms_module_path('gofrendi.gis.core').'/geoformat');
                
                // the SQL to select your data from mySQL
                // it is important to perform astext(your_geometry_field)
                $SQL = "
                    SELECT `cat`, `name`, `use`, `elev`, astext(`shape`) as `shape` 
                    FROM gis_alaska_airport 
                    WHERE `use`<>'Military' AND `use`<>'Civilian/Public'";
                    
                // the geometry field of your query
                $shape_column = 'shape';
                
                // the popup content
                // everything with '@' prefix will be replaced by corresponding field
                $popup_content = '';
                $popup_content .= '<b>@name</b><br />';
                $popup_content .= '<p>';
                $popup_content .= ' Usage : @use<br />';
                $popup_content .= ' Elevation : @elev<br />';
                $popup_content .= '</p>';
                
                // label content
                $label_content = '@name';
                
                // call sql2json function
                echo $this->geoformat->sql2json($SQL, $shape_column, $popup_content, $label_content);
            }
- Do some logic to be viewed in popup content
    - nope, you can't do that, costumize your query for such a purpose
- Donate?
    - I have a paypal account, goFrendiAsgard@gmail.com. Please write a message along with your donation
