module.exports = {
	name: 'test',
	description: 'Generally used to verify that AbsoChat is still functional.',
  args: false,
  power_level: 7,

	execute: function ( user, message, args )
	{
		let response;

    response = 'Test command!';

		return {
			message: response,
		};
	},
};
