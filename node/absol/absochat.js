var method = Absolute.prototype;

function Absolute(socket, conn) {
	this.socket = socket;
	this.conn = conn;
	
	this.init();
}

method.init = function () {
	this.socket.on('connection', function(client) {
		console.log("Client Connected");
		console.log(global);
		this.auth(client.user, client.postcode);
	});
		
	this.socket.on('input', function(data) {
		global.MessageLog.push({
			text: data.text,
			from: data.username,
			mode: '',
			timestamp: Math.round(Date.now())
		});
		
		this.socket.broadcast.emit("irc-message", global.MessageLog[global.MessageLog.length-1]);
	});
};

method.auth = function(userID, postcode) {
	
};


module.exports = Absolute;