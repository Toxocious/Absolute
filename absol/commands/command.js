const FS = require('fs');

module.exports = {
	name: 'command',
	description: 'When called, display a collective list of all available commands to the user.',
  args: false,
  
	execute: function ( user, message, args )
	{
    let response = FS.readdirSync('./commands').filter(file => file.endsWith('.js').map(key => response.push(key)));
    console.log(response);
    
		return {
			message: response,
		};
	},
};