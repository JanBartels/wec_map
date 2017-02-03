//
// Copyright notice
//
// (c) 2005-2009 Christian Technology Ministries International Inc.
// All rights reserved
// (c) 2011-2017 Jan Bartels, j.bartels@arcor.de, Google API V3
//
// This file is part of the Web-Empowered Church (WEC)
// (http://WebEmpoweredChurch.org) ministry of Christian Technology Ministries
// International (http://CTMIinc.org). The WEC is developing TYPO3-based
// (http://typo3.org) free software for churches around the world. Our desire
// is to use the Internet to help offer new life through Jesus Christ. Please
// see http://WebEmpoweredChurch.org/Jesus.
//
// You can redistribute this file and/or modify it under the terms of the
// GNU General Public License as published by the Free Software Foundation;
// either version 2 of the License, or (at your option) any later version.
//
// The GNU General Public License can be found at
// http://www.gnu.org/copyleft/gpl.html.
//
// This file is distributed in the hope that it will be useful for ministry,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// This copyright notice MUST APPEAR in all copies of the file!
//

// Global object that holds all data about the markers, maps, icons etc
// and can be used multiple times on the page
// It serves as a dispatcher for the WecMapGoogleV3-instances

function createWecMap() {
return( {
	maps: [],

	// fetches the WecMapGoogleV3 object
	get: function(mapId) {
		return this.maps[mapId];
	},

	createMap: function(mapId) {
		this.maps[mapId] = new WecMapGoogleV3( mapId );
		return this.maps[mapId];
	},

	drawMap: function(mapId) {
		var map = this.get( mapId );
		map.drawMap( mapId );
	},

	setMapType: function( mapId, type ) {
		var map = this.get( mapId );
		return map.setMapTypeId( type );
	},

	addMapType: function( mapId, type ) {
		var map = this.get( mapId );
		return map.addMapType( type );
	},

	addControl: function( mapId, control ) {
		var map = this.get( mapId );
		return map.addControl( control );
	},

	addKML: function( mapId, kml ) {
		var map = this.get( mapId );
		return map.addKML( kml );
	},

	// adds markers (all markers from a specified group of a map) to the marker Manager
	addMarkersToManager: function(mapId, groupId, minZoom, maxZoom) {
		var map = this.get( mapId );
		map.addMarkersToManager( groupId, minZoom, maxZoom);
	},

	// jumps to a specific marker (determined by groupId and markerId) and zoomlevel on the map
	jumpTo: function(mapId, groupId, markerId, zoom) {
		var map = this.get( mapId );
		return map.jumpTo( groupId, markerId, zoom );
	},

	setCenter: function( mapId, $latlong, $zoom, $type) {
		var map = this.get( mapId );
		return map.setCenter( $latlong, $zoom, $type );
	},

	// adds an icon that might be used on a marker object later on
	addIcon: function(mapId, iconId, image, shadow, iconSize, shadowSize, iconAnchor, infoWindowAnchor) {
		var map = this.get( mapId );
		map.addIcon(iconId, image, shadow, iconSize, shadowSize, iconAnchor, infoWindowAnchor);
	},

	// adds the content (as an array for each tab) and the labels of the tabs, that will be used with the add Marker call
	addBubble: function(mapId, groupId, markerId, labels, content) {
		var map = this.get( mapId );
		map.addBubble(groupId, markerId, labels, content);
	},

	// adds a GMarker object with all the data for the bubble and the precise location. The marker is added via
	// the markermanager (see addMarkersToManagers), here it basically just created and added to the array
	addMarker: function(mapId, markerId, latlng, iconId, dirTitle, groupId, address) {
		var map = this.get( mapId );
		return map.addMarker( markerId, latlng, iconId, dirTitle, groupId, address);
	},

	setDraggable: function( mapId, groupId, markerId, flag ) {
		var map = this.get( mapId );
		map.setDraggable( groupId, markerId, flag );
	},

	openInfoWindow: function( mapId, groupId, markerId ) {
		var map = this.get( mapId );
		return map.openInfoWindow( groupId, markerId );
	},

	closeInfoWindow: function( mapId, groupId, markerId ) {
		var map = this.get( mapId );
		return map.closeInfoWindow( groupId, markerId );
	},

	openInitialInfoWindow: function( mapId, groupId, markerId ) {
		var map = this.get( mapId );
		return map.openInitialInfoWindow( groupId, markerId );
	},

	enableOverlappingMarkerManager: function( mapId, flag )	{
		var map = this.get( mapId );
		map.enableOverlappingMarkerManager( flag );

	},

	// loads directions on a map
	directionsService: null,

	createDirections: function(mapId, directionsDivId ) {
		var map = this.get( mapId );
		return map.createDirections( directionsDivId );
	},

	setDirections: function(mapId, fromAddr, toAddr, dirTitle, travelMode) {
		var map = this.get( mapId );
		return map.setDirections( fromAddr, toAddr, dirTitle, travelMode );
	},

	// opens up the directions tab window to a marker
	openDirectionsToHere: function(mapId, groupId, markerId) {
		var map = this.get( mapId );
		return map.openDirectionsToHere(groupId, markerId);
	},

	// opens up the directions tab window from a marker
	openDirectionsFromHere: function(mapId, groupId, markerId) {
		var map = this.get( mapId );
		return map.openDirectionsFromHere(groupId, markerId);
	},

	// resize and recenter map for use in hidden containers accordeon, modal box, etc.
	resizeMap: function(mapId) {
		var map = this.get( mapId );
		map.resizeMap();
	},

	labels: {
		startaddress: 'startaddress: ',
		endaddress:   'endaddress: ',
		OSM:          'OSM',
		OSM_alt:      'OpenStreetMap',
		OSM_Copyright: '<a href="http://www.openstreetmap.org/">&copy; OSM</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		OSM_bike:     'OSM-bike',
		OSM_bike_alt: 'OpenCycleMap',
		OSM_bike_Copyright: '<a href="http://www.opencyclemap.org/">&copy; OCM</a>, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
		locale:       'en'
	},

	osmMapType: null,
	osmCycleMapType: null,

	init: function() {
		if ( this.osmMapType == null )
		{
			this.osmMapType = new google.maps.ImageMapType({
				getTileUrl: function(coord, zoom) {
					return location.protocol + "//a.tile.openstreetmap.org/" +
					zoom + "/" + coord.x + "/" + coord.y + ".png";
				},
				tileSize: new google.maps.Size(256, 256),
				isPng: true,
				alt: WecMap.labels.OSM_alt,
				name: WecMap.labels.OSM,
				maxZoom: 18
			});
		}
		if ( this.osmCycleMapType == null )
		{
			this.osmCycleMapType = new google.maps.ImageMapType({
				getTileUrl: function(coord, zoom) {
					return location.protocol + "//a.tile.opencyclemap.org/cycle/" +
					zoom + "/" + coord.x + "/" + coord.y + ".png";
				},
				tileSize: new google.maps.Size(256, 256),
				isPng: true,
				alt: WecMap.labels.OSM_bike_alt,
				name: WecMap.labels.OSM_bike,
				maxZoom: 18
			});
		}

	},
	// Layer definitions for OSM and OSM-bike
	osmMapTypeId: 'OpenStreetMap',
	osmCycleMapTypeId: 'OpenCycleMap'
}
); }

// WecMapGoogleV3 is the central map-wrapper for each Google-map.on a page
// Its methods provide maximum compatibility to the old API.
function WecMapGoogleV3( mapId )
{
	this.mapId = mapId;
	this.Options = {
		zoom: 8,
		center: new google.maps.LatLng(51.2245379, 6.7918158),
		mapTypeControlOptions: {
			mapTypeIds: [google.maps.MapTypeId.ROADMAP]
		},
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		mapTypeControl: false,
		rotateControl: true,
		rotateControlOptions: { },
		zoomControl: false,
		zoomControlOptions: { }
	}
	this.kmlArray = [];
	this.markers = [];
	this.markerArray = [];
	this.map = null;
	this.icons = [];
//	this.infoWindow = new google.maps.InfoWindow();
	this.infoWindow = new InfoBubble({ minWidth: 100, minHeight:100 });
	this.openInitialInfoWindowMarker = null;
	this.bubbleData = [];
	this.markerManager = null;
	this.overlappingMarkerManager = null;
	this.overlappingMarkerManagerEnabled = false;
	this.mmGroupZoom = [];
	this.directionsRenderer = null;
	this.directionsDivId = "";
	this.autocomplete = null;
	return this;
}

WecMapGoogleV3.prototype.drawMap = function( strID )
{
	this.map = new google.maps.Map(document.getElementById(strID), this.Options);
	this.copyrights = { };
	this.addMapLayer( WecMap.osmMapTypeId,      WecMap.osmMapType,      WecMap.labels.OSM_Copyright );
	this.addMapLayer( WecMap.osmCycleMapTypeId, WecMap.osmCycleMapType, WecMap.labels.OSM_bike_Copyright);

	// Create div for showing copyrights.
	var copyrightNode = document.createElement('div');
	copyrightNode.id = 'copyright-control';
	copyrightNode.style.fontSize = '11px';
	copyrightNode.style.fontFamily = 'Arial, sans-serif';
	copyrightNode.style.margin = '0 2px 2px 0';
	copyrightNode.style.whiteSpace = 'nowrap';
	copyrightNode.style.color = '#000000';
	copyrightNode.index = 0;
	this.map.controls[google.maps.ControlPosition.BOTTOM_RIGHT].push(copyrightNode);

	// Create closure for copyright updates
	var copyrightInfo = {
		map: this.map,
		copyrightNode: copyrightNode,
		copyrights: this.copyrights
	};
	google.maps.event.addListener(this.map, 'maptypeid_changed', function()
		{
/*
			// if http://code.google.com/p/gmaps-samples-v3/source/browse/trunk/custom-copyrights/copyright.js is used
			var notice = '';
			var collection = copyrightInfo.copyrights[copyrightInfo.map.getMapTypeId()];
			var bounds = copyrightInfo.map.getBounds();
			var zoom = copyrightInfo.map.getZoom();
			if (collection && bounds && zoom)
				notice = collection.getCopyrightNotice(bounds, zoom);
			copyrightInfo.copyrightNode.innerHTML = notice;
*/
			var notice = copyrightInfo.copyrights[copyrightInfo.map.getMapTypeId()];
			if ( notice )
				copyrightInfo.copyrightNode.innerHTML = notice;
			else
				copyrightInfo.copyrightNode.innerHTML = '';
			google.maps.event.trigger(copyrightInfo.map, 'bounds_changed');
		});
	this.map.setMapTypeId(this.Options.mapTypeId);

	for ( var layer = 0; layer < this.kmlArray.length; ++layer )
		this.kmlArray[ layer ].setMap( this.map );

	// add marker through MarkerManager; don't add them directly
	var that = this;
	var listener = google.maps.event.addListener(this.map, 'bounds_changed', function(){
		WecMapGoogleV3_setupMarkers( that );
		google.maps.event.removeListener(listener);
  	});
}

function WecMapGoogleV3_setupMarkers( mapObj)
{
	mapObj.markerManager = new MarkerManager( mapObj.map );
	mapObj.overlappingMarkerManager = mapObj.overlappingMarkerManagerEnabled ? new OverlappingMarkerSpiderfier( mapObj.map ) : null;

	google.maps.event.addListener(mapObj.markerManager, 'loaded', function(){
		for ( var group = 0; group < mapObj.mmGroupZoom.length; ++group )
		{
			var groupId = mapObj.mmGroupZoom[group].groupId;
			var minZoom = mapObj.mmGroupZoom[group].minZoom;
			var maxZoom = mapObj.mmGroupZoom[group].maxZoom;
			mapObj.markerManager.addMarkers( mapObj.markers[ groupId ], minZoom, maxZoom );

			if ( mapObj.overlappingMarkerManagerEnabled )
			{
				for ( var markerId = 0; markerId < mapObj.markers[ groupId ].length; ++markerId )
				{
					if ( mapObj.bubbleData[groupId] && mapObj.bubbleData[groupId][markerId] )
					{
						if ( mapObj.overlappingMarkerManager )
						{
							mapObj.overlappingMarkerManager.addListener('click', function(marker, event) {
								for ( var group = 0; group < mapObj.mmGroupZoom.length; ++group )
								{
									var groupId = mapObj.mmGroupZoom[group].groupId;
									mapObj.markerManager.addMarkers( mapObj.markers[ groupId ], minZoom, maxZoom );
									for ( var markerId = 0; markerId < mapObj.markers[ groupId ].length; ++markerId )
									{
										if ( mapObj.markers[groupId][markerId] === marker )
										{
											mapObj.openInfoWindow( groupId, markerId );
											return;
										}
									}
								}
							});
							mapObj.overlappingMarkerManager.addListener('spiderfy', function(markers) {
								mapObj.closeInfoWindow( groupId, markerId );
							});
							mapObj.overlappingMarkerManager.addMarker(mapObj.markers[groupId][markerId]);
						}
					}
				}
			} else {

			}

		}
		mapObj.markerManager.refresh();
		if ( mapObj.openInitialInfoWindowMarker )
			mapObj.openInfoWindow( mapObj.openInitialInfoWindowMarker.groupId, mapObj.openInitialInfoWindowMarker.markerId );
  	});
}

WecMapGoogleV3.prototype.setMapTypeId = function( MapTypeId )
{
	this.Options.mapTypeId = MapTypeId;
	if ( this.map )
		this.map.setMapTypeId(MapTypeId);
}

WecMapGoogleV3.prototype.addMapLayer = function( mapTypeId, mapType, strCopyright )
{
	this.map.mapTypes.set(mapTypeId, mapType);

/*
	var collection = new CopyrightCollection();
	collection.addCopyright(
		new Copyright(
			1,
			new google.maps.LatLngBounds( new google.maps.LatLng( -180, -90), new google.maps.LatLng(180,90) ),
			0,
			strCopyright
		)
	);
	this.copyrights[mapTypeId] = collection;
*/
	this.copyrights[mapTypeId] = strCopyright;
}

WecMapGoogleV3.prototype.addKML = function( url )
{
	var layer = new google.maps.KmlLayer( url );
	this.kmlArray.push( layer );
	if ( this.map )
		layer.setMap( this.map );
	return layer;
}

WecMapGoogleV3.prototype.addMarker = function( markerId, latlng, iconId, dirTitle, groupId, address)
{
	if (!iconId) {
		var iconId = 'default';
	}
	var icon = this.icons[iconId];
	var point = new google.maps.LatLng(latlng[0], latlng[1]);

	var marker = new google.maps.Marker(
		{
    			position: new google.maps.LatLng(latlng[0], latlng[1]),
    			icon: icon.image,
    			shadow: icon.shadow
		}
	);

	if (!(this.markers[groupId] instanceof Array))
		this.markers[groupId] = [];
	this.markers[groupId][markerId] = marker;
	this.markerArray.push(marker);

	if ( this.bubbleData[groupId] && this.bubbleData[groupId][markerId] )
	{
		var thisMap = this;
		if ( !this.overlappingMarkerManagerEnabled )
		{
			google.maps.event.addListener(marker, 'click', function() {
				thisMap.openInfoWindow( groupId, markerId );
			});
		} else if ( this.overlappingMarkerManager ) {
			this.overlappingMarkerManager.addListener('click', function(marker, event) {
				thisMap.openInfoWindow( groupId, markerId );
			});
			this.overlappingMarkerManager.addListener('spiderfy', function(markers) {
				thisMap.closeInfoWindow( groupId, markerId );
			});
		}
	}

	if ( this.map )
		marker.setMap( this.map );
	if ( this.overlappingMarkerManager )
		this.overlappingMarkerManager.addMarker(marker);

	return marker;

}


WecMapGoogleV3.prototype.setDraggable = function( groupId, markerId, flag )
{
	var marker = this.markers[groupId][markerId];
	marker.setDraggable(flag);
	if ( flag )
	{
		var map = this;
		google.maps.event.addListener(marker, 'drag', function( mouseEvent ) {
			if ( map.onDragMarker )
				map.onDragMarker( marker, mouseEvent );

		} );
		google.maps.event.addListener(marker, 'dragend', function( mouseEvent ) {
			if ( map.onDragMarkerEnd )
				map.onDragMarkerEnd( marker, mouseEvent );
		} );
	}
}


WecMapGoogleV3.prototype.enableOverlappingMarkerManager = function( flag )
{
	this.overlappingMarkerManagerEnabled = flag;
}

// http://google-maps-utility-library-v3.googlecode.com/svn/tags/markermanager/1.0/docs/reference.html
WecMapGoogleV3.prototype.addMarkersToManager = function( groupId, minZoom, maxZoom )
{
	if (!(this.mmGroupZoom instanceof Array))
		this.mmGroupZoom = [];
	var options = { groupId: groupId, minZoom: minZoom, maxZoom: maxZoom };
	this.mmGroupZoom.push( options );

	if ( this.markerManager )
	{
		this.markerManager.addMarkers(this.markers[groupId], minZoom, maxZoom);
		this.markerManager.refresh();
	}
}

WecMapGoogleV3.prototype.addIcon = function( iconID, imagepath, shadowpath, size, shadowSize, anchor, infoAnchor )
{
	var icon = {
			image: new google.maps.MarkerImage( imagepath, size, new google.maps.Point(0,0), anchor ),
			shadow: new google.maps.MarkerImage( shadowpath, shadowSize, new google.maps.Point(0,0), anchor ),
			infoAnchor: infoAnchor
		   };

	this.icons[ iconID ] = icon;
}

// jumps to a specific marker (determined by groupId and markerId) and zoomlevel on the map
WecMapGoogleV3.prototype.jumpTo = function(groupId, markerId, zoom)
{
	var marker = this.markers[groupId][markerId];
	if (zoom && this.map) {
		this.map.setZoom(zoom);
	}
	this.map.panTo( marker.getPosition() );
	this.openInfoWindow( groupId, markerId );
	return false;
}

WecMapGoogleV3.prototype.addBubble = function( groupId, markerId, labels, content)
{
	if (!(this.bubbleData[groupId] instanceof Array))
		this.bubbleData[groupId] = [];
	for (var i = 0; i < content.length; i++) {
		content[i] = '<div id="' + this.mapId + '_marker_' + groupId + '_' + markerId + '" class="marker">' + content[i] + '</div>';
	}
	this.bubbleData[groupId][markerId] = {
		labels: labels,
		content: content
	};
}

WecMapGoogleV3.prototype.openInfoWindow = function( groupId, markerId ) {
	var marker = this.markers[groupId][markerId];
	var bubbleData = this.bubbleData[groupId][markerId];
	if ( this.infoWindow && marker )
	{
		if ( this.infoWindow.tabs_ ) {
			for ( var i = this.infoWindow.tabs_.length; i > 0; --i ) {
				this.infoWindow.removeTab( i - 1 );
			}
		}
		if ( bubbleData.labels.length > 1 )
		{
			for (var i = 0; i < bubbleData.labels.length; i++) {
				this.infoWindow.addTab(bubbleData.labels[i], bubbleData.content[i]);
			}
		}
		else
			this.infoWindow.setContent(this.bubbleData[groupId][markerId].content[0] );
		this.infoWindow.open(this.map, marker );
	}
}

WecMapGoogleV3.prototype.closeInfoWindow = function( groupId, markerId ) {
	if ( this.infoWindow )
	{
		this.infoWindow.close();
	}
}

WecMapGoogleV3.prototype.openInitialInfoWindow = function( groupId, markerId ) {
	this.openInitialInfoWindowMarker = new Array();
	this.openInitialInfoWindowMarker['groupId'] = groupId;
	this.openInitialInfoWindowMarker['markerId'] = markerId;
	return true;
}


WecMapGoogleV3.prototype.createDirections = function( directionsDivId ) {
	if ( !this.directionsRenderer )
	{
		this.directionsDivId = directionsDivId;
		return true;
	}
	return false;
}

WecMapGoogleV3.prototype.setDirections = function( fromAddr, toAddr, dirTitle, travelMode) {


	if ( !travelMode )
		travelMode = google.maps.TravelMode.DRIVING;
	else if ( travelMode == "DRIVING" )
		travelMode = google.maps.TravelMode.DRIVING;
	else if ( travelMode == "BICYCLING" )
		travelMode = google.maps.TravelMode.BICYCLING;
	else if ( travelMode == "TRANSIT" )
		travelMode = google.maps.TravelMode.TRANSIT;
	else if ( travelMode == "WALKING" )
		travelMode = google.maps.TravelMode.WALKING;

	if ( !this.directionsRenderer )
	{
		if ( !this.directionsDivId )
			this.directionsDivId = this.mapId + '_directions';
		this.directionsRenderer = new google.maps.DirectionsRenderer();
		this.directionsRenderer.setMap( this.map );
		if ( !document.getElementById( this.directionsDivId ) )
		{
			// Workaround for EXT:cal
			// if directions-DIV doesn't exist, create and append it
			var newDiv = document.createElement('div');
		        newDiv.id = this.directionsDivId;
		        var map = document.getElementById( this.mapId )
        		map.parentNode.insertBefore(newDiv,map.nextSibling);
        	}
		this.directionsRenderer.setPanel( document.getElementById( this.directionsDivId ) );
	}

	var request = {
		origin: (fromAddr instanceof Array) ? new google.maps.LatLng( fromAddr[ 0 ], fromAddr[ 1 ] ) : fromAddr,
		destination: (toAddr instanceof Array) ? new google.maps.LatLng( toAddr[ 0 ], toAddr[ 1 ] ) : toAddr,
		travelMode: travelMode
	};
	if ( !WecMap.directionsService )
		WecMap.directionsService = new google.maps.DirectionsService();

	var that = this;
	WecMap.directionsService.route(request, function(response, status)
	{
		if (status == google.maps.DirectionsStatus.OK)
		{
			that.directionsRenderer.setDirections(response);
		}
		else if (  status == google.maps.DirectionsStatus.INVALID_REQUEST
		        || status == google.maps.DirectionsStatus.MAX_WAYPOINTS_EXCEEDED
		        || status == google.maps.DirectionsStatus.NOT_FOUND
		        || status == google.maps.DirectionsStatus.OVER_QUERY_LIMIT
		        || status == google.maps.DirectionsStatus.REQUEST_DENIED
		        || status == google.maps.DirectionsStatus.UNKNOWN_ERROR
		        || status == google.maps.DirectionsStatus.ZERO_RESULTS
		        )
		{
			alert( WecMap.labels[ status ] );
		}
		else
		{
			alert( WecMap.labels.UNKNOWN_ERROR );
		}
	});
	return true;
}

// opens up the directions tab window to a marker
WecMapGoogleV3.prototype.openDirectionsToHere = function( groupId, markerId ) {
	var form = document.getElementById( this.mapId + '_todirform_' + groupId + '_' + markerId );
	form.style.display = "none";
	var form = document.getElementById( this.mapId + '_fromdirform_' + groupId + '_' + markerId );
	form.style.display = "block";
        this.infoWindow.setContent(document.getElementById(this.mapId + '_marker_' + groupId + '_' + markerId));
        this.infoWindow.open();
	var input = document.getElementById( 'tx-wecmap-directions-from-' + this.mapId );
	this.autocomplete = new google.maps.places.Autocomplete(input);
	this.autocomplete.bindTo('bounds', this.map);
	return false;
}

// opens up the directions tab window from a marker
WecMapGoogleV3.prototype.openDirectionsFromHere = function( groupId, markerId ) {
	var form = document.getElementById( this.mapId + '_todirform_' + groupId + '_' + markerId );
	form.style.display = "block";
	var form = document.getElementById( this.mapId + '_fromdirform_' + groupId + '_' + markerId );
	form.style.display = "none";
        this.infoWindow.setContent(document.getElementById(this.mapId + '_marker_' + groupId + '_' + markerId));
        this.infoWindow.open();
	var input = document.getElementById( 'tx-wecmap-directions-to-' + this.mapId );
	this.autocomplete = new google.maps.places.Autocomplete(input);
	this.autocomplete.bindTo('bounds', this.map);
	return false;
}

// resize and recenter map for use in hidden containers accordeon, modal box, etc.
WecMapGoogleV3.prototype.resizeMap = function () {
	this.center = this.map.getCenter();
	this.zoom = this.map.getZoom();
	google.maps.event.trigger(this.map, 'resize');
	this.map.setCenter(this.center);
	this.map.setZoom(this.zoom);
}

// compatibility functions for V2->V3

var G_PHYSICAL_MAP = google.maps.MapTypeId.TERRAIN;
var G_NORMAL_MAP = google.maps.MapTypeId.ROADMAP;
var G_SATELLITE_MAP = google.maps.MapTypeId.SATELLITE;
var G_HYBRID_MAP = google.maps.MapTypeId.HYBRID;
var G_OSM_MAP = 'OpenStreetMap';
var G_OCM_MAP = 'OpenCycleMap';

WecMapGoogleV3.prototype.addMapType = function( MapTypeId )
{
	this.Options.mapTypeControlOptions.mapTypeIds.push( MapTypeId );
	if ( this.map )
		this.map.setOptions( this.Options );
}

WecMapGoogleV3.prototype.setCenter = function( LatLng, Zoom, MapTypeId )
{
	this.Options.zoom = Zoom;
	this.Options.center = LatLng;
	if ( MapTypeId )
		this.Options.mapTypeId = MapTypeId;

	if ( this.map )
	{
		map.setCenter( LatLng );
		map.setZoom( Zoom );
		if ( MapTypeId )
			map.setMapTypeId( MapTypeId );
	}
}

function GZoomControl() // Enable Zoom control
{
	this.modifyOptions = function( options )
	{
		options.zoomControl = true;
		return options;
	}
	return this;
}

function GLargeMapControl3D() // deprecated
{
	return GZoomControl();
}

function GLargeMapControl() // deprecated
{
	return GZoomControl();
}

function GSmallMapControl () // deprecated
{
	return GZoomControl();
}

function GScaleControl() // - a simpler large pan/zoom control. Appears in the top left corner of the map by default.
{
	this.modifyOptions = function( options )
	{
		options.scaleControl = true;
		return options;
	}
	return this;
}

function GSmallZoomControl3D() // deprecated
{
	this.modifyOptions = function( options )
	{
		options.zoomControl = true;
		return options;
	}
	return this;
}

function GSmallZoomControl() // deprecated
{
	return GZoomControl();
}

function GOverviewMapControl() // deprecated
{
	this.modifyOptions = function( options )
	{
		return options;
	}
	return this;
}

function GMapTypeControl() // - buttons that let the user toggle between map types (such as Map and Satellite)
{
	this.modifyOptions = function( options )
	{
		options.mapTypeControl = true;
		options.mapTypeControlOptions.style = google.maps.MapTypeControlStyle.HORIZONTAL_BAR;
		return options;
	}
	return this;
}

function GHierarchicalMapTypeControl() // - buttons that let the user toggle between map types (such as Map and Satellite)
{
	this.modifyOptions = function( options )
	{
		options.mapTypeControl = true;
		options.mapTypeControlOptions.style = google.maps.MapTypeControlStyle.DROPDOWN_MENU;
		return options;
	}
	return this;
}

WecMapGoogleV3.prototype.addControl = function( V2Control )
{
	this.Options = V2Control.modifyOptions( this.Options );
	if ( this.map )
		this.map.setOptions( this.Options );
}

