var uppod_instances = new Array();
var uppod_instances_id = new Array();
var uppod_play_next=0; // set 1 for autoplay next player
function uppodSend(playerID,com,callback) {
	document.getElementById(playerID).sendToUppod(com,(callback?callback:''));
}
function uppodPlayers() { 
	var objectID;
	var objectTags = document.getElementsByTagName("object");
	for(var i=0;i<objectTags.length;i++) {
		objectID = objectTags[i].id;
		if(objectID.indexOf("player") >-1&uppod_instances.indexOf(objectID)==-1) {
			uppod_instances[i] = objectID;
			uppod_instances_id[objectID]=i;
		}
	}
}
function uppodInit(playerID) {
	uppodSend(playerID, 'play');
	
}
if(!Array.indexOf){ 
	Array.prototype.indexOf = function(obj){
	for(var i=0; i<this.length; i++){
		if(this[i]==obj){
			return i;
			}
		}
		return -1;
		}
}
var ap_uppodID = setInterval(uppodPlayers, 1000);