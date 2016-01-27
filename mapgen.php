<?php
//Upload to server/////////////////////////////
if (isset($_POST["submit"]) ) {

   if (isset($_FILES["mapgen"])) {

            //if there was an error uploading the file
        if ($_FILES["mapgen"]["error"] > 0) {
            echo "Return Code: " . $_FILES["mapgen"]["error"] . "<br />";

        }
        else {
                 //Print file details
            $storagename = "rockford.csv";
            move_uploaded_file($_FILES["mapgen"]["tmp_name"], "./" . $storagename);            
        }
     } else {
             echo "No file selected <br />";
     }
}
///////////////////////////////////


$string = '<html>
<head>

    <title>Nick Map</title>

    <script> function getMarkers() { ';


function readCSV($csvFile){
	$file_handle = fopen($csvFile, 'r');
	while(!feof($file_handle)){
		$line_of_text[] = fgetcsv($file_handle, 1024);
	}
	fclose($file_handle);
	return $line_of_text;
}
$csvFile = 'rockford.csv';
$csv = readCSV($csvFile);
//print_r($csv);


for ($i = 1; $i < count($csv); $i ++)
{
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyAtAgvRVFf9XoYjCGqSvZoItpBeQIy8Q_8&address=' . urlencode($csv[$i][1]);
        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $result=curl_exec($ch);
        // Will dump a beauty json :3
        $result = json_decode($result, true);
        
        //var_dump($result);
        
        $lat = $result['results'][0]['geometry']['location']['lat'];

        $lng = $result['results'][0]['geometry']['location']['lng'];

        if ($lat != null && $lng != null)
	{  
           $string .= 'plotMarker(' . $lat . ', ' . $lng . ', "' . $csv[$i][10]. '", "' . $csv[$i][0] . '", "' . $csv[$i][1] . '", "' . $csv[$i][3] . '", "' . $csv[$i][5] . '", "' . $csv[$i][6] . '", "' . $csv[$i][7] . '", "' . $csv[$i][9] . '");';
        }
    
}

$string .= '} </script>

    <script src="http://maps.googleapis.com/maps/api/js"></script>
    
    <script>

        var map = null;
        var markerGroups = { "BusinessI": [], "BusinessII": [], "TechCollege": [], "HighSchool": [], "SchoolDistrict": []};
        var circleGroups = { "BusinessI": [], "BusinessII": [], "TechCollege": [], "HighSchool": [], "SchoolDistrict": []};
        function ShowTechGroup(type)
        {
           
            for(i = 0; i < markerGroups[type].length; i++)
            {
                var marker = markerGroups[type][i];
                if (!marker.getVisible())
                    marker.setVisible(true);
                else
                    marker.setVisible(false);
            }
 
            for(i = 0; i < circleGroups[type].length; i++)
            {
                var circle = circleGroups[type][i];
                if (!circle.getVisible())
                    circle.setVisible(true);
                else
                    circle.setVisible(false);
            }
          
        }
        
        function plotMarker (lat, lng, partner_type, company_name, address, employee_size, description, key_contacts, number, membership_level) {
            partner_type = partner_type.trim();
        
        var myCity = null;
     
        if (partner_type == "Tech College") {
            myCity = new google.maps.Circle({
            center:{lat : lat, lng : lng},
            radius:4000,
            strokeColor:"#FF0000",
            strokeOpacity:0.8,
            strokeWeight:2,
            fillColor:"#FF0000",
            fillOpacity:0.4,
            editable: true
            });

            myCity.setMap(map);
            circleGroups.TechCollege.push(myCity);

         }

         if (partner_type == "High School") {

            myCity = new google.maps.Circle({
            center:{lat : lat, lng : lng},
            radius:4000,
            strokeColor:"#FFFF00",
            strokeOpacity:0.8,
            strokeWeight:2,
            fillColor:"#FFFF00",
            fillOpacity:0.0,
            editable: true
            });

            myCity.setMap(map);
            circleGroups.HighSchool.push(myCity);

         }

        var pinColor = "FF00FF";

        switch (partner_type) {

            case "Business I" : pinColor = "0000FF";

            break;

            case "Business II" : pinColor = "800080";

            break;

            case "Tech College" : pinColor = "FF0000";

            break;

            case "High School" : pinColor = "FFFF00";

            break;

            case "School District" : pinColor = "FFA500";

            break;

        }

        var infowindow = new google.maps.InfoWindow({
            content: "<b>" + company_name + "</b><br><br>" + address + "<br><br># of Employees : " + employee_size + "<br><br>Description : " + description + "<br><br>Contacts : " + key_contacts + "<br><br>Phone #" + number + "<br><br>Membership Level : " + membership_level + "<br><br>Partner Type : " + partner_type,
        });

        
        var pinImage = new google.maps.MarkerImage("http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
        new google.maps.Size(21, 34),
        new google.maps.Point(0,0),
        new google.maps.Point(10, 34));
                var marker = new google.maps.Marker({
                  position: {lat : lat, lng : lng},
                  map: map,
                  icon: pinImage
                });
                if (partner_type == "Tech College")
                    markerGroups.TechCollege.push(marker);
                else if (partner_type == "High School")
                    markerGroups.HighSchool.push(marker);
                else if (partner_type == "Business I")
                    markerGroups.BusinessI.push(marker);
                else if (partner_type == "Business II")
                    markerGroups.BusinessII.push(marker);
                else if (partner_type == "School District")
                    markerGroups.SchoolDistrict.push(marker);
                
                marker.setMap(map);
                
                google.maps.event.addListener(marker,"click",function() {
                    infowindow.open(map,marker);
                });
            

        }

        function initialize() {
            var mapProp = {
                center:new google.maps.LatLng(42.2623522, -89.081763),
                zoom:11,
                mapTypeId:google.maps.MapTypeId.ROADMAP
            };
            map=new google.maps.Map(document.getElementById("googleMap"),mapProp);
            var searchBox = new google.maps.places.SearchBox(document.getElementById("pac-input"));
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(document.getElementById("pac-input"));
            google.maps.event.addListener(searchBox, "places_changed",function(){
                searchBox.set("map", null);
                var places = searchBox.getPlaces();
                var bounds = new google.maps.LatLngBounds();
                var i, place;
                for(i = 0; place = places[i]; i++){
                   (function(place){
                       var marker = new google.maps.Marker({
                           position: place.geometry.location
                       });
                       marker.bindTo("map", searchBox, "map");
                       google.maps.event.addListener(marker, "map_changed", function(){
                            if(!this.getMap()){
                                this.unbindAll();
                            }
                       });
                       bounds.extend(place.geometry.location);
                   }(place));
                }
                map.fitBounds(bounds);
                searchBox.set("map", map);
                map.setZoom(Math.min(map.getZoom(),12));
            });
            getMarkers();

        }

        google.maps.event.addDomListener(window, "load", initialize);
       
        </script>
        
</head>
<body>
<div style="width:100%; height:600px;" align="center">
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places"></script>
    <input id="pac-input" class="controls" type="text" placeholder="Search Box">
    <div id="googleMap" style="width:800px; height:600px;" ></div>
</div>
<div style="width:100%; height:100px;" align="center">
    <div style="width:800px; height:100px;" >
        <div style="float:left;"><input type="button" value="Turn on/off Tech College" onclick="ShowTechGroup(\'TechCollege\')"/></div>
        <div style="float:left;"><input type="button" value="Turn on/off High School" onclick="ShowTechGroup(\'HighSchool\')"/></div>
        <div style="float:left;"><input type="button" value="Turn on/off Business I" onclick="ShowTechGroup(\'BusinessI\')"/></div>
        <div style="float:left;"><input type="button" value="Turn on/off Business II" onclick="ShowTechGroup(\'BusinessII\')"/></div>
        <div style="float:left;"><input type="button" value="Turn on/off School District" onclick="ShowTechGroup(\'SchoolDistrict\')"/></div>
        <form action="mapgen.php" method="post" enctype="multipart/form-data">
		<div style="float:left;"><input type="file" name = "mapgen" id="mapgen"/></div>
		<div style="float:left;"><input type="submit" name="submit"/></div>
	</form>
    </div>
</div>
</body>
</html>

';

echo ($string);


?>	