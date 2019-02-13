/**
 * Handle onclick popups.
 */
function popup(url, height = 500, width = 500)
{
	/**
	 * All styling that your popup div is going to use.
	 */
	let styles = `
		background: #1d2639;
		border: 2px solid #4A618F;
		border-radius: 4px;
		font-size: 14px;
		height: ` + height + `px;
		left: 50%;
		margin-left: -` + (width / 2) + `px;
		margin-top: -` + (height / 2) + `px;
		padding: 3px;
		position: absolute;
		top: 50%;
		width: ` + width + `px;
		z-index: 1000000000;
	`;

	/**
	 * Append the popup div to the DOM.
	 */
	$('html').append(`
		<div id='popup' style='background: rgba(0,0,0,0.5); height: 100%; left: 0; position: absolute; top: 0; width: 100%; z-index: 100000000;'>
			<div id='popupjs_close' style='height: 100%; position: absolute; width: 100%; z-index: 999999999;'></div>
			<div id='popupjs' style='` + styles + `'></div>
		</div>
	`);

	/**
	 * Load the url to the popupjs div.
	 */
	$('#popupjs').load('core/ajax/popups/' + url);

	/**
	 * Clicking on the popup_bg will close the popup.
	 */
	$('#html').click(function()
	{
		$('#popup').remove();
	});
}