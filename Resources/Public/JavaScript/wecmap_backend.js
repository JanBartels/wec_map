
WecMapGoogleV3.prototype.processMouseEvent = function( mouseEvent )
{
	var form = document.forms['editform'];
	if ( form.elements['wec_map_lat'] )
		form.elements['wec_map_lat'].value = mouseEvent.latLng.lat();
	if ( form.elements['wec_map_long'] )
		form.elements['wec_map_long'].value = mouseEvent.latLng.lng();
}

WecMapGoogleV3.prototype.onDragMarker = function( marker, mouseEvent )
{
	this.processMouseEvent( mouseEvent );
}

WecMapGoogleV3.prototype.onDragMarkerEnd = function( marker, mouseEvent )
{
	this.processMouseEvent( mouseEvent );
}
