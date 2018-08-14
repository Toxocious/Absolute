$(function() {	
	// do w/e
});

function toggleMessages()
{
	if ( $('div.userbar > div.messages > div').css('display') === 'none' )
	{
		$('div.userbar > div.messages > div').css('display', 'block').html("Loading..");

		$.get('ajax/messages.php', function(data)
		{
			$('div.userbar > div.messages > div').html(data);
		});
	}
	else
	{
		$('div.userbar > div.messages > div').css('display', 'none').html("");
	}
}