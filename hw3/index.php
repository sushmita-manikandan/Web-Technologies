<?php


include 'geoHash.php';

$keyword=$_POST["keyword"];
$category=$_POST["category"];
$distance=$_POST["distance"];
$orig_latitude=$_POST["orig_latitude"];

$orig_longitude=$_POST["orig_longitude"];

$location=rawurlencode($_POST["location"]);
$dapi_key='**********';

$googleapi_key='**********';

switch ($category) {
  case "music":
    $segmentId="KZFzniwnSyZfZ7v7nJ";
    break;
  
  case "sports":
    $segmentId="KZFzniwnSyZfZ7v7nE";
    break;

  case "art":
    $segmentId="KZFzniwnSyZfZ7v7na";
    break;
  
  case "film":
    $segmentId="KZFzniwnSyZfZ7v7nn";
    break;
  
  case "miscellaneous":
    $segmentId="KZFzniwnSyZfZ7v7n1";
    break;
  
  default:
    $segmentId=""
    break;
}

if($location == "here")
{
    $custom_lat = $orig_latitude;
    $custom_long = $orig_longitude;
    
}
else
{
    $jsonObj = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$location."&key=".$google_api_key);

    $current_location = json_decode($jsonObj,true);
    $custom_lat = $current_location['results'][0]['geometry']['location']['lat'];
    $custom_long = $current_location['results'][0]['geometry']['location']['lng'];
}
$hash=encode($custom_lat,$custom_long);

$ticketMasterURL = 'https://app.ticketmaster.com/discovery/v2/events.json?apikey='.$discovery_api_key.'&keyword='.$Keyword.'&segmentId='.$segmentId.'&radius='.$Distance.'&unit=miles&geoPoint='.$hash;

$json_events_file = file_get_contents($ticketMasterURL);

$obj_json_events_file = json_decode($json_events_file,true);

for($i=0;$i<count($obj_json_events_file["_embedded"]["events"]);$i++)
{
    $id=$obj_json_events_file["_embedded"]["events"][$i]["id"]; 
    $get_keyword[$i]=$obj_json_events_file["_embedded"]["events"][$i]["_embedded"]["venues"][0]["name"];
    $event_url[$i]='https://app.ticketmaster.com/discovery/v2/events/'.$id.'?apikey='.$discovery_api_key.'&';
    $venue_url[$i]='https://app.ticketmaster.com/discovery/v2/venues?apikey='.$discovery_api_key.'&keyword='.rawurlencode($get_keyword[$i]);
    $get_event_url_contents[$i]=file_get_contents($event_url[$i]);
    $get_event_details[$i]=json_decode($get_event_url_contents[$i],true);
    $get_venue_url_contents[$i]=file_get_contents($venue_url[$i]);
    $get_venue_details[$i]=json_decode($get_venue_url_contents[$i],true);
}

?>

<html>
<head>
<title>Events Search</title>

<link rel="stylesheet" type="text/css" href="style.css">

<body>
<br>
<br>
<div class="form_styling">
<div class="form_heading_styling"> 
<h1><i>Events Search</i></h1> 
</div>
<form method="post" action="<?= $_SERVER['PHP_SELF']; ?>"  id="form_search">

<b>Keyword</b><input type="text" name="keyword" id="keyword" required > 
<br>
<b>Category</b>
<select name="category" id="category">
<option value="default" selected>Default</option>
<option value="music">Music</option>
<option value="sports">Sports</option>
<option value="art">Arts & Theatre</option>
<option value="film">Film</option>
<option value="miscellaneous">Miscellaneous</option>           
</select>
<br>
<b>Distance (miles)</b><input type="text" name="distance" placeholder="10" id="distance"><b>from</b>
<input type="radio" name="location" value="here" onclick="here()" id="here_button" checked>Here <br>
<input type="radio" name="location" value="not_here" onclick="not_here()" id="location_textbox" >
<input type="text" name="location" placeholder="location" id="location_text" required="required" >
<br>
<input type="submit" value="Search" id="submit_button" ><input type="button" value="Clear" onclick="clearAllValues()">
<input type="text" name="orig_latitude" id="orig_latitude" style="display:none"><input type="text" name="orig_longitude" id="orig_longitude" style="display:none">
</form>
</div>
<div>
<p id="tables"></p>
</div>
<p id="details"></p>
<p id="show_venue_info"></p>
<p id="venue_info"></p>
<p id="show_venue_photos"></p>
<p id="photo"></p>


<script type="text/javascript">
var xmlhttp;
document.getElementById("submit_button").disabled = true;
if (window.XMLHttpRequest)
{// code for IE7+, Firefox, Chrome, Opera, Safari
   xmlhttp=new XMLHttpRequest();
} 
else 
{// code for IE6, IE5
	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); 
}
	xmlhttp.open("GET", "http://ip-api.com/json", false);
    xmlhttp.send();
	
if (xmlhttp.readyState == 4 && xmlhttp.status==200 )
{
    var jsonObj = JSON.parse(xmlhttp.responseText);
    document.getElementById("orig_longitude").value = jsonObj.lon;
    document.getElementById("orig_latitude").value = jsonObj.lat;
    document.getElementById("submit_button").disabled = false;
}
<?php
if(isset($_POST['category'])):
?>
    document.getElementById('category').value = "<?php echo $_POST['category'];?>";
    localStorage.setItem('category',document.getElementById("category").value);
<?php
endif;
?>
<?php
if(isset($_POST['keyword'])):
?>
    document.getElementById("keyword").value = "<?php echo htmlentities($_POST['keyword']); ?>";
    localStorage.setItem('keyword',document.getElementById("keyword").value);
<?php
endif;
?>
<?php
if(isset($_POST['location'])&&$_POST['location']!="here"):
?>
    document.getElementById("location_text").value = "<?php echo htmlentities($_POST['location']); ?>";
    localStorage.setItem('location_text',document.getElementById("location_text").value);
<?php
endif;
?>
    
<?php
if(!isset($_POST['location'])):
?>
    document.getElementById("here_button").checked = true;
<?php
endif;
?>
<?php
if(isset($_POST['location']) && $_POST['location'] == 'here'):
?>
    document.getElementById("here_button").checked = true;
    localStorage.setItem('here_button',document.getElementById("here_button").value);
    localStorage.setItem('location_textbox','');
<?php
endif;
?>
   
<?php
if(isset($_POST['location']) && $_POST['location'] != 'here'):
?>
    document.getElementById("location_textbox").checked = true;
    localStorage.setItem('location_textbox',document.getElementById("location_textbox").value);
    localStorage.setItem('here_button','');
<?php
endif;
?>
    
<?php
if(isset($_POST['distance'])):
?>
    document.getElementById('distance').value = "<?php echo $_POST['distance'];?>";
    localStorage.setItem('distance',document.getElementById("distance").value);
<?php
endif;
?>
  
json_obj=<?php echo json_encode($obj_json_events_file) ?>;
json_get_event_details=<?php echo json_encode($get_event_details) ?>;
json_get_venue_details=<?php echo json_encode($get_venue_details) ?>;
latt = <?php echo json_encode($custom_lat) ?>;
logg = <?php echo json_encode($custom_long)?>;
if(document.getElementById("here_button").checked)
{
   document.getElementById("location_text").disabled = true;
}
if(document.getElementById("location_textbox").checked)
{
   document.getElementById("location_text").disabled = false;
}
function here() 
{
   document.getElementById("location_text").disabled = true;
   document.getElementById("location_textbox").checked = false;
}
function not_here()
{
   document.getElementById("location_text").disabled = false;
   document.getElementById("here_button").checked = false;
}
<!-- Displays search table------------------------------------------------------------------------------------------------------------------------ -->
function Event_Search_table()
{
html_text = "<table border='1'>";
html_text+= "<tbody>";
html_text+= "<tr>";
html_text+= "<th style='width:85px'>Date</th>";
html_text+= "<th style='width:75px'>Icon</th>";
html_text+= "<th>Event</th>";
html_text+= "<th>Genre</th>";
html_text+= "<th style='width:300px'>Venue</th>";
html_text+= "</tr>";
		
for(var i=0;i<json_obj._embedded.events.length;i++)
{
		id=json_obj._embedded.events[i].id;
		if(typeof(json_obj._embedded.events[i].dates.start.localDate)=='undefined') 
		html_text+="";
		else
		html_text+= "<td>"+json_obj._embedded.events[i].dates.start.localDate+ "<br>";
		if(typeof(json_obj._embedded.events[i].dates.start.localTime)=='undefined')
		html_text+=" ";
		else html_text+=json_obj._embedded.events[i].dates.start.localTime+"</td>";
		if(typeof(json_obj._embedded.events[i].images[0].url)=='undefined') html_text+="<td>N/A</td>";
		else
		html_text+= "<td><center><img class='img_class_2' src='"+json_obj._embedded.events[i].images[0].url+"'height=40px/></center></td>";
        if(typeof(json_obj._embedded.events[i].name)=='undefined' ||json_obj._embedded.events[i].name=='undefined'||json_obj._embedded.events[i].name=='Undefined' ) html_text+="<td>"+" "+"N/A</td>";	
		else html_text+= "<td><a onclick='loadTable("+i+")'>"+json_obj._embedded.events[i].name+"</a></td>";
		if( typeof(json_obj._embedded.events[i].classifications)=='undefined'|| typeof(json_obj._embedded.events[i].classifications[0].segment.name)=='undefined'||json_obj._embedded.events[i].classifications[0].segment.name=='undefined'|| json_obj._embedded.events[i].classifications[0].segment.name=='Undefined' ) html_text+="<td>"+" "+"N/A</td>";
        else html_text+="<td>"+json_obj._embedded.events[i].classifications[0].segment.name+"</td>";
	//.venues[0].location.latitude && json_get_venue_details[i]._embedded.venues[0].location.longitude && json_get_venue_details[i]._embedded.venues[0].name)&& json_get_venue_details[i]._embedded.venues[0].location&&json_get_venue_details[i]._embedded.venues[0].location.latitude&&json_get_venue_details[i]._embedded.venues[0].location.longitude
		if(json_get_venue_details[i]==null || typeof(json_get_venue_details[i]._embedded)=='undefined' || typeof(json_get_venue_details[i]._embedded.venues[0].location)=='undefined' || typeof(json_get_venue_details[i]._embedded.venues)=='undefined' || json_get_venue_details[i]._embedded.venues[0].name=='undefined' || json_get_venue_details[i]._embedded.venues[0].name=='Undefined' ||typeof(json_get_venue_details[i]._embedded.venues[0].name)=='undefined')html_text+="<td>N/A</td>";
		else 
		{
		html_text+="<td><a onclick = 'initMap("+i+","+json_get_venue_details[i]._embedded.venues[0].location.latitude+","+json_get_venue_details[i]._embedded.venues[0].location.longitude+")'>"+json_get_venue_details[i]._embedded.venues[0].name+"</a>";
		html_text+="<div id='Show_map"+i+"' style='display:none;height:280px;width:300px;position:absolute;down:200px;'><div onclick='initMap_WALK("+i+")'  class='walk_there'><a class='cursor_style'>Walk there</a></div>";
        html_text+= "<div class='bike_there' onclick='initMap_BIKE("+i+")'><a class='cursor_style'>Bike there</a></div><div class='drive_there' onclick='initMap_DRIVE("+i+")' ><a class='cursor_style'>Drive there</a></div><div id='map"+i+"' style='z-index=5;height:280px;width:300px;'></div></div></td>";
		}
		 
			
		
        html_text+= "</tr>";
        
        
        document.getElementById("tables").innerHTML = html_text;
		html_text += "</tbody>";
}
		html_text += "</table>";
}
	
<!-- ------------------------------------------------------------------------------------------------------------------------------------------------------- -->
 
<!-- ---------------------------------------------------------------------INIT MAP -------------------------------------------------------------------------- -->
 
function initMap(p,lt,lg) 
{
	loc = {lat: Number(lt), lng: Number(lg)};
	loc_here = {lat: Number(latt), lng: Number(logg)};
    var map_here = 'Show_map'+p;
    var map_position = 'map'+p;
    if(document.getElementById(map_here).style.display ==="none")
	{
       document.getElementById(map_here).style.display = "block";
       var map = new google.maps.Map(document.getElementById(map_position), {zoom: 13,center: loc});
	   var marker_here = new google.maps.Marker({position: loc,map: map});
    }
	else
	{
       document.getElementById(map_here).style.display = "none";
    }
}
function initMap_WALK(n) 
{
    var directions_display = new google.maps.DirectionsRenderer;
    var directions_service = new google.maps.DirectionsService;
    var m = 'map'+n;
    var map = new google.maps.Map(document.getElementById(m), {zoom: 13,center: loc_here});
    directions_display.setMap(map);
	var selectedMode = 'WALKING';
    directions_service.route({origin: loc_here,destination: loc,travelMode: google.maps.TravelMode[selectedMode]}, 
	function(response, status) 
	{
      if (status == 'OK') 
	  {
          directions_display.setDirections(response);
      } 
	  else 
	  {
         window.alert('Directions request failed due to the current status' + status);
      }
    });
}
function initMap_BIKE(n) 
{
    var directions_display = new google.maps.DirectionsRenderer;
    var directions_service = new google.maps.DirectionsService;
    var m = 'map'+n;
    var map = new google.maps.Map(document.getElementById(m), {zoom: 13,center: loc_here});
    directions_display.setMap(map);
	var selectedMode = 'BICYCLING';
    directions_service.route({origin: loc_here,destination: loc,travelMode: google.maps.TravelMode[selectedMode]}, 
	function(response, status) 
	{
      if (status == 'OK') 
	  {
          directions_display.setDirections(response);
      } 
	  else 
	  {
         window.alert('Directions request failed due to the current status' + status);
      }
    });
}
function initMap_DRIVE(n) 
{
    var directions_display = new google.maps.DirectionsRenderer;
    var directions_service = new google.maps.DirectionsService;
    var m = 'map'+n;
    var map = new google.maps.Map(document.getElementById(m), {zoom: 13,center: loc_here});
    directions_display.setMap(map);
	var selectedMode = 'DRIVING';
    directions_service.route({origin: loc_here,destination: loc,travelMode: google.maps.TravelMode[selectedMode]}, 
	function(response, status) 
	{
      if (status == 'OK') 
	  {
          directions_display.setDirections(response);
      } 
	  else 
	  {
         window.alert('Directions request failed due to the current status' + status);
      }
    });
}
<!-- --------------------------------------------------------------END OF INITMAPS------------------------------------------------------- -->	
<!-- -------------------------------------------------------------NO RECORDS FOUND TABLE-------------------------------------------------- -->
function generateEmptyTable()
{
	empty_text = "<table border='1' class='no_results_found'>";
    empty_text+= "<tbody><tr>";
    empty_text+= "<th>No Records have been found</th>";	
    empty_text+= "</tr></tbody></table>";
	document.getElementById("tables").innerHTML = empty_text;
}
<!-- ---------------------------------------------------------------------------------------------------------------------------------------------- -->	
<!-- ----------------------------- CHECKING FOR CONDITIONS----------------------------------------------------------------------------------------- -->	
<?php
if($_SERVER['REQUEST_METHOD'] == "POST" && !empty($obj_json_events_file['_embedded'])):
?>
    
	Event_Search_table();
	
  
<?php
endif;
?>
<?php
if($_SERVER['REQUEST_METHOD'] == "POST" && empty($obj_json_events_file['_embedded'])):
?>
generateEmptyTable();
    
<?php
endif;
?>


function clearAllValues() 
{
	document.getElementById("tables").innerHTML = "";
    document.getElementById('form_search').reset();
    document.getElementById('category').value = "default";
    document.getElementById('here_button').checked = true;
    document.getElementById('location_textbox').checked = false;
    document.getElementById('location_text').placeholder = "location";
    document.getElementById('location_text').value = "";
    document.getElementById('location_text').disabled = true;
    document.getElementById('distance').value = "";
    document.getElementById('distance').placeholder = "10";
    document.getElementById('details').innerHTML = "";
    document.getElementById('show_venue_info').innerHTML = "";
    document.getElementById("venue_info").innerHTML = "";
    document.getElementById('show_venue_photos').innerHTML = "";
	document.getElementById('photo').innerHTML="";
}
<!-- -------------------------------------------------------------------------------------------------------------------------------------- -->
<!-- function to display all the event and venue related information------------------------------------------------------------------------ -->
function loadTable(s)
{
		if(json_get_venue_details[s] !=null&&typeof(json_get_venue_details[s])!=='undefined' && typeof(json_get_venue_details[s]._embedded)!=='undefined' &&  typeof(json_get_venue_details[s]._embedded.venues)!=='undefined'&& typeof(json_get_venue_details[s]._embedded.venues[0].location)!=='undefined'&&typeof(json_get_venue_details[s]._embedded.venues[0].location.latitude)!=='undefined'&&typeof(json_get_venue_details[s]._embedded.venues[0].location.longitude)!=='undefined')
		{
		l1=json_get_venue_details[s]._embedded.venues[0].location.latitude;
		l2=json_get_venue_details[s]._embedded.venues[0].location.longitude;
		}
		detailText="<center>";
		if(json_get_event_details[s]!=null)
		{
		if(typeof(json_get_event_details[s])=='undefined' || typeof(json_get_event_details[s].name)=='undefined' || json_get_event_details[s].name=='undefined' ||json_get_event_details[s].name=='Undefined' )detailText+="";
		else
		detailText+="<h2><b>"+json_get_event_details[s].name+"</h2></b></center><br>";
		
		if(typeof(json_get_event_details[s])=='undefined' || typeof(json_get_event_details[s].seatmap)=='undefined'|| typeof(json_get_event_details[s].seatmap.staticUrl)=='undefined' || json_get_event_details[s].seatmap.staticUrl=='undefined'||json_get_event_details[s].seatmap.staticUrl=='Undefined') detailText+="<center>";
		else
		detailText+="<img align='right' src='"+json_get_event_details[s].seatmap.staticUrl+"' height='300' width='400'/><br><br>";
		if(typeof(json_get_event_details[s])=='undefined' || typeof(json_get_event_details[s].dates.start)=='undefined') detailText+= "";
		else
		
		detailText+="<font size='5'><b>Date</b></font><br>";
		if (typeof(json_get_event_details[s])=='undefined' || typeof(json_get_event_details[s].dates.start.localTime)=='undefined' || json_get_event_details[s].dates.start.localTime=='undefined'||json_get_event_details[s].dates.start.localTime=='Undefined') detailText+=json_get_event_details[s].dates.start.localDate;
		else	
		detailText+= json_get_event_details[s].dates.start.localDate+" "+json_get_event_details[s].dates.start.localTime;
		
		
		if (typeof(json_get_event_details[s])=='undefined' ||typeof(json_obj._embedded.events[s]._embedded)=='undefined' || typeof(json_obj._embedded.events[s]._embedded.attractions)=='undefined')detailText+=" <br>";
		
		else detailText+="<br><font size='5'><b>Artist/Team</b></font><br>";	
		
		if(typeof(json_obj._embedded.events[s]._embedded)=='undefined'||typeof(json_obj._embedded.events[s]._embedded.attractions)=='undefined' || typeof(json_obj._embedded.events[s]._embedded.attractions[0].name)=='undefined'||json_obj._embedded.events[s]._embedded.attractions[0].name=='Undefined') detailText+=" ";
			else detailText+="<a href='"+json_obj._embedded.events[s]._embedded.attractions[0].url+"' target='_blank'>"+json_obj._embedded.events[s]._embedded.attractions[0].name+"</a>"+" | ";
		
		if(typeof(json_obj._embedded.events[s]._embedded)=='undefined'|| typeof(json_obj._embedded.events[s]._embedded.attractions)=='undefined'||typeof(json_obj._embedded.events[s]._embedded.attractions[1])=='undefined'||typeof(json_obj._embedded.events[s]._embedded.attractions[1].name)=='undefined'||json_obj._embedded.events[s]._embedded.attractions[1].name=='undefined'||json_obj._embedded.events[s]._embedded.attractions[1].name=='Undefined') detailText+=" ";
			else detailText+="<a href='"+json_obj._embedded.events[s]._embedded.attractions[1].url+"' target='_blank'>"+json_obj._embedded.events[s]._embedded.attractions[1].name+"</a>";
		
		
        if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s]._embedded)=='undefined' || typeof(json_get_event_details[s]._embedded.venues[0].name)=='undefined'||json_get_event_details[s]._embedded.venues[0].name=='undefined'||json_get_event_details[s]._embedded.venues[0].name=='Undefined') detailText+="<br>";
		else
		detailText+="<br><font size='5'><b>Venue</b></font><br>"+json_get_event_details[s]._embedded.venues[0].name;
		if(typeof(json_get_event_details[s].classifications)=='undefined') detailText+=" ";
		else 
		{
		detailText+="<br><font size='5'><b>Genres</b></font><br>";
		if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].classifications[0].subGenre)=='undefined' || typeof(json_get_event_details[s].classifications[0].subGenre.name)=='undefined'||json_get_event_details[s].classifications[0].subGenre.name=='Undefined') detailText+="";
		else
		detailText+=json_get_event_details[s].classifications[0].subGenre.name+" | ";
		
		if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].classifications[0].genre)=='undefined' || typeof(json_get_event_details[s].classifications[0].genre.name)=='undefined'||json_get_event_details[s].classifications[0].genre.name=='Undefined' ) detailText+="";
		else detailText+=json_get_event_details[s].classifications[0].genre.name+" | ";
	
		if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].classifications[0].segment)=='undefined' || typeof(json_get_event_details[s].classifications[0].segment.name)=='undefined'||json_get_event_details[s].classifications[0].segment.name=='Undefined') detailText+="";
		else detailText+= json_get_event_details[s].classifications[0].segment.name+" | ";
		
        if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].classifications[0].subType)=='undefined' || typeof(json_get_event_details[s].classifications[0].subType.name)=='undefined'|| json_get_event_details[s].classifications[0].subType.name=='Undefined') detailText+="";
		else	detailText+=json_get_event_details[s].classifications[0].subType.name+" | ";
		
		if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].classifications[0].type)=='undefined' || typeof(json_get_event_details[s].classifications[0].type.name)=='undefined'||json_get_event_details[s].classifications[0].type.name=='Undefined' ) detailText+="<br>";
		else	detailText+=json_get_event_details[s].classifications[0].type.name;
	
		}
		if(typeof(json_get_event_details[s].priceRanges)=='undefined') detailText+= " ";
		else if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].priceRanges[0].min)=='undefined')
		{
		detailText+="<br><font size='5'><b>Price Ranges</b></font><br>";
		detailText+=json_get_event_details[s].priceRanges[0].max+" USD";
		}
		else if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].priceRanges[0].max)=='undefined')
		{
		detailText+="<br><font size='5'><b>Price Ranges</b></font><br>";
		detailText+=json_get_event_details[s].priceRanges[0].min+" USD";
		}
		else{
		detailText+="<br><font size='5'><b>Price Ranges</b></font><br>";
		detailText+=json_get_event_details[s].priceRanges[0].min+" - "+json_get_event_details[s].priceRanges[0].max+" USD";
		}
		if(typeof(json_get_event_details[s])=='undefined' ||typeof(json_get_event_details[s].dates.status.code)=='undefined') detailText+="";
		else
		detailText+="<br><font size='5'><b>Ticket Status</b></font><br>"+json_get_event_details[s].dates.status.code;
		if(typeof(json_get_event_details[s].url)=='undefined' || json_get_event_details[s].url=='undefined' || json_get_event_details[s].url=='Undefined') detailText+="";
		else
		{
		detailText+="<br><font size='5'><b>Buy Ticket At:</b></font><br>";
		detailText+="<a href='"+json_get_event_details[s].url+"'target='_blank'>TicketMaster</a><br>";
		}
		detailText+="</center>";
		}
		else
		{detailText+="Nothing to display as the link you clicked contains no data";}
	document.getElementById('details').innerHTML=detailText;
	document.getElementById('tables').innerHTML="";
		

	
	showVenueInfo = "<p style='color: #888585'>click to show venue info<br></p>";
    showVenueInfo += "<img src='http://csci571.com/hw/hw6/images/arrow_down.png' height='20px'>";
    document.getElementById("show_venue_info").innerHTML = showVenueInfo;
    document.getElementById("venue_info").style.display = "none";
    document.getElementById('show_venue_info').onclick = function () 
	{
        document.getElementById("photo").style.display = "none";
        if (document.getElementById("venue_info").style.display === "none") 
		{
            hideVenueInfo = "<p style='color: #888585'>click to hide venue info<br></p>";
            hideVenueInfo += "<img src='http://csci571.com/hw/hw6/images/arrow_up.png' height='20px'>";
            document.getElementById("show_venue_info").innerHTML = hideVenueInfo;
            venueText = "<table border='1'>";
            venueText+= "<tbody>";
			           
            if(json_get_venue_details[s]==null||typeof(json_get_venue_details[s])=='undefined' || typeof(json_get_venue_details[s]._embedded)=='undefined'||typeof(json_get_venue_details[s]._embedded.venues)=='undefined')
			{
            venueText+= "<tr>";
            venueText+= "<th id='no_venue_info'>No venue info found</th>";
            venueText+= "</tr>";
			}
		
			else
			{
            venueText+= "<tr>";
			 
            venueText+= "<td style='text-align:right'><b>Name</b></td>";
			if(typeof(json_get_venue_details[s]._embedded.venues[0].name)=='undefined'||json_get_venue_details[s]._embedded.venues[0].name=='undefined'||json_get_venue_details[s]._embedded.venues[0].name=='Undefined') venueText+="<td>N/A</td></tr>";
			else
			venueText+="<td style='text-align:center'>"+json_get_venue_details[s]._embedded.venues[0].name+"</td></tr>";
			venueText+="<tr><td style='text-align:right'><b>Map</b></td>";
			venueText+= "<td style='text-align:center'><center>"
			if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' ||typeof(json_get_venue_details[s]._embedded.venues[0].location)=='undefined'||typeof(json_get_venue_details[s]._embedded.venues[0].location.latitude)=='undefined'||typeof(json_get_venue_details[s]._embedded.venues[0].location.longitude)=='undefined') venueText+="<td>N/A</td></tr>";
			else
			venueText+= "<div id='Show_map"+s+"' style='height:280px;width:300px;position:absolute;down:200px;display:none'></div><div onclick='initMap_WALK("+s+")' class='walk1'><a class='cursor_style'>Walk there</a></div><div class='bike1' onclick='initMap_BIKE("+s+")'><a class='cursor_style'>Bike there</a></div><div class='drive1' onclick='initMap_DRIVE("+s+")' ><a class='cursor_style'>Drive there</a></div><div id='map"+s+"' style='height:280px;width:300px;z-index=5'></div></center></td></tr>";
			
			venueText+= "<tr><td style='text-align:right'><b>Address</b></td>";
			if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' ||typeof(json_get_venue_details[s]._embedded.venues[0].address)=='undefined'||json_get_venue_details[s]._embedded.venues[0].address.line1=='undefined'||json_get_venue_details[s]._embedded.venues[0].address.line1=='Undefined'||typeof(json_get_venue_details[s]._embedded.venues[0].address.line1)=='undefined' )venueText+="<td style='text-align:center'>N/A</td></tr>";
			else
			venueText+="<td style='text-align:center'>"+json_get_venue_details[s]._embedded.venues[0].address.line1+"</td></tr>";
		
			venueText+="<tr><td style='text-align:right'><b>City</b></td>";
			if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' ||typeof(json_get_venue_details[s]._embedded.venues[0].city)=='undefined' || typeof(json_get_venue_details[s]._embedded.venues[0].city.name)=='undefined')  venueText+="<td  style='text-align:center'>"+json_get_venue_details[s]._embedded.venues[0].state.stateCode+"</td></tr>";
			else if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' ||typeof(json_get_venue_details[s]._embedded.venues[0].state)=='undefined' || typeof(json_get_venue_details[s]._embedded.venues[0].state.stateCode)=='undefined') venueText+= "<td style='text-align:center'>"+json_get_venue_details[s]._embedded.venues[0].city.name+"</td></tr>";
			else if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' || typeof(json_get_venue_details[s]._embedded.venues[0].city)=='undefined'&& typeof(json_get_venue_details[s]._embedded.venues[0].city)=='undefined') venueText+="<td>N/A</td></tr>";
			else venueText+="<td style='text-align:center'>"+json_get_venue_details[s]._embedded.venues[0].city.name+","+json_get_venue_details[s]._embedded.venues[0].state.stateCode+"</td></tr>";
		
			venueText+="<tr><td style='text-align:right'><b>Postal Code</b></td>";
			if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' || typeof(json_get_venue_details[s]._embedded.venues[0].postalCode)=='undefined'||json_get_venue_details[s]._embedded.venues[0].postalCode=='undefined'||json_get_venue_details[s]._embedded.venues[0].postalCode=='Undefined') venueText+="<td>N/A</td></tr>";
			else
			venueText+="<td style='text-align:center'>"+json_get_venue_details[s]._embedded.venues[0].postalCode+"</td></tr>";
			
			venueText+="<tr><td style='text-align:right'><b>Upcoming Events</b></td>";
			if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' ||typeof(json_get_venue_details[s]._embedded.venues[0].url)=='undefined') venueText+="<td style='text-align:center'>"+json_get_venue_details[s]._embedded.venues[0].name+" "+"Tickets</td></tr>";
			else if(typeof(json_get_venue_details[s]._embedded.venues[0])=='undefined' ||typeof(json_get_venue_details[s]._embedded.venues[0].name)=='undefined') venueText+="<td style='text-align:center'>N/A</td></tr>";
			else
			venueText+="<td style='text-align:center'><a href='"+json_get_venue_details[s]._embedded.venues[0].url+"' target='_blank'>"+json_get_venue_details[s]._embedded.venues[0].name+" "+"Tickets</td></tr>";
			
			}
            
         
            venueText += "</tbody>";
            venueText += "</table>";
            document.getElementById("venue_info").innerHTML = venueText;
            document.getElementById("venue_info").style.display = "block";
        }
        else 
		{
            document.getElementById("venue_info").style.display = "none";
            document.getElementById("show_venue_info").innerHTML = showVenueInfo;
        }
        showPhoto = "<p style='color: #888585'>click to show venue photos<br></p>";
        showPhoto+= "<img src='http://csci571.com/hw/hw6/images/arrow_down.png' height='20px'>";
        document.getElementById("show_venue_photos").innerHTML = showPhoto;
		if(typeof(l1)!=='undefined' && typeof(l2)!=='undefined')
		document.getElementById("Show_map"+s).innerHTML=initMap(s,l1,l2);	 
	};
	
	showPhoto = "<p style='color: #888585'>click to show venue photos<br></p>";
    showPhoto+= "<img src='http://csci571.com/hw/hw6/images/arrow_down.png' height='20px'>";
    document.getElementById("show_venue_photos").innerHTML = showPhoto;
    document.getElementById("photo").style.display = "none";
	
    document.getElementById('show_venue_photos').onclick = function () 
	{
        document.getElementById("venue_info").style.display = "none";
        showVenueInfo = "<p style='color: #888585'>click to show venue info<br></p>";
        showVenueInfo += "<img src='http://csci571.com/hw/hw6/images/arrow_down.png' height='20px'>";
        document.getElementById("show_venue_info").innerHTML = showVenueInfo;
        if (document.getElementById("photo").style.display === "none") {
            hidePhotos = "<p style='color: #888585'>click to hide venue photos<br></p>";
            hidePhotos += "<img src='http://csci571.com/hw/hw6/images/arrow_up.png' height='20px'>";
            document.getElementById("show_venue_photos").innerHTML = hidePhotos;
            venuePhotos = "<table border='1' id='show_photo_table'>";
            venuePhotos += "<tbody >";
			if(json_get_venue_details[s]==null||typeof(json_get_venue_details[s]._embedded)=='undefined' || typeof(json_get_venue_details[s]._embedded.venues[0].images)=='undefined')
			{
			venuePhotos += "<tr>";
            venuePhotos += "<th id='no_venue_photos'>No Venue Photos Found</th>";
            venuePhotos += "</tr>";	
			}
			else
			{
			
			var x=json_get_venue_details[s]._embedded.venues[0].images.length;
			for(var k=0;k<x;k++)
            venuePhotos+="<tr><th><img class='img_class' src = '"+json_get_venue_details[s]._embedded.venues[0].images[k].url+" '/></th></tr>";
			}
			
           
            venuePhotos += "</tbody>";
            venuePhotos += "</table>";
            document.getElementById("photo").innerHTML = venuePhotos;
            document.getElementById("photo").style.display = "block";
        }
        else
		{
            document.getElementById("photo").style.display = "none";
            document.getElementById("show_venue_photos").innerHTML = showPhoto;
        }
    };
}


</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAmyiqlzeWLjsBUxhF3sYRWST4IXA4-7_Q">
</script>
</body>
</html>