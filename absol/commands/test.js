module.exports = {
	name: 'test',
	description: 'Generally used to verify that AbsoChat is still functional.',
  args: false,
  
	execute: function ( user, message, args )
	{
		let response;

		if ( user.Rank !== 'Administrator' )
			response = 'You have to be an Administrator to activate this command.';
		else
			response = 'Test command!';

		return {
			message: response,
		};
	},
};