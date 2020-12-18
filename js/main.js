/**
 * Colorbox trigger.
 */
$(".popup.cboxElement").colorbox({ iframe: true, innerWidth: 680, innerHeight: 491, zIndex: 2 });

/**
 * Userbar roster tooltip hovering functionality.
 */
function showSlot(slot) {
	$('#rosterTooltip' + slot).css({ 'display':'block' });
}

function hideSlot(slot) {
	$('#rosterTooltip' + slot).css({ 'display':'none' });
}