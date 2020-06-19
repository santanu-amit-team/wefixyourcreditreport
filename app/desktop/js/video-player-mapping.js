flowplayer(function (api) {

	api.on('resume seek beforeseek pause finish', function(ev) {

		var video = document.getElementsByTagName("video");
		
		/* To get the name of the video playing, we are taking Video SRC
		   And removing unwanted path and file extension.*/
		var vidoeSrc = $(".fp-engine").attr("src");
		var url = new URL(vidoeSrc);
		var mediaName = ((url.pathname).split("/").splice(0, 5)[3] + " credit repair video");

		var mediaLength = video.duration;
		var mediaPlayerName = "Flowplayer HTML5";
		
		var mediaOffset = Math.floor(api.video.time) || 0
		, mediaLength = api.video.duration;
		switch (ev.type) {
			case 'resume':
				if (!mediaOffset) {  
					s.Media.open(mediaName,mediaLength,mediaPlayerName); 
					s.Media.play(mediaName,mediaOffset);
				} else {
					s.Media.play(mediaName,mediaOffset);
				};
			break;
			case 'seek':
				s.Media.play(mediaName,mediaOffset);
			break;
			case 'beforeseek':
				s.Media.stop(mediaName,mediaOffset);
			break;
			case 'pause':
				s.Media.stop(mediaName,mediaOffset);
			break;
			case 'finish':
				s.Media.stop(mediaName,mediaOffset);
				s.Media.close(mediaName);
				mediaOffset = 0;
			break;
			//etc etc
		}

	});

});
