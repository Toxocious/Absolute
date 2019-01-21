/**
 * Userbar roster tooltip hovering functionality.
 */
function showSlot(slot) {
	$('#rosterTooltip' + slot).css({ 'display':'block' });
}

function hideSlot(slot) {
	$('#rosterTooltip' + slot).css({ 'display':'none' });
}