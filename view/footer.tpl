	</div>
	<div class="popup-block">
		<div class="overlay"></div>
		<div class="overlay-block">
			<div class="popup">
				<div class="icon close icon-delete"></div>
				<div class="data"></div>
			</div>
		</div>
	</div>	
	<script>
	
		$("body").on('click','.popup > .close',function(){
			$(".popup-block").hide();
			$(".popup-block .data").html("");
		})		
	</script>
</body>
</html>