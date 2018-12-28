function setLocation(curLoc){
    try {
      history.pushState(null, null, curLoc);
      return;
    } catch(e) {}
    location.hash = '#' + curLoc;
}
$("body").on('click','a',function(e){
		e.preventDefault();
		page = $(this).attr("href");
		$.ajax({
			xhr: function(){
			    var xhr = new window.XMLHttpRequest();
			    //Upload progress
			    xhr.upload.addEventListener("progress", function(evt){
			      if (evt.lengthComputable) {
			        var percentComplete = evt.loaded / evt.total;
			        //Do something with upload progress
			        //console.log("up", percentComplete);
			      }
			    }, false);
			    //Download progress
			    xhr.addEventListener("progress", function(evt){
			      if (evt.lengthComputable) {
			        var percentComplete = evt.loaded / evt.total;
			        //Do something with download progress
			        //console.log("down", evt.loaded / 600000);
			      }
			    }, false);
			    return xhr;
			},
			url:page,
			type:"post",
			datatype:"html",
			data:{'1':'1'},
			success:function(data){
				$("#content-page").html(data);
				setLocation(page);
			}
		})
	})