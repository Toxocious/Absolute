/* **********************
 * functions.js
 *
 * Houses several commmon functions for Scyther. Only called when needed
 */
 
var ALLOWED_UNICODE = []

var Entities = require('html-entities').XmlEntities;

var fs = require('fs');
var os = require('os');

module.exports = {
log: function(text, logfile) {
	fs.appendFile(logfile, text+os.EOL, encoding='utf8', function (err) {
		if (err) 
			throw err;
	});
	console.log(text);
},
logSync: function(text, logfile) {
	fs.appendFileSync(logfile, text+os.EOL, encoding='utf8', function (err) {
		if (err) 
			throw err;
	});
	console.log(text);
},
asciiOnly: function(text) {
	for(var i = 0, l = text.length; i < l; i++) {
		c = text.charCodeAt(i) 
		if(c > 127 && ALLOWED_UNICODE.indexOf(c) === -1) {
			return false;
		}
	}
	return true;
},
sayPokeName: function(PokeName) {
	entities = new Entities();
	return module.exports.ucfirst(entities.decode(PokeName));
},

/* ****
 * With a Pokemon ID and Alt ID, generate the appropriate icon
 */
getPokeIcon: function(PokeID, AltID, Type) {
	Image = ""+PokeID
	if (Image.length == 1) Image = "00"+Image
	if (Image.length == 2) Image =  "0"+Image
	if (typeof AltID !== "undefined" && AltID+"" != "0") Image = Image+"."+AltID;
	
	if (typeof Image === "undefined" || Image == "")
		Image = "123";
	
	if (Type.toLowerCase() == 'normal' || Type == '' || typeof Type === "undefined")
		Type = 'normal';	
		
	return 'https://sprites.tpkrpg.net/pokemon/icons/'+Type.toLowerCase()+'/'+Image+'.png';
},
/* **********************
 * Easy way to tell if a number has been entered. Used for processing inputs
 */
isNumeric: function (n) {
  if (n > 1000000000000000)
	  return false;
  return !isNaN(parseFloat(n)) && isFinite(n);
},

/* **********************
 * PHPJs ucfirst
 */
ucfirst: function (str) {
  str += '';
  var f = str.charAt(0)
    .toUpperCase();
  return f + str.substr(1);
},

/* **********************
 * Generates a random number
 */
randomIntInc: function (low, high) {
    return Math.floor(Math.random() * (high - low + 1) + low);
},

/* **********************
 * Generate a Random String
 */
randomString: function (len, charSet) {
    charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var randomString = '';
    for (var i = 0; i < len; i++) {
    	var randomPoz = Math.floor(Math.random() * charSet.length);
    	randomString += charSet.substring(randomPoz,randomPoz+1);
    }
    return randomString;
},

/* ***********************
 * HTML Character Entity Encoder
 */
encodeHTML: function (data) {
	var encodedStr = data.replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
	   return '&#'+i.charCodeAt(0)+';';
	});
	
	return encodedStr
},

decodeHTML: function(str) {
    return str.replace(/&#([0-9]{1,5});/gi, function(match, numStr) {
        var num = parseInt(numStr, 10); // read num as normal number
        return String.fromCharCode(num);
    });
}


};

