const FS = require('fs');

module.exports = {
	name: 'commands',
	description: 'When called, display a collective list of all available commands to the user.',
  args: false,

	execute: function ( user, message, args )
	{
    let response = "Available Commands: ";

    FS.readdirSync('./commands').filter(file => file.endsWith('.js')).forEach(File_Name =>
    {
      File_Name = File_Name.slice(0, File_Name.length - 3);
      response += `${File_Name}, `;
    });

    response = response.slice(0, response.length - 2);

		return {
			message: response,
		};
	},
};
