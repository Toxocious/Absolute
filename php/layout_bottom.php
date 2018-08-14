		<div id='dialog'></div>
		
		<script type='text/javascript'>
			function covertMonth(num) {
				let months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
				let computedRes = months[num];
				return computedRes;
			}

			function Time() {
				let date = new Date();
				this.time = date.toLocaleTimeString();
				this.year = date.getUTCFullYear();
				this.day = date.getUTCDate();
				this.month = date.getUTCMonth();
				this.currentTime = covertMonth(this.month) + ' ' + this.day + ', ' + this.year + ' ' + date.toLocaleTimeString();
				return this.currentTime;
			}

			function timeOutput() {
				let where = document.getElementById('serverTime');
				where.textContent = Time();
			}

			setInterval(timeOutput, 1000);

			$(function() {
				$('#serverTime').text(timeOutput);
			});

			function showSlot(slot) {
				$('#rosterTooltip' + slot).css({ 'display':'block' });
			}
			function hideSlot(slot) {
				$('#rosterTooltip' + slot).css({ 'display':'none' });
			}
		</script>
	</body>
</html>