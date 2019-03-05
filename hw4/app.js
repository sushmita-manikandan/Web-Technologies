var express = require('express');
var app = express();
var http=require('http');
var https= require('https');
var url= require('url');
var request = require('request');
var geohash= require('ngeohash');
var SpotifyWebApi = require('spotify-web-api-node');
var port = process.env.PORT ||8081;

app.get('/',function(req,res){res.sendFile(__dirname + '/event_search.html');
});
app.use(express.static(__dirname));

app.get('/here_button', function (req, res) 
{
   res.setHeader("Content-Type","text/plain");
   res.setHeader("Access-Control-Allow-Origin","*");
   var params = url.parse(req.url, true).query;	
   var hashing=geohash.encode(params.lat1,params.lon1);
   var url_text='https://app.ticketmaster.com/discovery/v2/events.json?apikey=Rg4nFQmGAvlW4iANkIWnZyw5XpIXnY&keyword='+params.Keyword+'&segmentId='+params.segmentId+'&radius='+params.Distance+'&unit=miles&geoPoint='+hashing;
	//modified API key as it is sensitive information
	    https.get(url_text,function(req2,res2)
		{
        var res_text = "";
        req2.on('data',function(data)
		{
            res_text+=data;
			
        });
        req2.on('end',function()
		{
            return res.send(res_text);
        });

		});

});

app.get('/auto_complete', function (req, res) 
{
   res.setHeader("Content-Type","text/plain");
   res.setHeader("Access-Control-Allow-Origin","*");
   var params = url.parse(req.url, true).query;
   var url_text_auto='https://app.ticketmaster.com/discovery/v2/suggest?apikey=Rg4nFQmGAvlW4iANkIWnZiWyw5XpIXnY&keyword='+params.Keyword;

	    https.get(url_text_auto,function(req2,res2)
		{
        var res_text = "";
        req2.on('data',function(data)
		{
            res_text+=data;
			
        });
        req2.on('end',function()
		{
			return res.send(res_text);
        });

        });
   

});

app.get('/google_photos', function (req, res) 
{
   res.setHeader("Content-Type","text/plain");
   res.setHeader("Access-Control-Allow-Origin","*");
   var params = url.parse(req.url, true).query;
   var url_text_google='https://www.googleapis.com/customsearch/v1?q='+params.Keyword+'&cx=011572014524656636668:fuajcjgw75g&imgSize=medium&imgType=news&num=8&searchType=image&key=AIzaSyDV8_05oaa-iVMJ-_p2gOsr2Ymsw90EVBY';

   https.get(url_text_google,function(req2,res2)
   {
        var res_text = "";
        req2.on('data',function(data)
		{
            res_text+=data;
			
        });
        req2.on('end',function()
		{
            return res.send(res_text);
        });

    });
    

});
var spotifyApi = new SpotifyWebApi({
  clientId: '2e7740a8223c4ac0aa986e0b029b5414',
  clientSecret: '08c9b5841f0d494485897f42678a1456',
  redirectUri: 'http://www.example.com/callback'
});
app.get('/spotify',function(req, res)
{
    res.setHeader("Content-Type","text/plain");
    res.setHeader("Access-Control-Allow-Origin","*");
    var params = url.parse(req.url, true).query;
	var url_text_spotify= "https://api.spotify.com/v1/search?q="+params.Keyword+"type=artist";
	console.log( "https://api.spotify.com/v1/search?q="+params.Keyword+"type=artist");
	
	https.get(url_text_spotify,function(req2,res2)
	{
        var res_text = "";
        req2.on('data',function(data)
		{
            res_text+=data;
			
        });
        req2.on('end',function(){
            return res.send(res_text);
        });

    });
        
    spotifyApi.searchArtists(params.Keyword)
  .then(function(data) {
    console.log('Search artists by ', data.body);
  }, function(err) 
  {
    console.error(err);
  });        
               // Authorization: 'Bearer BQDA9j6B41WNrpvsKmI7L7O-V8gR_lOi2La_jIvHCPSbCNGiIyh65613Fobr6gZgXSTQ0YKuTypeUgpftdfEH-Jahge5DtHfv8a8tSYflTN8vTUrfGVp8mpoARIzko6-KzhHe-AZKxt0_8HVuHo3Cv3AOkitv4Y0qQ';
			
   
 });


app.get('/songkick', function (req, res) {
    res.setHeader("Content-Type","text/plain");
    res.setHeader("Access-Control-Allow-Origin","*");
    var params = url.parse(req.url, true).query;
	console.log(params.api);
	console.log(params.venue);
	
	
	
    var url_text='https://api.songkick.com/api/3.0/search/venues.json?query='+params.venue+'&apikey='+params.api;
	console.log(url_text);
	
        https.get(url_text,function(req2,res2){
        var res_text = "";
		var res_text_2="";
        req2.on('data',function(data){
			res_text += data;
			console.log('\n\n\n************************************************************************');
			console.log(res_text);
			console.log('************************************************************************\n\n\n\n');
            
			
			
			
			
		});
		
        req2.on('end',function(){
            if(JSON.parse(res_text).resultsPage.results!=undefined &&JSON.parse(res_text).resultsPage.results!=''&& JSON.parse(res_text).resultsPage.results.venue!=undefined && JSON.parse(res_text).resultsPage.results.venue[0].id!=undefined)
			{
				console.log("First Call**********************************************");
				//console.log(res_text);
				var url_text_song='https://api.songkick.com/api/3.0/venues/'+JSON.parse(res_text).resultsPage.results.venue[0].id+'/calendar.json?apikey='+params.api;
				https.get(url_text_song,function(req3,res3)
				{
					
					req3.on('data', function(data){
						res_text_2+=data;
						console.log("Second Call**************************************************");
						console.log(res_text_2);
						//return res.send(res_text_2);
						
					});					
					req3.on('end',function(){
						return res.send(res_text_2);
					});
				});
			}
			else {
				return res.send("");
			}
        });

    });


});


app.listen(8081);